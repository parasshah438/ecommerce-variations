<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class PagesController extends Controller
{
    /**
     * Display the About Us page
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        return view('pages.about');
    }

    /**
     * Display the FAQ page
     *
     * @return \Illuminate\View\View
     */
    public function faq()
    {
        return view('pages.faq');
    }

    /**
     * Display the Help page
     *
     * @return \Illuminate\View\View
     */
    public function help()
    {
        return view('pages.help');
    }

    /**
     * Display the Support page (redirect to help)
     *
     * @return \Illuminate\View\View
     */
    public function support()
    {
        return view('pages.help');
    }

    /**
     * Display the Privacy Policy page
     *
     * @return \Illuminate\View\View
     */
    public function privacy()
    {
        return view('pages.privacy');
    }

    /**
     * Display the Terms & Conditions page
     *
     * @return \Illuminate\View\View
     */
    public function terms()
    {
        return view('pages.terms');
    }

    /**
     * Display the Shipping Policy page
     *
     * @return \Illuminate\View\View
     */
    public function shipping()
    {
        return view('pages.shipping');
    }

    /**
     * Display the Return & Refund Policy page
     *
     * @return \Illuminate\View\View
     */
    public function returnRefund()
    {
        return view('pages.return-refund');
    }

    /**
     * Display the Cookie Policy page
     *
     * @return \Illuminate\View\View
     */
    public function cookiePolicy()
    {
        return view('pages.cookie-policy');
    }

    /**
     * Display the Cookie Preferences page
     *
     * @return \Illuminate\View\View
     */
    public function cookiePreferences()
    {
        return view('pages.cookie-preferences');
    }

    /**
     * Display the Size Guide page
     *
     * @return \Illuminate\View\View
     */
    public function sizeGuide()
    {
        return view('pages.size-guide');
    }

    /**
     * Display the Virtual Try-On page
     *
     * @return \Illuminate\View\View
     */
    public function virtualTryOn()
    {
        return view('pages.virtual-try-on');
    }

    /**
     * Display the Accessibility / Screen Reader Info page
     *
     * @return \Illuminate\View\View
     */
    public function accessibility()
    {
        return view('pages.accessibility');
    }

    /**
     * Display the Security / Data Protection page
     *
     * @return \Illuminate\View\View
     */
    public function securityDataProtection()
    {
        return view('pages.security-data-protection');
    }

    /**
     * Display the AI Personal Shopper / Quiz page
     *
     * @return \Illuminate\View\View
     */
    public function aiPersonalShopper()
    {
        return view('pages.ai-personal-shopper');
    }
    /**
     * Get AI recommendations based on user preferences
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function getAiRecommendations(Request $request)
    {
        $category = $request->input('category');
        $priceRange = $request->input('price_range');
        $occasion = $request->input('occasion');
        $style = $request->input('style');
        $colors = $request->input('colors', []);
        $size = $request->input('size');

        // Build query for products with variations
        $query = \App\Models\Product::with(['images', 'variations.stock'])
          //  ->where('status', 'active')
            ->whereHas('variations');

        // Apply filters based on selections
        if ($priceRange) {
            [$minPrice, $maxPrice] = explode('-', $priceRange);
            $query->whereBetween('price', [(float)$minPrice, (float)$maxPrice]);
        }

        // Simulate AI logic based on selections
        $baseScore = 100;
        $orderBy = 'created_at'; // default

        if ($style) {
            switch ($style) {
                case 'modern':
                    $query->where('name', 'LIKE', '%modern%');
                    break;
                case 'classic':
                    $query->where('name', 'LIKE', '%classic%');
                    break;
                case 'trendy':
                    $orderBy = 'created_at'; // newest first
                    break;
            }
        }

        if ($occasion) {
            switch ($occasion) {
                case 'formal':
                    $query->where(function($q) {
                        $q->where('name', 'LIKE', '%formal%')
                          ->orWhere('name', 'LIKE', '%shirt%')
                          ->orWhere('name', 'LIKE', '%blazer%');
                    });
                    break;
                case 'casual':
                    $query->where(function($q) {
                        $q->where('name', 'LIKE', '%casual%')
                          ->orWhere('name', 'LIKE', '%t-shirt%')
                          ->orWhere('name', 'LIKE', '%jeans%');
                    });
                    break;
                case 'party':
                    $query->where(function($q) {
                        $q->where('name', 'LIKE', '%party%')
                          ->orWhere('name', 'LIKE', '%dress%')
                          ->orWhere('name', 'LIKE', '%suit%');
                    });
                    break;
            }
        }

        // Get products
        $products = $query->orderBy($orderBy, 'desc')
                         ->limit(12)
                         ->get();

        // If no products found with filters, get random products
        if ($products->isEmpty()) {
            $products = \App\Models\Product::with(['images', 'variations.stock'])
                //->where('status', 'active')
                ->whereHas('variations')
                ->inRandomOrder()
                ->limit(12)
                ->get();
        }

        // Return HTML partial for AJAX
        if ($request->ajax()) {
            $html = view('pages.partials.ai-recommendations', compact('products'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $products->count(),
                'message' => "Found {$products->count()} products based on your preferences"
            ]);
        }

        return view('pages.ai-personal-shopper', compact('products'));
    }
    /**
     * Display the Product Care Guide page
     *
     * @return \Illuminate\View\View
     */
    public function productCareGuide()
    {
        return view('pages.product-care');
    }

    /**
     * Display the Lookbook page
     *
     * @return \Illuminate\View\View
     */
    public function lookbook()
    {
        return view('pages.lookbook');
    }

    /**
     * Display the Gallery page
     *
     * @return \Illuminate\View\View
     */
    public function gallery()
    {
        return view('pages.lookbook'); // Redirect to lookbook
    }

    /**
     * Display the Maintenance page
     *
     * @return \Illuminate\View\View
     */
    public function maintenance()
    {
        return view('pages.maintenance');
    }

    /**
     * Display the 404 Error page
     *
     * @return \Illuminate\View\View
     */
    public function error404()
    {
        return response()->view('pages.404', [], 404);
    }

    /**
     * Display the sitemap page
     *
     * @return \Illuminate\View\View
     */
    public function sitemap()
    {
        // Organize routes by categories for better SEO and user experience
        $routes = [
            'main' => [
                ['name' => 'Home', 'url' => route('welcome'), 'description' => 'Welcome to our online store with the latest products and offers'],
                ['name' => 'Products', 'url' => route('products.index'), 'description' => 'Browse our complete collection of products'],
                ['name' => 'New Arrivals', 'url' => route('products.new_arrivals'), 'description' => 'Latest products added to our collection'],
            ],
            'account' => [
                ['name' => 'Login', 'url' => route('login'), 'description' => 'Sign in to your account'],
                ['name' => 'Register', 'url' => route('register'), 'description' => 'Create a new account'],
                ['name' => 'Dashboard', 'url' => route('dashboard'), 'description' => 'Your personal dashboard', 'auth' => true],
            ],
            'shopping' => [
                ['name' => 'Cart', 'url' => route('cart.index'), 'description' => 'View and manage your shopping cart', 'auth' => true],
                ['name' => 'Checkout', 'url' => route('checkout.index'), 'description' => 'Complete your purchase securely', 'auth' => true],
                ['name' => 'Wishlist', 'url' => route('wishlist.index'), 'description' => 'Save your favorite items', 'auth' => true],
                ['name' => 'Orders', 'url' => route('orders.index'), 'description' => 'View your order history', 'auth' => true],
            ],
            'information' => [
                ['name' => 'About Us', 'url' => route('pages.about'), 'description' => 'Learn about our company, mission, and values'],
                ['name' => 'Help & Support', 'url' => route('pages.help'), 'description' => 'Get help with orders, returns, and general questions'],
                ['name' => 'FAQ', 'url' => route('pages.faq'), 'description' => 'Frequently asked questions about our products and services'],
                ['name' => 'Size Guide', 'url' => route('pages.size.guide'), 'description' => 'Find the perfect fit with our comprehensive sizing guide'],
                ['name' => 'Virtual Try-On', 'url' => route('pages.virtual.try.on'), 'description' => 'Try products virtually using AR technology before you buy'],
            ],
            'policies' => [
                ['name' => 'Privacy Policy', 'url' => route('pages.privacy'), 'description' => 'How we collect, use, and protect your personal information'],
                ['name' => 'Terms & Conditions', 'url' => route('pages.terms'), 'description' => 'Terms of service and usage conditions'],
                ['name' => 'Shipping Policy', 'url' => route('pages.shipping'), 'description' => 'Shipping options, delivery times, and costs'],
                ['name' => 'Return & Refund Policy', 'url' => route('pages.return.refund'), 'description' => 'Our hassle-free return and refund process'],
                ['name' => 'Cookie Policy', 'url' => route('pages.cookie.policy'), 'description' => 'How we use cookies to enhance your experience'],
            ],
            'gallery' => [
                ['name' => 'Lookbook & Gallery', 'url' => route('pages.lookbook'), 'description' => 'Explore our fashion photography and style inspiration'],
            ],
        ];

        return view('pages.sitemap', compact('routes'));
    }

    /**
     * Generate XML sitemap for search engines
     *
     * @return \Illuminate\Http\Response
     */
    public function sitemapXml()
    {
        // Get all static page URLs with their priorities and change frequencies
        $urls = collect([
            // High priority pages (main site pages)
            [
                'url' => route('welcome'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '1.0'
            ],
            [
                'url' => route('products.index'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'daily', 
                'priority' => '0.9'
            ],
            [
                'url' => route('products.new_arrivals'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '0.8'
            ],
            
            // Authentication pages
            [
                'url' => route('login'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ],
            [
                'url' => route('register'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ],
            
            // Information pages - High SEO value
            [
                'url' => route('pages.about'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.7'
            ],
            [
                'url' => route('pages.help'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.6'
            ],
            [
                'url' => route('pages.faq'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.6'
            ],
            [
                'url' => route('pages.size.guide'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.6'
            ],
            [
                'url' => route('pages.virtual.try.on'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.6'
            ],
            [
                'url' => route('pages.product.care'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ],
            [
                'url' => route('pages.lookbook'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.6'
            ],
            
            // Policy pages - Important for legal compliance
            [
                'url' => route('pages.privacy'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'yearly',
                'priority' => '0.4'
            ],
            [
                'url' => route('pages.terms'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'yearly',
                'priority' => '0.4'
            ],
            [
                'url' => route('pages.shipping'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ],
            [
                'url' => route('pages.return.refund'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ],
            [
                'url' => route('pages.cookie.policy'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'yearly',
                'priority' => '0.3'
            ],
            [
                'url' => route('pages.cookie.preferences'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'yearly',
                'priority' => '0.3'
            ],
            
            // Additional utility pages
            [
                'url' => route('pages.sitemap'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.4'
            ],
        ]);

        // Add dynamic product URLs if products exist
        try {
            if (class_exists('\App\Models\Product')) {
                $products = \App\Models\Product::where('is_active', true)
                    ->select('slug', 'updated_at')
                    ->get();
                
                foreach ($products as $product) {
                    $urls->push([
                        'url' => route('products.show', $product->slug),
                        'lastmod' => $product->updated_at->format('Y-m-d'),
                        'changefreq' => 'weekly',
                        'priority' => '0.7'
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently ignore if Product model doesn't exist or database error
        }

        // Add category URLs if categories exist
        try {
            if (class_exists('\App\Models\Category')) {
                $categories = \App\Models\Category::where('is_active', true)
                    ->select('slug', 'updated_at')
                    ->get();
                
                foreach ($categories as $category) {
                    $urls->push([
                        'url' => route('category.products', $category->slug),
                        'lastmod' => $category->updated_at->format('Y-m-d'),
                        'changefreq' => 'weekly',
                        'priority' => '0.8'
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently ignore if Category model doesn't exist or database error
        }

        // Add sales URLs if sales exist
        try {
            if (class_exists('\App\Models\Sale')) {
                $sales = \App\Models\Sale::where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now())
                    ->select('slug', 'updated_at')
                    ->get();
                
                foreach ($sales as $sale) {
                    $urls->push([
                        'url' => route('sales.show', $sale->slug),
                        'lastmod' => $sale->updated_at->format('Y-m-d'),
                        'changefreq' => 'daily',
                        'priority' => '0.9'
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently ignore if Sale model doesn't exist or database error
        }

        // Generate XML content
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['url']) . "</loc>\n";
            $xml .= "    <lastmod>" . $url['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }
        
        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
    }
}