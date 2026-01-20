<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Support\Facades\Cache;

class WelcomeController extends Controller
{
    public function index()
    {
        // Get active sliders for homepage with caching (1 week - for stable promotional banners)
        $sliders = Cache::remember('home_sliders', now()->addWeek(), function () {
            return Slider::active()->ordered()->get();
        });

        return view('welcome', compact('sliders'));
    }

    public function getFeaturedProducts(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 6);

        $products = Product::with(['images', 'variations', 'brand', 'category'])
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Transform products for frontend
        $transformedProducts = $products->getCollection()->map(function ($product) {
            // Calculate price range for products with variations
            if ($product->variations->count() > 0) {
                $prices = $product->variations->pluck('price')->filter();
                $minPrice = $prices->count() > 0 ? $prices->min() : $product->price;
                $maxPrice = $prices->count() > 0 ? $prices->max() : $product->price;
            } else {
                $minPrice = $product->price;
                $maxPrice = $product->price;
            }

            // Get first image
            $image = $product->images->first();
            $imageUrl = $image ? asset('storage/' . $image->path) : 'https://via.placeholder.com/400x300?text=No+Image';

            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $minPrice,
                'original_price' => $product->mrp,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'has_variations' => $product->variations->count() > 0,
                'rating' => 4.5, // You can implement real ratings later
                'reviews' => rand(50, 500), // Sample data
                'image' => $imageUrl,
                'category' => $product->category ? $product->category->name : 'Uncategorized',
                'brand' => $product->brand ? $product->brand->name : null,
                'in_stock' => $this->checkProductStock($product),
            ];
        });

        return response()->json([
            'products' => $transformedProducts,
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'has_more' => $products->hasMorePages(),
            'total' => $products->total(),
        ]);
    }

    private function checkProductStock($product)
    {
        if ($product->variations->count() > 0) {
            // Check if any variation has stock
            foreach ($product->variations as $variation) {
                if ($variation->stock && $variation->stock->quantity > 0) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    public function post()
    {
        return view('post');
    }

    public function generateDescription(Request $request)
    {
    $messages = [
        [
            "role" => "system",
            "content" => "You are an experienced HR professional and recruitment expert with expertise in creating comprehensive, engaging job descriptions that attract top talent."
        ],
        [
            "role" => "user",
            "content" => "
Create a comprehensive and professional job description in HTML format.

Job Title: {$request->title}
Category: {$request->category}
Sub Category: {$request->sub_category}

FORMATTING REQUIREMENTS:
- Output ONLY clean HTML (no markdown, no explanations)
- Use semantic HTML tags: <h3>, <h4>, <p>, <ul>, <li>, <strong>, <em>, <span>
- Add styling classes for better presentation
- Use bullet points extensively for readability
- Highlight key terms with <strong> and <em> tags
- Create visually appealing sections with proper spacing

CONTENT REQUIREMENTS:
- Minimum 400-600 words
- Write in an engaging, professional tone
- Be specific and detailed for the given job category
- Include industry-relevant keywords and requirements
- Make it attractive to potential candidates

REQUIRED SECTIONS:
1. Job Overview (compelling summary with highlights)
2. Key Responsibilities (detailed bullet points with sub-points)
3. Required Qualifications (education, experience, skills)
4. Preferred Skills & Experience (bonus qualifications)
5. What We Offer (benefits, growth opportunities)
6. Company Culture & Environment
7. How to Apply (call-to-action)

STYLE GUIDELINES:
- Use <h3> for main section headings
- Use <h4> for sub-sections
- Highlight important requirements with <strong>
- Use <em> for emphasis on benefits and opportunities
- Create nested <ul> and <li> for detailed lists
- Add <span class='highlight'> for standout information
- Include salary range hints where appropriate for the category
"
        ]
    ];

    $response = $this->callGroqAPI($messages);

    // Enhanced safety checks
    if (
        !isset($response['choices'][0]['message']['content']) ||
        strlen(strip_tags($response['choices'][0]['message']['content'] ?? '')) < 200
    ) {
        return response()->json([
            'description' => $this->getFallbackDescription($request->title, $request->category, $request->sub_category)
        ]);
    }

    return response()->json([
        'description' => trim($response['choices'][0]['message']['content'])
    ]);
}

private function getFallbackDescription($title, $category, $subCategory)
{
    return "
    <h3>Job Overview</h3>
    <p>We are seeking a dedicated <strong>{$title}</strong> to join our dynamic team in the <em>{$category}</em> department. This role focuses on <strong>{$subCategory}</strong> and offers excellent opportunities for professional growth.</p>
    
    <h3>Key Responsibilities</h3>
    <ul>
        <li><strong>Primary duties:</strong> Handle core responsibilities related to {$subCategory}</li>
        <li><strong>Team collaboration:</strong> Work closely with cross-functional teams</li>
        <li><strong>Quality assurance:</strong> Ensure high standards in all deliverables</li>
        <li><strong>Continuous improvement:</strong> Identify and implement process improvements</li>
    </ul>
    
    <h3>Required Qualifications</h3>
    <ul>
        <li>Relevant experience in <strong>{$category}</strong></li>
        <li>Strong communication and interpersonal skills</li>
        <li>Proven track record in <em>{$subCategory}</em></li>
        <li>Ability to work independently and as part of a team</li>
    </ul>
    
    <h3>What We Offer</h3>
    <ul>
        <li><span class='highlight'>Competitive salary package</span></li>
        <li><strong>Career growth opportunities</strong></li>
        <li><em>Flexible working arrangements</em></li>
        <li>Comprehensive benefits package</li>
    </ul>
    ";
}

private function callGroqAPI($messages)
    {
        $apiKey = config('services.groq.api_key');

        $payload = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => $messages,
        "temperature" => 0.7, // Increased for more creative descriptions
        "max_tokens" => 1500, // Increased to support longer descriptions
        "top_p" => 0.9,
        "stream" => false
    ];

    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer {$apiKey}"
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 45, // Increased timeout for longer responses
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        \Log::error('Groq Curl Error: ' . curl_error($ch));
        return ['error' => 'API connection failed'];
    }

    if ($httpCode !== 200) {
        \Log::error('Groq API Error: HTTP ' . $httpCode . ' - ' . $response);
        return ['error' => 'API request failed'];
    }    
    }
}
