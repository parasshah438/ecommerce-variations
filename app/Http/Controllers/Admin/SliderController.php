<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use App\Helpers\ImageOptimizer;

class SliderController extends Controller
{
    /**
     * Display a listing of sliders
     */
    public function index()
    {
        return view('admin.sliders.index');
    }

    /**
     * Get sliders data for DataTables
     */
    public function data(Request $request)
    {
        try {
            // Return statistics if requested
            if ($request->get('get_stats')) {
                $stats = [
                    'total' => Slider::count(),
                    'active' => Slider::where('is_active', true)->count(),
                    'inactive' => Slider::where('is_active', false)->count()
                ];
                return response()->json(['stats' => $stats]);
            }

            // Validate request parameters
            $request->validate([
                'search.value' => 'nullable|string|max:255',
                'length' => 'nullable|integer|min:1|max:100',
                'start' => 'nullable|integer|min:0',
                'order' => 'nullable|array',
                'columns' => 'nullable|array',
                'status' => 'nullable|in:active,inactive',
                'draw' => 'nullable|integer'
            ]);

            $query = Slider::query();

            // Apply filters
            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            // Global search
            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $query->where(function($q) use ($searchValue) {
                    $q->where('title', 'LIKE', "%{$searchValue}%")
                      ->orWhere('description', 'LIKE', "%{$searchValue}%");
                });
            }

            // Total records count
            $totalRecords = Slider::count();
            $filteredRecords = $query->count();

            // Ordering
            if ($request->filled('order')) {
                $orderColumn = $request->input('order.0.column', 0);
                $orderDir = $request->input('order.0.dir', 'asc');
                
                $columns = ['', 'id', 'title', 'is_active', 'sort_order', 'created_at', ''];
                if (isset($columns[$orderColumn]) && $columns[$orderColumn]) {
                    $query->orderBy($columns[$orderColumn], $orderDir);
                }
            } else {
                $query->orderBy('sort_order');
            }

            // Pagination
            $length = $request->input('length', 25);
            $start = $request->input('start', 0);
            
            $sliders = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($sliders as $slider) {
                $data[] = [
                    'id' => $slider->id,
                    'title' => $slider->title ?: 'Untitled Slider',
                    'description' => $slider->description ? Str::limit($slider->description, 50) : '-',
                    'image' => $slider->image ? $slider->getThumbnailUrl(150) : asset('images/slider-placeholder.jpg'),
                    'link' => $slider->link ?: '-',
                    'is_active' => $slider->is_active,
                    'sort_order' => $slider->sort_order,
                    'created_at' => $slider->created_at ? $slider->created_at->format('d M Y') : 'N/A',
                    'actions' => $this->generateActionButtons($slider)
                ];
            }

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $filteredRecords,
                "data" => $data
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in slider data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Internal server error',
                'message' => 'Failed to load sliders data'
            ], 500);
        }
    }

    /**
     * Generate action buttons for slider row
     */
    private function generateActionButtons(Slider $slider): string
    {
        $sliderId = $slider->id;
        $sliderStatus = $slider->is_active;
        
        $actions = [];
      
        // Edit Button
        $actions[] = '<button type="button" class="btn btn-sm btn-primary edit-slider me-1" data-id="' . $sliderId . '" title="Edit Slider">
                        <i class="fas fa-edit"></i>
                      </button>';
        
        // Delete Button
        $actions[] = '<button type="button" class="btn btn-sm btn-danger delete-slider" data-id="' . $sliderId . '" title="Delete Slider">
                        <i class="fas fa-trash"></i>
                      </button>';
        
        return '<div class="btn-group">' . implode('', $actions) . '</div>';
    }

    /**
     * Show the form for creating a new slider (AJAX)
     */
    public function create()
    {
        try {
            return response()->json([
                'success' => true,
                'html' => view('admin.sliders.partials.form')->render()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load create form'
            ], 500);
        }
    }

    /**
     * Store a newly created slider
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link' => 'nullable|url|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max, will be optimized
            'is_active' => 'nullable|in:0,1,true,false',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            // Handle optimized image upload
            $imagePath = null;
            $imageData = [];
            
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                // Use new ImageOptimizer handleUpload method
                $optimizationResult = ImageOptimizer::handleUpload(
                    $request->file('image'),
                    'sliders',
                    [
                        'quality' => 80,
                        'max_width' => 1920,
                        'max_height' => 740,
                        'force_resize' => true, // Force exact dimensions like your old code
                        'generate_webp' => true,
                        'thumbnails' => [300, 600, 900, 1200]
                    ]
                );
                
                if (isset($optimizationResult['queued'])) {
                    // Image was queued for processing
                    $imagePath = $optimizationResult['path'];
                    \Log::info('Slider image queued for optimization', ['path' => $imagePath]);
                } else {
                    // Image was processed immediately
                    $imagePath = $optimizationResult['path'];
                    $imageData = $optimizationResult;
                    
                    \Log::info('Slider image optimized successfully', [
                        'path' => $imagePath,
                        'webp_generated' => isset($optimizationResult['webp_path'])
                    ]);
                }
            }

            // Get next sort order if not provided
            $sortOrder = $validated['sort_order'] ?? (Slider::max('sort_order') + 1);

            // Create the slider
            $slider = Slider::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'link' => $validated['link'],
                'image' => $imagePath,
                'is_active' => in_array($request->input('is_active'), ['1', 'true', true], true),
                'sort_order' => $sortOrder
            ]);

            // Clear home sliders cache
            \Cache::forget('home_sliders');
            
            return response()->json([
                'success' => true,
                'message' => 'Slider created successfully!',
                'slider' => [
                    'id' => $slider->id,
                    'title' => $slider->title,
                    'is_active' => $slider->is_active,
                    'sort_order' => $slider->sort_order
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Delete uploaded image if slider creation fails
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
            \Log::error('Error creating slider: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create slider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified slider (AJAX)
     */
    public function show(Slider $slider)
    {
        try {
            return response()->json([
                'success' => true,
                'slider' => [
                    'id' => $slider->id,
                    'title' => $slider->title,
                    'description' => $slider->description,
                    'link' => $slider->link,
                    'image_url' => $slider->image ? $slider->getThumbnailUrl(600) : null,
                    'is_active' => $slider->is_active,
                    'sort_order' => $slider->sort_order,
                    'created_at' => $slider->created_at->format('d M Y, h:i A'),
                    'updated_at' => $slider->updated_at->format('d M Y, h:i A')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load slider details'
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified slider (AJAX)
     */
    public function edit(Slider $slider)
    {
        try {
            return response()->json([
                'success' => true,
                'slider' => [
                    'id' => $slider->id,
                    'title' => $slider->title,
                    'description' => $slider->description,
                    'link' => $slider->link,
                    'image_url' => $slider->image ? asset('uploads/' . $slider->image) : null,
                    'is_active' => $slider->is_active,
                    'sort_order' => $slider->sort_order
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading slider for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load slider data'
            ], 500);
        }
    }

    /**
     * Update the specified slider
     */
    public function update(Request $request, Slider $slider)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max, will be optimized
            'is_active' => 'nullable|in:0,1,true,false',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            $oldImagePath = $slider->image;
            $imagePath = $oldImagePath;

            // Handle optimized image upload
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                
                if ($file->isValid()) {
                    try {
                        // Use new ImageOptimizer handleUpload method (updated method)
                        $optimizationResult = ImageOptimizer::handleUpload(
                            $file,
                            'sliders',
                            [
                                'quality' => 80,
                                'max_width' => 1920,
                                'max_height' => 740,
                                'force_resize' => true, // Force exact dimensions like your old code
                                'generate_webp' => true,
                                'thumbnails' => [300, 600, 900, 1200]
                            ]
                        );
                        
                        if (isset($optimizationResult['queued'])) {
                            // Image was queued for processing
                            $imagePath = $optimizationResult['path'];
                            \Log::info('Slider image queued for optimization', ['path' => $imagePath, 'slider_id' => $slider->id]);
                        } else {
                            // Image was processed immediately
                            $imagePath = $optimizationResult['path'];
                            
                            \Log::info('Slider image updated and optimized', [
                                'slider_id' => $slider->id,
                                'path' => $imagePath,
                                'webp_generated' => isset($optimizationResult['webp_path'])
                            ]);
                        }
                        
                        // Delete old image and related files only after successful upload
                        if ($oldImagePath && $imagePath !== $oldImagePath) {
                            $this->deleteImageFiles($oldImagePath);
                        }
                        
                    } catch (\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to upload image: ' . $e->getMessage()
                        ], 500);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid file upload'
                    ], 422);
                }
            }

            // Update the slider
            $slider->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'link' => $validated['link'],
                'image' => $imagePath,
                'is_active' => in_array($request->input('is_active'), ['1', 'true', true], true),
                'sort_order' => $validated['sort_order'] ?? $slider->sort_order
            ]);

            // Clear home sliders cache
            \Cache::forget('home_sliders');
            
            return response()->json([
                'success' => true,
                'message' => 'Slider updated successfully!',
                'slider' => [
                    'id' => $slider->id,
                    'title' => $slider->title,
                    'is_active' => $slider->is_active,
                    'sort_order' => $slider->sort_order
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating slider: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update slider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified slider
     */
    public function destroy(Slider $slider)
    {
        try {
            // Delete image and all related files
            if ($slider->image) {
                \Log::info('Deleting slider images', [
                    'slider_id' => $slider->id,
                    'image_path' => $slider->image
                ]);
                $this->deleteImageFiles($slider->image);
            }

            // Delete slider
            $slider->delete();
            
            // Clear home sliders cache
            \Cache::forget('home_sliders');

            return response()->json([
                'success' => true,
                'message' => 'Slider and all associated files deleted successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting slider: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete slider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove image from slider
     */
    public function removeImage(Slider $slider)
    {
        try {
            if ($slider->image) {
                // Delete main image and all related optimized files
                $this->deleteImageFiles($slider->image);
                $slider->update(['image' => null]);
            }

            // Clear home sliders cache
            \Cache::forget('home_sliders');
            
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
            $thumbnailSizes = [300, 600, 900, 1200];
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
            \Log::warning('Failed to delete some slider image files: ' . $e->getMessage(), [
                'image_path' => $imagePath
            ]);
        }
    }

    /**
     * Get optimized image URL for slider
     */
    public function getOptimizedImageUrl(Slider $slider, $size = null)
    {
        if (!$slider->image) {
            return null;
        }
        
        $pathInfo = pathinfo($slider->image);
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
        
        return Storage::url($slider->image);
    }

    /**
     * Bulk action for sliders
     */
    public function bulkAction(Request $request)
    {
        try {
            $request->validate([
                'action' => 'required|in:activate,deactivate,delete',
                'ids' => 'required|array|min:1',
                'ids.*' => 'exists:sliders,id'
            ]);

            $ids = $request->ids;
            $action = $request->action;
            $count = 0;

            switch ($action) {
                case 'activate':
                    $count = Slider::whereIn('id', $ids)->update(['is_active' => true]);
                    break;
                case 'deactivate':
                    $count = Slider::whereIn('id', $ids)->update(['is_active' => false]);
                    break;
                case 'delete':
                    $sliders = Slider::whereIn('id', $ids)->get();
                    $imageCount = 0;
                    foreach ($sliders as $slider) {
                        if ($slider->image) {
                            \Log::info('Bulk deleting slider images', [
                                'slider_id' => $slider->id,
                                'image_path' => $slider->image
                            ]);
                            $this->deleteImageFiles($slider->image);
                            $imageCount++;
                        }
                    }
                    $count = Slider::whereIn('id', $ids)->delete();
                    \Log::info('Bulk delete completed', [
                        'sliders_deleted' => $count,
                        'images_cleaned' => $imageCount
                    ]);
                    break;
            }

            // Clear home sliders cache after bulk action
            \Cache::forget('home_sliders');
            
            return response()->json([
                'success' => true,
                'message' => "Bulk action completed successfully! {$count} sliders processed."
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in bulk action: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk action'
            ], 500);
        }
    }
}