<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Helpers\ImageOptimizer;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        $query = Category::with(['parent', 'children', 'products']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by parent category
        if ($request->filled('parent_id')) {
            if ($request->parent_id == 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $categories = $query->orderBy('name')->paginate(15)->appends($request->all());
        
        // Get parent categories for filter dropdown
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        
        return view('admin.categories.index', compact('categories', 'parentCategories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
       
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max, will be optimized
            'is_active' => 'nullable|boolean'
        ]);

        try {
            // Handle optimized image upload
            $imagePath = null;
            $imageData = [];
            
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                // Use ImageOptimizer for better compression and WebP generation
                $optimizationResult = ImageOptimizer::optimizeUploadedImage(
                    $request->file('image'),
                    'categories',
                    [
                        'quality' => 85,
                        'maxWidth' => 800,
                        'maxHeight' => 600,
                        'generateWebP' => true,
                        'generateThumbnails' => true,
                        'thumbnailSizes' => [150, 300]
                    ]
                );
                
                if (isset($optimizationResult['optimized'])) {
                    $imagePath = $optimizationResult['optimized'];
                    $imageData = $optimizationResult;
                    
                    // Log optimization success
                    \Log::info('Category image optimized successfully', [
                        'original_size' => $optimizationResult['original_size'] ?? 0,
                        'optimized_size' => $optimizationResult['optimized_size'] ?? 0,
                        'compression_ratio' => $optimizationResult['compression_ratio'] ?? 0,
                        'webp_generated' => isset($optimizationResult['webp'])
                    ]);
                } else {
                    // Fallback to regular upload if optimization fails
                    $filename = time() . '_' . Str::random(10) . '.' . $request->file('image')->getClientOriginalExtension();
                    $imagePath = $request->file('image')->storeAs('categories', $filename, 'public');
                }
            }

            // Create the category
            $category = Category::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'],
                'parent_id' => $validated['parent_id'],
                'image' => $imagePath,
                'is_active' => (bool) $request->input('is_active', false)
            ]);

            return redirect()
                ->route('admin.categories.index')
                ->with('success', 'Category created successfully!');

        } catch (\Exception $e) {
            // Delete uploaded image if category creation fails
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create category: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified category
     */
    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'products.images']);
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::where('id', '!=', $category->id)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
            
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($category->id)
            ],
            'description' => 'nullable|string',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($category) {
                    if ($value == $category->id) {
                        $fail('A category cannot be its own parent.');
                    }
                }
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max, will be optimized
            'is_active' => 'nullable|boolean'
        ]);

        try {
            $oldImagePath = $category->image;
            $imagePath = $oldImagePath;

            // Handle optimized image upload
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                
                if ($file->isValid()) {
                    try {
                        // Use ImageOptimizer for better compression and WebP generation
                        $optimizationResult = ImageOptimizer::optimizeUploadedImage(
                            $file,
                            'categories',
                            [
                                'quality' => 85,
                                'maxWidth' => 800,
                                'maxHeight' => 600,
                                'generateWebP' => true,
                                'generateThumbnails' => true,
                                'thumbnailSizes' => [150, 300]
                            ]
                        );
                        
                        if (isset($optimizationResult['optimized'])) {
                            $imagePath = $optimizationResult['optimized'];
                            
                            // Verify file was actually stored
                            if (!Storage::disk('public')->exists($imagePath)) {
                                throw new \Exception('Optimized file was not stored successfully');
                            }
                            
                            // Delete old image and related files only after successful upload
                            if ($oldImagePath) {
                                $this->deleteImageFiles($oldImagePath);
                            }
                            
                            // Log optimization success
                            \Log::info('Category image updated and optimized', [
                                'category_id' => $category->id,
                                'original_size' => $optimizationResult['original_size'] ?? 0,
                                'optimized_size' => $optimizationResult['optimized_size'] ?? 0,
                                'compression_ratio' => $optimizationResult['compression_ratio'] ?? 0,
                                'webp_generated' => isset($optimizationResult['webp'])
                            ]);
                        } else {
                            // Fallback to regular upload
                            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                            $imagePath = $file->storeAs('categories', $filename, 'public');
                            
                            if (!Storage::disk('public')->exists($imagePath)) {
                                throw new \Exception('File was not stored successfully');
                            }
                            
                            // Delete old image
                            if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                                Storage::disk('public')->delete($oldImagePath);
                            }
                        }
                        
                    } catch (\Exception $e) {
                        return back()
                            ->withInput()
                            ->withErrors(['image' => 'Failed to upload image: ' . $e->getMessage()]);
                    }
                } else {
                    return back()
                        ->withInput()
                        ->withErrors(['image' => 'Invalid file upload']);
                }
            }

            // Update the category
            $category->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'],
                'parent_id' => $validated['parent_id'],
                'image' => $imagePath,
                'is_active' => (bool) $request->input('is_active', false)
            ]);

            return redirect()
                ->route('admin.categories.index')
                ->with('success', 'Category updated successfully!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update category: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        try {
            // Check if category has products
            if ($category->products()->count() > 0) {
                return back()->withErrors(['error' => 'Cannot delete category with associated products.']);
            }

            // Check if category has subcategories
            if ($category->children()->count() > 0) {
                return back()->withErrors(['error' => 'Cannot delete category with subcategories.']);
            }

            // Delete image
            $category->deleteImage();

            // Delete category
            $category->delete();

            return redirect()
                ->route('admin.categories.index')
                ->with('success', 'Category deleted successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete category: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove image from category
     */
    public function removeImage(Category $category)
    {
        try {
            if ($category->image) {
                // Delete main image and all related optimized files
                $this->deleteImageFiles($category->image);
                $category->update(['image' => null]);
            }

            return response()->json(['success' => true, 'message' => 'Image removed successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to remove image.']);
        }
    }

    /**
     * Delete image and all related optimized files (WebP, thumbnails)
     */
    private function deleteImageFiles($imagePath)
    {
        try {
            // Delete main image
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
            // Get file info for related files
            $pathInfo = pathinfo($imagePath);
            $directory = $pathInfo['dirname'];
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'] ?? '';
            
            // Delete WebP version
            $webpPath = $directory . '/' . $filename . '.webp';
            if (Storage::disk('public')->exists($webpPath)) {
                Storage::disk('public')->delete($webpPath);
            }
            
            // Delete thumbnails
            $thumbnailSizes = [150, 300];
            foreach ($thumbnailSizes as $size) {
                $thumbPath = $directory . '/' . $filename . '_' . $size . '.' . $extension;
                if (Storage::disk('public')->exists($thumbPath)) {
                    Storage::disk('public')->delete($thumbPath);
                }
            }
            
            // Delete backup file if it exists
            $backupPath = str_replace('storage/app/public/', '', storage_path('app/public/' . $imagePath)) . '.backup';
            $fullBackupPath = storage_path('app/public/' . $directory . '/' . basename($imagePath) . '.backup');
            if (file_exists($fullBackupPath)) {
                unlink($fullBackupPath);
            }
            
        } catch (\Exception $e) {
            \Log::warning('Failed to delete some image files: ' . $e->getMessage(), [
                'image_path' => $imagePath
            ]);
        }
    }

    /**
     * Get optimized image URL for category
     */
    public function getOptimizedImageUrl(Category $category, $size = null)
    {
        if (!$category->image) {
            return null;
        }
        
        $pathInfo = pathinfo($category->image);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? 'jpg';
        
        if ($size) {
            // Return thumbnail URL
            $thumbPath = $directory . '/' . $filename . '_' . $size . '.' . $extension;
            if (Storage::disk('public')->exists($thumbPath)) {
                return Storage::url($thumbPath);
            }
        }
        
        // Return WebP if available, otherwise original
        $webpPath = $directory . '/' . $filename . '.webp';
        if (Storage::disk('public')->exists($webpPath)) {
            return Storage::url($webpPath);
        }
        
        return Storage::url($category->image);
    }
}