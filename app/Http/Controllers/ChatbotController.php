<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Ecommerce Chatbot Controller
 * Handles all chatbot interactions with Groq API
 * Manages conversation history and site context
 */
class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        try {
            $userMessage = $request->input('message');
            $history = json_decode($request->input('history', '[]'), true);

            if (empty($userMessage)) {
                return response()->json(['error' => 'No message provided'], 400);
            }

            //Check if user is asking about products
            $productKeywords = ['product', 'have', 'available', 'sell', 'buy', 'price', 'cost', 'laptop', 'phone', 'shirt', 'shoe', 'item', 'what do you', 'do you have', 'show me', 'find', 'search', 'looking for'];
            $userMessageLower = strtolower($userMessage);
            $isProductQuery = false;
            
            foreach ($productKeywords as $keyword) {
                if (strpos($userMessageLower, $keyword) !== false) {
                    $isProductQuery = true;
                    break;
                }
            }

            // If asking about products, search database first
            $productData = '';
            if ($isProductQuery) {
                // Extract search keyword from message
                $searchKeyword = $this->extractSearchKeyword($userMessage);
                Log::info('Extracted search keyword', ['user_message' => $userMessage, 'keyword' => $searchKeyword]);
                
                if ($searchKeyword) {
                    $products = $this->getProductsForResponse($searchKeyword);
                    // Always include product data (even if empty) to tell AI no products found
                    $productData = $this->formatProductsForPrompt($products);
                }
            }

            // Build system prompt with site context
            $systemPrompt = $this->buildSystemPrompt($productData);

            // Debug: Log what's being sent to AI
            Log::info('System prompt being sent to AI', [
                'product_data_length' => strlen($productData),
                'product_data_preview' => substr($productData, 0, 500) . '...',
                'system_prompt_length' => strlen($systemPrompt),
                'full_product_data' => $productData
            ]);

