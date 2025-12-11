<?php

// Example usage of the new optimized ImageOptimizer

use App\Helpers\ImageOptimizer;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Handle product image upload
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,gif,webp|max:5120' // 5MB
        ]);

        try {
            // The new way - queue-friendly and production-ready
            $result = ImageOptimizer::handleUpload(
                $request->file('image'),
                'products',
                [
                    'quality' => 90, // High quality for product images
                    'generate_webp' => true,
                    'thumbnails' => [150, 300, 600, 900]
                ]
            );

            if (isset($result['queued'])) {
                return response()->json([
                    'message' => 'Image uploaded successfully, optimization in progress',
                    'path' => $result['path'],
                    'queued' => true
                ]);
            }

            return response()->json([
                'message' => 'Image processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Upload failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display product with optimized images
     */
    public function show(Product $product)
    {
        // Generate responsive image HTML
        $imageHtml = ImageOptimizer::responsiveImage(
            $product->image_path,
            $product->name,
            ['class' => 'product-image img-fluid']
        );

        return view('products.show', compact('product', 'imageHtml'));
    }

    /**
     * Batch optimize all product images
     */
    public function batchOptimize()
    {
        // This will queue optimization jobs for all images
        $result = ImageOptimizer::batchOptimizeDirectory('products');
        
        return response()->json([
            'message' => 'Batch optimization started',
            'processed' => $result['processed'],
            'errors' => $result['errors']
        ]);
    }
}

// Blade template usage:
?>
<!-- In your Blade template -->
<div class="product-gallery">
    {!! ImageOptimizer::responsiveImage($product->image_path, $product->name, ['class' => 'main-image']) !!}
    
    <!-- For lazy loading -->
    <img src="{{ ImageOptimizer::lazyPlaceholder() }}" 
         data-src="{{ Storage::url($product->image_path) }}" 
         alt="{{ $product->name }}"
         class="lazy-load">
</div>

<!-- JavaScript for lazy loading -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const lazyImages = document.querySelectorAll('.lazy-load');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy-load');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    }
});
</script>

<?php
// Configuration in .env file:
/*
IMAGE_QUEUE_ENABLED=true
IMAGE_QUEUE_NAME=images
IMAGE_CACHE_ENABLED=true
IMAGE_CACHE_DRIVER=redis
CDN_ENABLED=true
CDN_URL=https://cdn.yoursite.com
JPEGOPTIM_PATH=jpegoptim
OPTIPNG_PATH=optipng
PNGQUANT_PATH=pngquant
GIFSICLE_PATH=gifsicle
*/

// Queue worker command:
// php artisan queue:work --queue=images --timeout=300 --memory=512
?>