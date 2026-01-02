<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
            $sessionId = $request->input('session_id', session()->getId());
            $userId = auth()->id();

            if (empty($userMessage)) {
                return response()->json(['error' => 'No message provided'], 400);
            }

            // Get user context and preferences
            $userContext = $this->getUserContext($userId, $sessionId);
            
            // Analyze user intent using NLP
            $intent = $this->analyzeUserIntent($userMessage, $history);
            
            // Track conversation analytics
            $this->trackConversationMetrics($userMessage, $intent, $userId);

            // Check for greetings first (before product queries)
            $greetingKeywords = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'greetings', 'howdy', 'hiya', 'what\'s up', 'whats up'];
            $userMessageLower = strtolower($userMessage);
            $isGreeting = false;
            
            // Check if message is a simple greeting
            foreach ($greetingKeywords as $greeting) {
                if ($userMessageLower === $greeting || strpos($userMessageLower, $greeting) === 0) {
                    $isGreeting = true;
                    break;
                }
            }
            
            // Handle greetings with personalized response
            if ($isGreeting) {
                $userName = auth()->check() ? auth()->user()->name : 'User';
                $greetingResponse = "Hello {$userName}! 😊<br><br>What would you like to do or talk about today? I'm here to help you with:<br><br>• Finding products and deals<br>• Shopping assistance and recommendations<br>• Order tracking and support<br>• Questions about our services<br><br>How can I help make your shopping experience better?";
                
                return response()->json([
                    'success' => true,
                    'reply' => $greetingResponse,
                    'timestamp' => now()->toDateTimeString()
                ]);
            }

            //Check if user is asking about products
            $productKeywords = ['product', 'have', 'available', 'sell', 'buy', 'price', 'cost', 'laptop', 'phone', 'shirt', 'shoe', 'item', 'what do you', 'do you have', 'show me', 'find', 'search', 'looking for', 'need', 'want', 'get', 'camera', 'mobile', 'watch', 'headphone', 'tablet', 'laptop', 'computer', 'keyboard', 'mouse', 'monitor', 'speaker', 'charger', 'cable', 'adapter'];
            $isProductQuery = false;
            
            // Only check for product keywords if it's not a greeting
            if (!$isGreeting) {
                foreach ($productKeywords as $keyword) {
                    if (strpos($userMessageLower, $keyword) !== false) {
                        $isProductQuery = true;
                        break;
                    }
                }
                
                // If not matched by keywords, check if message contains any meaningful words (potential product search)
                if (!$isProductQuery) {
                    $commonWords = ['what', 'do', 'you', 'have', 'any', 'show', 'me', 'tell', 'about', 'find', 'search', 'looking', 'for', 'i', 'can', 'could', 'would', 'please', 'the', 'a', 'an', 'is', 'are', 'in', 'on', 'at', 'to', 'from', 'by', 'with', 'or', 'and', 'products', 'product', 'this', 'that', 'these', 'those', 'my', 'your', 'his', 'her', 'its', 'our', 'their', 'be', 'been', 'being', 'has', 'had', 'does', 'did', 'will', 'would', 'should', 'could', 'may', 'might', 'must', 'shall', 'can', 'do', 'does', 'did', 'will', 'would', 'should', 'could', 'may', 'might', 'must', 'shall', 'hello', 'hi', 'hey', 'good', 'morning', 'afternoon', 'evening', 'greetings'];
                    $words = str_word_count(strtolower($userMessage), 1);
                    $meaningfulWords = array_diff($words, $commonWords);
                    
                    // If there are meaningful words (potential product names), treat as product query
                    // But only if message has more than just greeting words
                    if (!empty($meaningfulWords) && count($words) > 2) {
                        $isProductQuery = true;
                    }
                }
            }

            // If asking about products, search database first
            $productData = '';
            if ($isProductQuery) {
                // Extract search keyword from message
                $searchKeyword = $this->extractSearchKeyword($userMessage);
                
                
                if ($searchKeyword) {
                    $products = $this->getProductsForResponse($searchKeyword);
                    // Always include product data (even if empty) to tell AI no products found
                    $productData = $this->formatProductsForPrompt($products);
                }
            }

            // Build system prompt with site context
            $systemPrompt = $this->buildSystemPrompt($productData);

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
              
                $response = $this->getFallbackResponse($messages);
            }

            $botReply = $response['choices'][0]['message']['content'] ?? 'Sorry, I could not process your request.';

            return response()->json([
                'success' => true,
                'reply' => $botReply,
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
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
         
            
            // Direct database check for debugging
            $testProduct = Product::select('id', 'name', 'slug')->first();
            
            
            // If keyword is wildcard, get latest products (10-15 products)
            if ($keyword === '%') {
                $products = Product::select('id', 'name', 'description', 'price', 'slug')
                    ->orderBy('created_at', 'desc')
                    ->limit(15)
                    ->get();
                
            } else {
                // Fetch products matching keyword - search comprehensively
                $products = Product::where(function($query) use ($keyword) {
                        $query->where('name', 'LIKE', "%{$keyword}%")
                              ->orWhere('description', 'LIKE', "%{$keyword}%")
                              ->orWhere('slug', 'LIKE', "%{$keyword}%");
                    })
                    ->select('id', 'name', 'description', 'price', 'slug')
                    ->orderBy('created_at', 'desc')
                    ->limit(15)
                    ->get();
               
                
                // If no specific products found and not a general query, don't show all products
                if ($products->count() === 0 && !in_array(strtolower($keyword), ['%', 'products', 'items', 'all'])) {
                    
                    // Return empty to trigger "no products found" response
                    return [];
                }
            }
            $productsArray = $products->toArray();
            
            return $productsArray;
        } catch (\Exception $e) {
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
- Handle greetings warmly and offer assistance

GREETING RESPONSES:
When users say hello, hi, hey, or similar greetings, respond warmly by:
1. Greeting them back (use their name if available)
2. Add a friendly emoji (😊)
3. Ask what they'd like to do or talk about today
4. List key ways you can help (finding products, shopping assistance, order support, etc.)
5. End with "How can I help make your shopping experience better?"

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
        
        if (!$apiKey || empty(trim($apiKey))) {
           
            
            // Return a fallback response instead of error
            return $this->getFallbackResponse($messages);
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
           
            return ['error' => "API Error: {$error}"];
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
           
            return $this->getFallbackResponse($messages);
        }

        if (isset($data['error'])) {
           
            return $this->getFallbackResponse($messages);
        }

        return $data;
    }

    /**
     * Get fallback response when API is unavailable
     */
    private function getFallbackResponse($messages)
    {
        // Get the last user message
        $lastMessage = end($messages);
        $userMessage = $lastMessage['content'] ?? '';
      
        // Check if asking about products
        $productKeywords = ['product', 'have', 'available', 'sell', 'buy', 'price', 'cost', 'laptop', 'phone', 'shirt', 'shoe', 'item', 'what do you', 'do you have', 'show me', 'find', 'search', 'looking for', 'need', 'want', 'get', 'camera', 'mobile', 'watch', 'headphone', 'tablet', 'laptop', 'computer', 'keyboard', 'mouse', 'monitor', 'speaker', 'charger', 'cable', 'adapter'];
        $userMessageLower = strtolower($userMessage);
        $isProductQuery = false;
        
        foreach ($productKeywords as $keyword) {
            if (strpos($userMessageLower, $keyword) !== false) {
                $isProductQuery = true;
                break;
            }
        }
        
        // If not matched by keywords, check if message contains any meaningful words (potential product search)
        if (!$isProductQuery) {
            $commonWords = ['what', 'do', 'you', 'have', 'any', 'show', 'me', 'tell', 'about', 'find', 'search', 'looking', 'for', 'i', 'can', 'could', 'would', 'please', 'the', 'a', 'an', 'is', 'are', 'in', 'on', 'at', 'to', 'from', 'by', 'with', 'or', 'and', 'products', 'product', 'this', 'that', 'these', 'those', 'my', 'your', 'his', 'her', 'its', 'our', 'their', 'be', 'been', 'being', 'has', 'had', 'does', 'did', 'will', 'would', 'should', 'could', 'may', 'might', 'must', 'shall', 'can', 'do', 'does', 'did', 'will', 'would', 'should', 'could', 'may', 'might', 'must', 'shall'];
            $words = str_word_count(strtolower($userMessage), 1);
            $meaningfulWords = array_diff($words, $commonWords);
            
            // If there are meaningful words (potential product names), treat as product query
            if (!empty($meaningfulWords)) {
                $isProductQuery = true;
            }
        }
        
        if ($isProductQuery) {
            // Try to get products from database
            $searchKeyword = $this->extractSearchKeyword($userMessage);
            $products = $this->getProductsForResponse($searchKeyword);
            
            if (!empty($products)) {
                $response = "Here are our available products:\n\n";
                $counter = 1;
                foreach ($products as $product) {
                    $price = number_format($product['price'], 2);
                    $slug = isset($product['slug']) && !empty($product['slug']) ? $product['slug'] : 'product-' . $product['id'];
                    $url = url('/products/' . $slug);
                    
                    $response .= "{$counter}. {$product['name']} - ₹{$price}\n";
                    $response .= "   Description: {$product['description']}\n";
                    $response .= "   Link: {$url}\n\n";
                    $counter++;
                }
                $response .= "Is there anything specific you'd like to know about these products?";
            } else {
                $response = "Sorry, we don't have that product available right now. Please check our website for available options, or let me know if you need help with anything else.";
            }
        } else {
            // Generic helpful responses for common queries
            $commonResponses = [
                'shipping' => "We offer free shipping on orders above ₹500. Standard delivery takes 5-7 business days, and express delivery takes 2-3 business days.",
                'return' => "We have a 30-day return policy for unused items in original packaging. Please contact our support team for return authorization.",
                'payment' => "We accept credit/debit cards, digital wallets, bank transfers, and cash on delivery (where available).",
                'track' => "You can track your order by logging into your account and going to 'My Orders'. You'll see real-time tracking information there.",
                'support' => "You can reach our customer support via email or phone. We're available 9 AM - 9 PM IST and respond within 24 hours.",
                'account' => "You can create an account by clicking 'Sign Up' on our homepage. Just enter your email and password, then verify your email address."
            ];
            
            $response = "Hello! I'm here to help you with your shopping needs. ";
            
            // Try to match common topics
            foreach ($commonResponses as $topic => $answer) {
                if (strpos($userMessageLower, $topic) !== false) {
                    $response = $answer;
                    break;
                }
            }
            
            if ($response === "Hello! I'm here to help you with your shopping needs. ") {
                $response .= "You can ask me about our products, shipping, returns, payments, or any other questions about shopping with us. What would you like to know?";
            }
        }
        
        return [
            'choices' => [
                [
                    'message' => [
                        'content' => $response
                    ]
                ]
            ]
        ];
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
     * Get user context for personalized responses
     */
    private function getUserContext($userId, $sessionId)
    {
        $context = [
            'is_authenticated' => !is_null($userId),
            'session_id' => $sessionId,
            'preferences' => [],
            'purchase_history' => [],
            'browsing_behavior' => [],
            'cart_items' => [],
            'wishlist_items' => []
        ];

        if ($userId) {
            try {
                // Get user's purchase history
                $context['purchase_history'] = Order::where('user_id', $userId)
                    ->with('items.productVariation.product')
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(function($order) {
                        return [
                            'order_date' => $order->created_at->format('Y-m-d'),
                            'total' => $order->total,
                            'products' => $order->items->map(function($item) {
                                return [
                                    'name' => $item->productVariation->product->name ?? 'Unknown',
                                    'category' => $item->productVariation->product->category->name ?? 'Uncategorized',
                                    'price' => $item->price
                                ];
                            })
                        ];
                    });

                // Get current cart items
                $context['cart_items'] = \App\Models\CartItem::where('user_id', $userId)
                    ->with('productVariation.product')
                    ->get()
                    ->map(function($item) {
                        return [
                            'name' => $item->productVariation->product->name ?? 'Unknown',
                            'quantity' => $item->quantity,
                            'price' => $item->productVariation->price
                        ];
                    });

                // Get wishlist items
                $context['wishlist_items'] = \App\Models\WishlistItem::where('user_id', $userId)
                    ->with('productVariation.product')
                    ->get()
                    ->map(function($item) {
                        return [
                            'name' => $item->productVariation->product->name ?? 'Unknown',
                            'price' => $item->productVariation->price
                        ];
                    });

            } catch (\Exception $e) {
                \Log::error('Error getting user context: ' . $e->getMessage());
            }
        }

        return $context;
    }

    /**
     * Analyze user intent using advanced NLP techniques
     */
    private function analyzeUserIntent($message, $history)
    {
        $message = strtolower($message);
        
        $intents = [
            'product_search' => ['product', 'show', 'find', 'search', 'looking for', 'buy', 'purchase'],
            'price_inquiry' => ['price', 'cost', 'how much', 'expensive', 'cheap', 'budget'],
            'order_status' => ['order', 'tracking', 'shipped', 'delivered', 'status'],
            'support' => ['help', 'problem', 'issue', 'support', 'contact'],
            'recommendation' => ['recommend', 'suggest', 'best', 'popular', 'trending'],
            'comparison' => ['compare', 'difference', 'vs', 'versus', 'better'],
            'availability' => ['stock', 'available', 'in stock', 'out of stock'],
            'shipping' => ['delivery', 'shipping', 'when will', 'how long'],
            'return_refund' => ['return', 'refund', 'exchange', 'cancel'],
            'account' => ['login', 'register', 'account', 'profile', 'password']
        ];

        $scores = [];
        foreach ($intents as $intent => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $score += 1;
                }
            }
            $scores[$intent] = $score;
        }

        $primaryIntent = array_keys($scores, max($scores))[0];
        
        return [
            'primary' => $primaryIntent,
            'confidence' => max($scores) / count(explode(' ', $message)),
            'all_scores' => $scores
        ];
    }

    /**
     * Track conversation metrics and analytics
     */
    private function trackConversationMetrics($message, $intent, $userId)
    {
        try {
            // Store conversation data for analytics
            \DB::table('chatbot_conversations')->insert([
                'user_id' => $userId,
                'session_id' => session()->getId(),
                'message' => $message,
                'intent' => $intent['primary'],
                'confidence' => $intent['confidence'],
                'timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to track conversation metrics: ' . $e->getMessage());
        }
    }

    /**
     * Get personalized product recommendations
     */
    private function getPersonalizedRecommendations($userContext, $limit = 5)
    {
        try {
            $recommendations = [];
            
            if (!empty($userContext['purchase_history'])) {
                // Get categories from purchase history
                $purchasedCategories = collect($userContext['purchase_history'])
                    ->flatMap(function($order) {
                        return collect($order['products'])->pluck('category');
                    })
                    ->unique()
                    ->values();

                // Find similar products in same categories
                $categoryProducts = Product::whereHas('category', function($query) use ($purchasedCategories) {
                    $query->whereIn('name', $purchasedCategories->toArray());
                })
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

                $recommendations = array_merge($recommendations, $categoryProducts->toArray());
            }

            // If no purchase history, get trending/popular products
            if (empty($recommendations)) {
                $recommendations = Product::orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get()
                    ->toArray();
            }

            return $recommendations;
            
        } catch (\Exception $e) {
            \Log::error('Error getting recommendations: ' . $e->getMessage());
            return [];
        }
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
                ->orderBy('created_at', 'desc')
                ->limit(15)
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
            return response()->json(['error' => 'Failed to get product details'], 500);
        }
    }

    /**
     * Get real-time inventory status
     */
    public function getInventoryStatus(Request $request)
    {
        try {
            $productIds = $request->input('product_ids', []);
            
            if (empty($productIds)) {
                return response()->json(['error' => 'No product IDs provided'], 400);
            }

            $inventory = \App\Models\ProductVariation::whereIn('product_id', $productIds)
                ->with(['stock', 'product'])
                ->get()
                ->groupBy('product_id')
                ->map(function($variations) {
                    $totalStock = $variations->sum(function($v) {
                        return optional($v->stock)->quantity ?? 0;
                    });
                    
                    return [
                        'product_name' => $variations->first()->product->name,
                        'total_stock' => $totalStock,
                        'in_stock' => $totalStock > 0,
                        'low_stock' => $totalStock > 0 && $totalStock <= 5,
                        'variations_count' => $variations->count()
                    ];
                });

            return response()->json([
                'success' => true,
                'inventory' => $inventory
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get inventory status'], 500);
        }
    }

    /**
     * Handle voice messages (future feature)
     */
    public function handleVoiceMessage(Request $request)
    {
        // Placeholder for voice-to-text integration
        return response()->json([
            'success' => false,
            'message' => 'Voice messages not implemented yet'
        ]);
    }

    /**
     * Get chatbot analytics and performance metrics
     */
    public function getAnalytics(Request $request)
    {
        try {
            $period = $request->input('period', 'week'); // day, week, month
            
            $analytics = [
                'total_conversations' => \DB::table('chatbot_conversations')->count(),
                'unique_users' => \DB::table('chatbot_conversations')->distinct('user_id')->count(),
                'top_intents' => \DB::table('chatbot_conversations')
                    ->select('intent', \DB::raw('count(*) as count'))
                    ->groupBy('intent')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get(),
                'conversation_satisfaction' => 4.2, // Mock data - implement rating system
                'resolution_rate' => 0.85, // Mock data
                'average_response_time' => '2.3s' // Mock data
            ];

            return response()->json([
                'success' => true,
                'analytics' => $analytics,
                'period' => $period
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get analytics'], 500);
        }
    }

    /**
     * Smart product suggestions based on user behavior
     */
    public function getSmartSuggestions(Request $request)
    {
        try {
            $userContext = $this->getUserContext(auth()->id(), session()->getId());
            $currentMessage = $request->input('message', '');
            
            $suggestions = [];
            
            // If user has items in cart, suggest checkout
            if (!empty($userContext['cart_items'])) {
                $suggestions[] = [
                    'type' => 'action',
                    'text' => 'Complete your purchase - you have ' . count($userContext['cart_items']) . ' items in cart',
                    'action' => 'checkout',
                    'url' => route('cart.index')
                ];
            }

            // If user has wishlist items, suggest purchase
            if (!empty($userContext['wishlist_items'])) {
                $suggestions[] = [
                    'type' => 'action',
                    'text' => 'Check your wishlist - ' . count($userContext['wishlist_items']) . ' saved items',
                    'action' => 'wishlist',
                    'url' => route('wishlist.index')
                ];
            }

            // Personalized product recommendations
            $recommendations = $this->getPersonalizedRecommendations($userContext, 3);
            foreach ($recommendations as $product) {
                $suggestions[] = [
                    'type' => 'product',
                    'text' => "Check out: {$product['name']} - ₹" . number_format($product['price'], 2),
                    'action' => 'view_product',
                    'url' => url('/products/' . $product['slug'])
                ];
            }

            return response()->json([
                'success' => true,
                'suggestions' => array_slice($suggestions, 0, 5) // Limit to 5 suggestions
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get smart suggestions'], 500);
        }
    }
}