            // Prepare messages
            $messages = [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
                ]
            ];

            // Add conversation history
            if (is_array($history) && !empty($history)) {
                $messages = array_merge($messages, $history);
            }

            // Add current user message
            $messages[] = [
                'role' => 'user',
                'content' => $userMessage
            ];

            // Call Groq API
            $response = $this->callGroqAPI($messages);

            if (isset($response['error'])) {
                Log::error('Groq API Error: ' . $response['error']);
                return response()->json(['error' => $response['error']], 500);
            }

            $botReply = $response['choices'][0]['message']['content'] ?? 'Sorry, I could not process your request.';

            return response()->json([
                'success' => true,
                'reply' => $botReply,
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            Log::error('Chatbot Error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get site context (for debugging)
     */
    public function getContext()
    {
        $context = $this->getSiteContextData();
        return response()->json($context);
    }

    /**
     * Extract search keyword from user message
     */
    private function extractSearchKeyword($message)
    {
        // Remove common words including "products" for general queries
        $commonWords = ['what', 'do', 'you', 'have', 'any', 'show', 'me', 'tell', 'about', 'find', 'search', 'looking', 'for', 'i', 'want', 'need', 'can', 'could', 'would', 'please', 'the', 'a', 'an', 'is', 'are', 'in', 'on', 'at', 'to', 'from', 'by', 'with', 'or', 'and', 'products', 'product'];
        
        $words = str_word_count(strtolower($message), 1);
        $keywords = array_diff($words, $commonWords);
        
        // For general product queries, return wildcard
        $generalQueries = ['what products do you have', 'show me products', 'what do you have', 'what items', 'show me items'];
        if (in_array(strtolower(trim($message)), $generalQueries)) {
            return '%';
        }
        
        // Return first meaningful keyword or join multiple
        if (!empty($keywords)) {
            return implode(' ', array_slice($keywords, 0, 3));
        }
        
        // If no keywords found, return wildcard to get all products
        return '%';
    }

    /**
     * Get products for response
     */
    private function getProductsForResponse($keyword)
    {
        try {
            // Debug: Check total products in database
            $totalProducts = Product::count();
            Log::info('Total products in database', ['count' => $totalProducts]);
            
            // Direct database check for debugging
            $testProduct = Product::select('id', 'name', 'slug')->first();
            Log::info('First product in database', ['product' => $testProduct ? $testProduct->toArray() : 'none']);
            
            // If keyword is wildcard, get all products
            if ($keyword === '%') {
                $products = Product::select('id', 'name', 'description', 'price', 'slug')
                    ->limit(5)
                    ->get();
                Log::info('Wildcard search results', ['count' => $products->count()]);
            } else {
                // Fetch products matching keyword - be more strict with matching
                $products = Product::where(function($query) use ($keyword) {
                        $query->where('name', 'LIKE', "%{$keyword}%")
                              ->orWhere('description', 'LIKE', "%{$keyword}%")
                              ->orWhere('slug', 'LIKE', "%{$keyword}%");
                    })
                    ->select('id', 'name', 'description', 'price', 'slug')
                    ->limit(5)
                    ->get();
                Log::info('Keyword search results', ['keyword' => $keyword, 'count' => $products->count()]);
                
                // If no specific products found and not a general query, don't show all products
                if ($products->count() === 0 && !in_array(strtolower($keyword), ['%', 'products', 'items', 'all'])) {
                    Log::info('No specific products found for keyword', ['keyword' => $keyword]);
                    // Return empty to trigger "no products found" response
                    return [];
                }
            }

            // Debug log to verify slug values
            foreach ($products as $product) {
                Log::info('Product retrieved', [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'slug_type' => gettype($product->slug)
                ]);
            }

            $productsArray = $products->toArray();
            
            // Debug the array conversion
            Log::info('Products converted to array', [
                'products_array' => $productsArray
            ]);

            return $productsArray;
        } catch (\Exception $e) {
            Log::error('Error getting products for response: ' . $e->getMessage());
            return [];
        }
    }
    /**
     * Format products for system prompt
     */
    private function formatProductsForPrompt($products)
    {
        if (empty($products)) {
            return "\n\nNO PRODUCTS FOUND IN DATABASE:\n" .
                   "CRITICAL: No products match the search criteria.\n" .
                   "You MUST respond EXACTLY: 'Sorry, we don't have that product available right now.'\n" .
                   "DO NOT suggest other products.\n" .
                   "DO NOT show alternatives.\n" .
                   "DO NOT mention other categories.\n" .
                   "DO NOT create fake products or links.\n" .
                   "JUST say the product is not available and ask if they need help with anything else.\n";
        }

        $formatted = "\n\nREAL PRODUCTS FROM OUR DATABASE:\n";
        $formatted .= "These are the ONLY products available. Do NOT make up or suggest products from other websites.\n\n";

        $counter = 1;
        foreach ($products as $product) {
            $price = number_format($product['price'], 2);
            
            // Validate slug exists and is not null
            $slug = isset($product['slug']) && !empty($product['slug']) ? $product['slug'] : 'product-' . $product['id'];
            
            // Force correct URL structure with explicit path using actual slug
            $url = url('/products/' . $slug);
            
            // Log the URL being generated for debugging
            Log::info('Generated product URL', [
                'product_id' => $product['id'],
                'product_name' => $product['name'], 
                'product_slug' => $slug,
                'original_slug' => $product['slug'] ?? 'null',
                'url' => $url
            ]);
            
            $formatted .= "{$counter}. {$product['name']} - ₹{$price}\n";
            $formatted .= "   Description: {$product['description']}\n";
            $formatted .= "   Link: {$url}\n";
            $formatted .= "   [SLUG: {$slug} | URL: {$url}]\n";
            $counter++;
        }
        $currentDomain = parse_url(config('app.url', url('/')), PHP_URL_HOST);
        $formatted .= "\nCRITICAL RULES - READ CAREFULLY:\n";
        $formatted .= "1. ONLY show products listed above from our database\n";
        $formatted .= "2. Do NOT suggest products from other websites or domains\n";
        $formatted .= "3. Do NOT make up product names or links\n";
        $formatted .= "4. If user asks for a product NOT in the list above, say: 'Sorry, we don't have that product available. Please check our website for available options.'\n";
        $formatted .= "5. ALWAYS use ONLY the EXACT links provided above (from {$currentDomain})\n";
        $formatted .= "6. Never suggest external domains - only use the exact links provided from our site\n";
        $formatted .= "7. Format each product on separate lines with Description and Link indented\n";
        $formatted .= "8. Add blank line between products\n";
        $formatted .= "9. Start response directly with '1. Product Name' (no intro text)\n";
        $formatted .= "10. After products, add brief closing message asking if they need help\n";
        $formatted .= "11. Do NOT suggest alternatives or similar products not in the database\n";
        $formatted .= "\nIMPORTANT: Copy the Link URLs EXACTLY as shown above. Do NOT modify, change, or create your own URLs.\n";
        $formatted .= "All URLs MUST start with: {$currentDomain}/products/\n";
        $formatted .= "NEVER use '/product/' - always use '/products/' (plural)\n";
        return $formatted;
    }
    /**
     * Build comprehensive system prompt
     */
    private function buildSystemPrompt($productData = '')
    {
        $siteContext = $this->getSiteContextData();

        $prompt = <<<PROMPT
You are a helpful ecommerce customer support chatbot for our online shopping website.

YOUR ROLE:
- Answer questions ONLY about our website, products, services, and shopping process
- Help customers find products, understand features, and complete purchases
- Provide information about orders, shipping, returns, and support
- Be friendly, professional, and concise

CRITICAL CONSTRAINTS - FOLLOW THESE EXACTLY:
- ONLY use products listed in the "REAL PRODUCTS FROM OUR DATABASE" section below
- If NO products are listed in the database section, say "Sorry, we don't have that product available"
- DO NOT create, invent, or mention any products not explicitly listed below
- DO NOT use product names like "vpro", "laptop", "phone" unless they appear in the database section
- DO NOT provide information about competitors or other websites
- DO NOT answer general questions unrelated to our site
- NEVER HALLUCINATE OR MAKE UP PRODUCTS, PRICES, OR LINKS
- ONLY RESPOND WITH PRODUCTS THAT ARE ACTUALLY IN THE DATABASE SECTION BELOW
- WHEN NO PRODUCTS MATCH: Do NOT suggest alternatives, do NOT show other products, just say not available

OUR WEBSITE INFORMATION:
{$siteContext['site_info']}

AVAILABLE PRODUCTS & CATEGORIES:
{$siteContext['products_info']}

SHOPPING PROCESS & FEATURES:
{$siteContext['features_info']}

HELP & SUPPORT:
{$siteContext['support_info']}

RESPONSE GUIDELINES:
1. Keep responses concise and helpful
2. Use product names and categories from our database
3. Provide specific details when available
4. Suggest related products when relevant
5. Always maintain focus on our site only
6. If customer asks for product recommendations, suggest based on available inventory
7. For order-related questions, guide them to their account or support
8. When showing products, ALWAYS format them as a numbered list with this exact format:
   
   1. Product Name - ₹Price
      Description: Product description here
      Link: Product URL here
   
   2. Product Name - ₹Price
      Description: Product description here
      Link: Product URL here
   
   3. Product Name - ₹Price
      Description: Product description here
      Link: Product URL here

9. ALWAYS include ALL product links provided in the database
10. Keep the numbered list format professional and clean
11. Add a brief closing message after the product list asking if they need more help

CRITICAL URL RULES:
- NEVER create your own URLs
- ALWAYS copy the exact URL provided in the database section
- URLs MUST contain '/products/' (plural) not '/product/' (singular)
- Example correct URL: http://localhost:8000/products/product-slug
- Example wrong URL: http://localhost:8000/product/product-slug
- If you see a URL with '/product/', it's WRONG - use '/products/' instead

{$productData}

PROMPT;

        return $prompt;
    }

    /**
     * Get all site context data
     */
    private function getSiteContextData()
    {
        // Temporarily disable cache for debugging (re-enable later for production)
        // Cache::forget('chatbot_site_context');
        
        // Cache context for 1 hour to improve performance
        return Cache::remember('chatbot_site_context', 3600, function () {
            return [
                'site_info' => $this->getSiteInfo(),
                'products_info' => $this->getProductsInfo(),
                'features_info' => $this->getFeaturesInfo(),
                'support_info' => $this->getSupportInfo()
            ];
        });
    }

    /**
     * Get basic site information
     */
    private function getSiteInfo()
    {
        $siteUrl = config('app.url', url('/'));
        $siteName = config('app.name', 'Your Ecommerce Store');
        
        $info = <<<INFO
Website Name: {$siteName}
Website URL: {$siteUrl}
Main Services:
- Online shopping with secure checkout
- Product search and filtering
- User accounts and order history
- Wishlist and save for later
- Product reviews and ratings
- Customer support and help center

Key Features:
- Fast and secure payment processing
- Multiple payment methods accepted
- Real-time order tracking
- Easy returns and exchanges
- Customer reviews and ratings
- Personalized recommendations

INFO;

        return $info;
    }

    /**
     * Get products and categories from database
     */
    private function getProductsInfo()
    {
        try {
            // Get all active categories
            $categories = Category::select('id', 'name', 'slug')
                ->limit(20)
                ->get();

            // Get top products (only active ones)
            $products = Product::select('id', 'name', 'description', 'price', 'category_id')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            $info = "CATEGORIES:\n";
            foreach ($categories as $cat) {
                $info .= "- {$cat->name}: {$cat->description}\n";
            }

            $info .= "\nTOP PRODUCTS:\n";
            foreach ($products as $prod) {
                $price = number_format($prod->price, 2);
                $info .= "- {$prod->name} (₹{$price}): {$prod->description}\n";
            }

            // Add product count
            $totalProducts = Product::count();
            $info .= "\nTotal Products Available: {$totalProducts}\n";

            return $info;

        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            return "We have a wide range of products available in multiple categories.";
        }
    }

    /**
     * Get shopping features and process
     */
    private function getFeaturesInfo()
    {
        $info = <<<INFO
SHOPPING PROCESS:
1. Browse Products: Use search or browse by category
2. View Details: Check product description, images, and reviews
3. Add to Cart: Select quantity and add items
4. Checkout: Review cart and proceed to payment
5. Payment: Choose payment method and complete purchase
6. Order Confirmation: Receive order confirmation email
7. Tracking: Track your order in real-time
8. Delivery: Receive your package
9. Returns: Easy 30-day return policy

FEATURES:
- Advanced search with filters
- Product comparison
- Wishlist to save favorites
- Save for Later option
- Customer reviews and ratings
- Personalized recommendations
- Secure checkout with SSL encryption
- Multiple payment options
- Order history and tracking
- Easy returns and exchanges

PAYMENT METHODS:
- Credit/Debit Cards
- Digital Wallets
- Bank Transfers
- Cash on Delivery (where available)

SHIPPING:
- Free shipping on orders above ₹500
- Express delivery available
- Real-time tracking
- Insured shipments

INFO;

        return $info;
    }

    /**
     * Get support and help information
     */
    private function getSupportInfo()
    {
        $domain = parse_url(config('app.url', url('/')), PHP_URL_HOST);
        $supportEmail = "support@{$domain}";
        
        $info = <<<INFO
CUSTOMER SUPPORT:
- Email: {$supportEmail}
- Phone: +91-XXXXXXXXXX
- Live Chat: Available 9 AM - 9 PM IST
- Response Time: Within 24 hours

COMMON QUESTIONS:

Q: How do I track my order?
A: Log into your account, go to "My Orders", and click on the order to see real-time tracking.

Q: What is your return policy?
A: We offer 30-day returns for unused items in original packaging. Contact support for return authorization.

Q: How long does delivery take?
A: Standard delivery: 5-7 business days. Express delivery: 2-3 business days.

Q: Is my payment secure?
A: Yes, we use SSL encryption and PCI-DSS compliance for all transactions.

Q: Can I cancel my order?
A: Orders can be cancelled within 24 hours of placement. Contact support immediately.

Q: Do you offer international shipping?
A: Currently, we ship within India only.

Q: How do I use a coupon code?
A: Enter the code at checkout in the "Promo Code" field before payment.

Q: What if my item arrives damaged?
A: Contact support with photos within 48 hours for replacement or refund.

Q: How do I create an account?
A: Click "Sign Up" on the homepage, enter your email and password, and verify your email.

Q: Can I change my order after placing it?
A: If the order hasn't shipped yet, contact support immediately to make changes.

INFO;

        return $info;
    }

    /**
     * Call Groq API
     */
    private function callGroqAPI($messages)
    {
        $apiKey = config('services.groq.api_key');

        if (!$apiKey) {
            Log::error('Groq API key not configured');
            return ['error' => 'API configuration error'];
        }

        $postData = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => $messages,
            "temperature" => 0.0, // Zero temperature for exact responses
            "max_tokens" => 512, // Shorter responses
            "top_p" => 0.1 // More focused responses
        ];

        $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer {$apiKey}"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Log::error('Groq API curl error: ' . $error);
            return ['error' => "API Error: {$error}"];
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            Log::error('Groq API HTTP error: ' . $httpCode, $data);
            return ['error' => $data['error']['message'] ?? 'API request failed'];
        }

        if (isset($data['error'])) {
            Log::error('Groq API error: ' . json_encode($data['error']));
            return ['error' => $data['error']['message'] ?? 'Unknown API error'];
        }

        return $data;
    }

    /**
     * Clear conversation history (optional)
     */
    public function clearHistory()
    {
        return response()->json(['success' => true, 'message' => 'History cleared']);
    }

    /**
     * Get suggested questions
     */
    public function getSuggestedQuestions()
    {
        $suggestions = [
            'What products do you have?',
            'How does the checkout process work?',
            'What is your return policy?',
            'How long does delivery take?',
            'Do you offer free shipping?',
            'How do I track my order?',
            'What payment methods do you accept?',
            'Can I cancel my order?'
        ];

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * Debug method to test URL generation
     */
    public function testUrls()
    {
        $products = Product::select('id', 'name', 'slug')->limit(5)->get();
        
        $urls = [];
        foreach ($products as $product) {
            $urls[] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'url_generated' => url('/products/' . $product->slug),
                'route_generated' => route('products.show', ['slug' => $product->slug])
            ];
        }
        
        return response()->json($urls);
    }

    /**
     * Search for products by name/keyword
     * This is called when chatbot mentions a product
     */
    public function searchProducts(Request $request)
    {
        try {
            $keyword = $request->input('keyword', '');
            
            if (empty($keyword) || strlen($keyword) < 2) {
                return response()->json(['error' => 'Keyword too short'], 400);
            }

            // Search products by name or description
            $products = Product::where(function($query) use ($keyword) {
                    $query->where('name', 'LIKE', "%{$keyword}%")
                          ->orWhere('description', 'LIKE', "%{$keyword}%");
                })
                ->select('id', 'name', 'description', 'price', 'slug', 'category_id')
                ->limit(10)
                ->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No products found for: ' . $keyword,
                    'products' => []
                ]);
            }

            // Format products with links
            $formattedProducts = $products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => '₹' . number_format($product->price, 2),
                    'slug' => $product->slug,
                    'url' => url('/products/' . $product->slug),
                    'category_id' => $product->category_id
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'count' => count($formattedProducts),
                'products' => $formattedProducts
            ]);

        } catch (\Exception $e) {
            Log::error('Product search error: ' . $e->getMessage());
            return response()->json(['error' => 'Search failed'], 500);
        }
    }

    /**
     * Get product details by ID or slug
     */
    public function getProductDetails(Request $request)
    {
        try {
            $productId = $request->input('product_id');
            $productSlug = $request->input('product_slug');

            $product = null;

            if ($productId) {
                $product = Product::with('category', 'images', 'variations')
                    ->find($productId);
            } elseif ($productSlug) {
                $product = Product::with('category', 'images', 'variations')
                    ->where('slug', $productSlug)
                    ->first();
            }

            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => '₹' . number_format($product->price, 2),
                    'slug' => $product->slug,
                    'url' => url('/products/' . $product->slug),
                    'category' => $product->category ? $product->category->name : 'N/A',
                    'images_count' => $product->images->count(),
                    'variations_count' => $product->variations->count(),
                    'in_stock' => $product->variations->sum(function($v) {
                        return optional($v->stock)->quantity ?? 0;
                    }) > 0
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get product details error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get product details'], 500);
        }
    }
}
