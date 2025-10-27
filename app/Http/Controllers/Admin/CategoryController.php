<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'nullable|boolean'
        ]);

        try {
            // Handle image upload first
            $imagePath = null;
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $filename = time() . '_' . Str::random(10) . '.' . $request->file('image')->getClientOriginalExtension();
                $imagePath = $request->file('image')->storeAs('categories', $filename, 'public');
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'nullable|boolean'
        ]);

        try {
            $oldImagePath = $category->image;
            $imagePath = $oldImagePath;

            // Handle image upload
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                
                if ($file->isValid()) {
                    // Generate unique filename
                    $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    
                    try {
                        $imagePath = $file->storeAs('categories', $filename, 'public');
                        
                        // Verify file was actually stored
                        if (!Storage::disk('public')->exists($imagePath)) {
                            throw new \Exception('File was not stored successfully');
                        }
                        
                        // Delete old image only after successful upload
                        if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                            Storage::disk('public')->delete($oldImagePath);
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
                $category->deleteImage();
                $category->update(['image' => null]);
            }

            return response()->json(['success' => true, 'message' => 'Image removed successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to remove image.']);
        }
    }
}