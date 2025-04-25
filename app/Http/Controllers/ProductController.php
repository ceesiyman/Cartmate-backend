<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API Endpoints for product management"
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/products/fetch",
     *     summary="Fetch product details from a URL",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"url"},
     *             @OA\Property(
     *                 property="url",
     *                 type="string",
     *                 description="Product URL. Supports both full and shortened URLs for all platforms.",
     *                 example="https://www.amazon.com/dp/B07ZPKN6YR or https://a.co/d/ijvysyu"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product fetched and stored successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid URL or missing parameters",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="URL is required")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch product details")
     *         )
     *     )
     * )
     */
    public function fetchProduct(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        try {
            $url = $request->url;
            
            // Check if product already exists with this URL
            $existingProduct = Product::where('original_url', $url)->first();
            if ($existingProduct) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product already exists',
                    'data' => $existingProduct
                ]);
            }
            
            // Determine the platform from the URL
            $platform = $this->determinePlatform($url);
            
            // Handle shortened URLs for all platforms
            $shortenedDomains = [
                'a.co' => 'amazon.com',
                'amzn.to' => 'amazon.com',
                'wmt.us' => 'walmart.com',
                'ebay.us' => 'ebay.com',
                'aliex.us' => 'aliexpress.com',
                'alib.us' => 'alibaba.com',
                'zara.us' => 'zara.com',
                'etsy.me' => 'etsy.com'
            ];
            
            // Check if it's a shortened URL
            $parsedUrl = parse_url($url);
            $host = $parsedUrl['host'] ?? '';
            
            if (array_key_exists($host, $shortenedDomains)) {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ])->withOptions([
                    'allow_redirects' => true,
                    'verify' => false
                ])->get($url);
                
                if ($response->successful()) {
                    $url = $response->effectiveUri()->__toString();
                    // Re-determine platform after URL expansion
                    $platform = $this->determinePlatform($url);
                }
            }
            
            // Set common headers
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'none',
                'Sec-Fetch-User' => '?1',
                'Cache-Control' => 'max-age=0',
                'sec-ch-ua' => '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"Windows"'
            ];
            
            // Add platform-specific headers
            if ($platform === 'aliexpress') {
                $headers['Cookie'] = 'aep_usuc_f=site=usa&c_tp=USD&region=US&b_locale=en_US&ae_u_c=1';
                $headers['Accept-Language'] = 'en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7';
            } elseif ($platform === 'etsy') {
                $headers = array_merge($headers, [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Cache-Control' => 'no-cache',
                    'Pragma' => 'no-cache',
                    'Sec-CH-UA-Full-Version-List' => '"Not_A Brand";v="8.0.0.0", "Chromium";v="120.0.0.0", "Google Chrome";v="120.0.0.0"',
                    'Sec-CH-UA-Bitness' => '"64"',
                    'Sec-CH-UA-Model' => '""',
                    'Sec-CH-UA-Platform-Version' => '"15.0.0"',
                    'DNT' => '1',
                    'Referer' => 'https://www.google.com/',
                    'Cookie' => 'uaid=' . uniqid() . '; user_prefs=CgkIChIGcHJlZnMaAA; fve=' . time()
                ]);
            } elseif ($platform === 'walmart') {
                $headers = array_merge($headers, [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Connection' => 'keep-alive',
                    'Upgrade-Insecure-Requests' => '1',
                    'Sec-Fetch-Dest' => 'document',
                    'Sec-Fetch-Mode' => 'navigate',
                    'Sec-Fetch-Site' => 'none',
                    'Sec-Fetch-User' => '?1',
                    'Cache-Control' => 'max-age=0',
                    'sec-ch-ua' => '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
                    'sec-ch-ua-mobile' => '?0',
                    'sec-ch-ua-platform' => '"Windows"',
                    'DNT' => '1',
                    'Referer' => 'https://www.google.com/',
                    'Cookie' => 'customerId=' . uniqid() . '; storeId=0; marketId=1;'
                ]);
            }
            
            // Make the HTTP request with options
            $response = Http::withHeaders($headers)
                ->withOptions([
                    'allow_redirects' => true,
                    'verify' => false,
                    'timeout' => 30,
                    'connect_timeout' => 10
                ]);
                
            // Add proxy support if configured
            if (config('services.proxy.enabled')) {
                $response = $response->withOptions([
                    'proxy' => config('services.proxy.url')
                ]);
            }
            
            // Make the request
            $response = $response->get($url);
            
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch the product page: ' . $response->status());
            }
            
            $html = $response->body();
            
            // Enhanced bot detection checks
            $botDetectionPhrases = [
                'Robot Check',
                'captcha',
                'security check',
                'To discuss automated access to Amazon data please contact',
                'Please verify you are a human',
                'Access Denied',
                'detected unusual traffic',
                'automated requests',
                'suspicious activity'
            ];
            
            // Add platform-specific bot detection phrases
            if ($platform === 'etsy') {
                $botDetectionPhrases = array_merge($botDetectionPhrases, [
                    'Sorry, we\'re experiencing technical difficulties',
                    'Your request was blocked',
                    'Please confirm you are a human',
                    'We\'ve detected unusual activity',
                    'temporarily unavailable',
                    'Too Many Requests',
                    'rate limit exceeded',
                    'unusual traffic pattern',
                    'automated browsing behavior',
                    'verify your identity',
                    'our security system has been activated',
                    'our site is currently unavailable in your region'
                ]);
                
                // Check for Cloudflare protection
                if (str_contains($html, 'cf-browser-verification') || 
                    str_contains($html, 'cf-challenge') || 
                    str_contains($html, '_cf_chl_opt')) {
                    Log::warning("Cloudflare protection detected for Etsy URL: $url");
                    throw new \Exception("This website is currently using enhanced protection. Please try again later.");
                }
            } elseif ($platform === 'walmart') {
                $botDetectionPhrases = array_merge($botDetectionPhrases, [
                    'Please verify you are a human',
                    'Security Check',
                    'Access Denied',
                    'Bot Protection',
                    'unusual activity',
                    'suspicious activity',
                    'automated access',
                    'security verification',
                    'verification required',
                    'verify you are human',
                    'verify you are not a robot',
                    'verify you are not automated',
                    'verify you are not a bot'
                ]);
                
                // Check for Walmart-specific bot detection
                if (str_contains($html, 'security check') || 
                    str_contains($html, 'verify you are human') || 
                    str_contains($html, 'bot protection')) {
                    Log::warning("Walmart bot protection detected for URL: $url");
                    throw new \Exception("Walmart is currently blocking automated access. Please try again later or use a different approach.");
                }
            }
            
            foreach ($botDetectionPhrases as $phrase) {
                if (str_contains($html, $phrase)) {
                    Log::warning("Bot detection triggered for $platform: $phrase");
                    throw new \Exception("This website is currently blocking automated access. Please try again later or use a different approach.");
                }
            }
            
            // Parse the HTML based on the platform
            $productData = $this->parseProductHtml($html, $platform, $url);
            
            // Add the original URL to the product data
            $productData['original_url'] = $url;
            
            // Generate similar products
            $productData['similar_products'] = $this->generateSimilarProducts($productData);
            
            // Use firstOrCreate to handle potential race conditions
            $product = Product::firstOrCreate(
                ['original_url' => $url],
                $productData
            );
            
            return response()->json([
                'success' => true,
                'message' => $product->wasRecentlyCreated ? 'Product fetched and stored successfully' : 'Product already exists',
                'data' => $product
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching product: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get all products",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $products = Product::all();
        
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get a specific product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/trending",
     *     summary="Get trending products based on cart additions",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of trending products to return",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trending products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Product Name"),
     *                     @OA\Property(property="original_price", type="number", format="float", example=99.99),
     *                     @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
     *                     @OA\Property(property="store", type="string", example="Amazon"),
     *                     @OA\Property(property="cart_count", type="integer", example=42)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function trending(Request $request)
    {
        $limit = $request->input('limit', 4);

        $trendingProducts = Product::select([
            'products.id',
            'products.name',
            'products.original_price',
            'products.discounted_price',
            'products.image',
            'products.description',
            'products.store',
            'products.original_url',
            'products.shipping',
            'products.customs',
            'products.service_fee',
            'products.vat',
            'products.total_price',
            'products.images',
            'products.similar_products',
            'products.features',
            'products.specifications',
            'products.brand',
            'products.category',
            'products.rating',
            'products.review_count',
            'products.in_stock',
            'products.sku',
            'products.additional_info',
            'products.created_at',
            'products.updated_at'
        ])
        ->selectRaw('COUNT(carts.id) as cart_count')
        ->leftJoin('carts', 'products.id', '=', 'carts.product_id')
        ->groupBy([
            'products.id',
            'products.name',
            'products.original_price',
            'products.discounted_price',
            'products.image',
            'products.description',
            'products.store',
            'products.original_url',
            'products.shipping',
            'products.customs',
            'products.service_fee',
            'products.vat',
            'products.total_price',
            'products.images',
            'products.similar_products',
            'products.features',
            'products.specifications',
            'products.brand',
            'products.category',
            'products.rating',
            'products.review_count',
            'products.in_stock',
            'products.sku',
            'products.additional_info',
            'products.created_at',
            'products.updated_at'
        ])
        ->orderBy('cart_count', 'desc')
        ->limit($limit)
        ->get();

        return response()->json([
            'success' => true,
            'data' => $trendingProducts
        ]);
    }

    /**
     * Scrape product details from a URL
     *
     * @param string $url
     * @return array
     */
    private function scrapeProduct($url)
    {
        // Determine the platform from the URL
        $platform = $this->determinePlatform($url);
        
        // Handle shortened URLs for all platforms
        $shortenedDomains = [
            'a.co' => 'amazon.com',
            'amzn.to' => 'amazon.com',
            'wmt.us' => 'walmart.com',
            'ebay.us' => 'ebay.com',
            'aliex.us' => 'aliexpress.com',
            'alib.us' => 'alibaba.com',
            'zara.us' => 'zara.com',
            'etsy.me' => 'etsy.com'
        ];
        
        // Check if it's a shortened URL
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? '';
        
        if (array_key_exists($host, $shortenedDomains)) {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ])->withOptions([
                'allow_redirects' => true,
                'verify' => false
            ])->get($url);
            
            if ($response->successful()) {
                $url = $response->effectiveUri()->__toString();
                // Re-determine platform after URL expansion
                $platform = $this->determinePlatform($url);
            }
        }
        
        // Set common headers
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Sec-Fetch-User' => '?1',
            'Cache-Control' => 'max-age=0',
            'sec-ch-ua' => '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"'
        ];
        
        // Add platform-specific headers
        if ($platform === 'aliexpress') {
            $headers['Cookie'] = 'aep_usuc_f=site=usa&c_tp=USD&region=US&b_locale=en_US&ae_u_c=1';
            $headers['Accept-Language'] = 'en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7';
        } elseif ($platform === 'etsy') {
            $headers = array_merge($headers, [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
                'Sec-CH-UA-Full-Version-List' => '"Not_A Brand";v="8.0.0.0", "Chromium";v="120.0.0.0", "Google Chrome";v="120.0.0.0"',
                'Sec-CH-UA-Bitness' => '"64"',
                'Sec-CH-UA-Model' => '""',
                'Sec-CH-UA-Platform-Version' => '"15.0.0"',
                'DNT' => '1',
                'Referer' => 'https://www.google.com/',
                'Cookie' => 'uaid=' . uniqid() . '; user_prefs=CgkIChIGcHJlZnMaAA; fve=' . time()
            ]);
        } elseif ($platform === 'walmart') {
            $headers = array_merge($headers, [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'none',
                'Sec-Fetch-User' => '?1',
                'Cache-Control' => 'max-age=0',
                'sec-ch-ua' => '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"Windows"',
                'DNT' => '1',
                'Referer' => 'https://www.google.com/',
                'Cookie' => 'customerId=' . uniqid() . '; storeId=0; marketId=1;'
            ]);
        }
        
        // Make the HTTP request with options
        $response = Http::withHeaders($headers)
            ->withOptions([
                'allow_redirects' => true,
                'verify' => false,
                'timeout' => 30,
                'connect_timeout' => 10
            ]);
            
        // Add proxy support if configured
        if (config('services.proxy.enabled')) {
            $response = $response->withOptions([
                'proxy' => config('services.proxy.url')
            ]);
        }
        
        // Make the request
        $response = $response->get($url);
        
        if (!$response->successful()) {
            throw new \Exception('Failed to fetch the product page: ' . $response->status());
        }
        
        $html = $response->body();
        
        // Enhanced bot detection checks
        $botDetectionPhrases = [
            'Robot Check',
            'captcha',
            'security check',
            'To discuss automated access to Amazon data please contact',
            'Please verify you are a human',
            'Access Denied',
            'detected unusual traffic',
            'automated requests',
            'suspicious activity'
        ];
        
        // Add platform-specific bot detection phrases
        if ($platform === 'etsy') {
            $botDetectionPhrases = array_merge($botDetectionPhrases, [
                'Sorry, we\'re experiencing technical difficulties',
                'Your request was blocked',
                'Please confirm you are a human',
                'We\'ve detected unusual activity',
                'temporarily unavailable',
                'Too Many Requests',
                'rate limit exceeded',
                'unusual traffic pattern',
                'automated browsing behavior',
                'verify your identity',
                'our security system has been activated',
                'our site is currently unavailable in your region'
            ]);
            
            // Check for Cloudflare protection
            if (str_contains($html, 'cf-browser-verification') || 
                str_contains($html, 'cf-challenge') || 
                str_contains($html, '_cf_chl_opt')) {
                Log::warning("Cloudflare protection detected for Etsy URL: $url");
                throw new \Exception("This website is currently using enhanced protection. Please try again later.");
            }
        } elseif ($platform === 'walmart') {
            $botDetectionPhrases = array_merge($botDetectionPhrases, [
                'Please verify you are a human',
                'Security Check',
                'Access Denied',
                'Bot Protection',
                'unusual activity',
                'suspicious activity',
                'automated access',
                'security verification',
                'verification required',
                'verify you are human',
                'verify you are not a robot',
                'verify you are not automated',
                'verify you are not a bot'
            ]);
            
            // Check for Walmart-specific bot detection
            if (str_contains($html, 'security check') || 
                str_contains($html, 'verify you are human') || 
                str_contains($html, 'bot protection')) {
                Log::warning("Walmart bot protection detected for URL: $url");
                throw new \Exception("Walmart is currently blocking automated access. Please try again later or use a different approach.");
            }
        }
        
        foreach ($botDetectionPhrases as $phrase) {
            if (str_contains($html, $phrase)) {
                Log::warning("Bot detection triggered for $platform: $phrase");
                throw new \Exception("This website is currently blocking automated access. Please try again later or use a different approach.");
            }
        }
        
        // Parse the HTML based on the platform
        $productData = $this->parseProductHtml($html, $platform, $url);
        
        // Add the original URL to the product data
        $productData['original_url'] = $url;
        
        // Generate similar products
        $productData['similar_products'] = $this->generateSimilarProducts($productData);
        
        return $productData;
    }
    
    /**
     * Determine the e-commerce platform from the URL
     *
     * @param string $url
     * @return string
     */
    private function determinePlatform($url)
    {
        $platforms = [
            'amazon.com' => 'amazon',
            'walmart.com' => 'walmart',
            'ebay.com' => 'ebay',
            'aliexpress.com' => 'aliexpress',
            'alibaba.com' => 'alibaba',
            'zara.com' => 'zara',
            'etsy.com' => 'etsy'
        ];
        
        foreach ($platforms as $domain => $platform) {
            if (str_contains($url, $domain)) {
                return $platform;
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Parse product HTML based on the platform
     *
     * @param string $html
     * @param string $platform
     * @param string $url
     * @return array
     */
    private function parseProductHtml($html, $platform, $url)
    {
        $crawler = new Crawler($html);
        
        $productData = [
            'name' => '',
            'original_price' => 0,
            'discounted_price' => null,
            'image' => '',
            'description' => '',
            'store' => ucfirst($platform),
            'original_url' => $url,
            'shipping' => 0,
            'customs' => 0,
            'service_fee' => 0,
            'vat' => 0,
            'total_price' => 0,
            'images' => [],
            'features' => [],           // New
            'specifications' => [],     // New
            'brand' => null,           // New
            'category' => null,        // New
            'rating' => null,          // New
            'review_count' => 0,       // New
            'in_stock' => true,        // New
            'sku' => null,             // New
            'additional_info' => []    // New
        ];
        
        switch ($platform) {
            case 'amazon':
                $productData = $this->parseAmazonProduct($crawler, $productData);
                break;
            case 'walmart':
                $productData = $this->parseWalmartProduct($crawler, $productData);
                break;
            case 'ebay':
                $productData = $this->parseEbayProduct($crawler, $productData);
                break;
            case 'aliexpress':
                $productData = $this->parseAliexpressProduct($crawler, $productData);
                break;
            case 'alibaba':
                $productData = $this->parseAlibabaProduct($crawler, $productData);
                break;
            case 'zara':
                $productData = $this->parseZaraProduct($crawler, $productData);
                break;
            case 'etsy':
                $productData = $this->parseEtsyProduct($crawler, $productData);
                break;
            default:
                $productData = $this->parseGenericProduct($crawler, $productData);
                break;
        }
        
        // Calculate additional fees
        $productData['shipping'] = 25.0;
        $productData['customs'] = 18.5;
        $productData['service_fee'] = 12.99;
        $productData['vat'] = $productData['original_price'] * 0.2; // 20% VAT
        $productData['total_price'] = $productData['original_price'] + 
                                     $productData['shipping'] + 
                                     $productData['customs'] + 
                                     $productData['service_fee'] + 
                                     $productData['vat'];
        
        return $productData;
    }
    
    /**
     * Parse Amazon product
     *
     * @param Crawler $crawler
     * @param array $productData
     * @return array
     */
    private function parseAmazonProduct($crawler, $productData)
    {
        try {
            // Extract product name
            $productData['name'] = $this->getFirstAvailableText($crawler, [
                '#productTitle',
                'h1#title',
                'span#productTitle'
            ]) ?? 'Unknown Product';
            
            // Extract price
            $priceText = $this->getFirstAvailableText($crawler, [
                'span.a-price .a-offscreen',
                '#priceblock_ourprice',
                '#priceblock_saleprice',
                '.a-price .a-price-whole',
                'span[data-a-color="price"] .a-offscreen'
            ]) ?? '';
            $productData['original_price'] = $this->extractPrice($priceText);
            
            // Extract discounted price
            $discountedPriceText = $this->getFirstAvailableText($crawler, [
                '#priceblock_dealprice',
                '#priceblock_saleprice',
                '.savingsPercentage + .a-price .a-offscreen'
            ]) ?? '';
            if ($discountedPriceText) {
                $productData['discounted_price'] = $this->extractPrice($discountedPriceText);
            }
            
            // Extract image
            $productData['image'] = $this->getFirstAvailableAttribute($crawler, [
                '#landingImage',
                '#imgBlkFront',
                '#main-image',
                'img[data-a-image-name="landingImage"]'
            ], 'src') ?? '';
            
            // Extract description
            $productData['description'] = $this->getFirstAvailableText($crawler, [
                '#productDescription',
                '#feature-bullets',
                '#productDetails_feature_div'
            ]) ?? '';
            
            // Extract additional images
            $productData['images'] = [];
            $imagePaths = [
                '#altImages .a-button-thumbnail img',
                '#imageBlock_feature_div .item img',
                '.imageThumbnail img'
            ];
            foreach ($imagePaths as $path) {
                try {
                    $crawler->filter($path)->each(function (Crawler $node) use (&$productData) {
                        $imgSrc = $node->attr('src');
                        if ($imgSrc) {
                            $highResSrc = preg_replace('/\._[^\.]+_\./', '.', $imgSrc);
                            if (!in_array($highResSrc, $productData['images'])) {
                                $productData['images'][] = $highResSrc;
                            }
                        }
                    });
                    if (!empty($productData['images'])) {
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // Extract features (from feature bullets)
            $productData['features'] = [];
            try {
                $crawler->filter('#feature-bullets ul li')->each(function (Crawler $node) use (&$productData) {
                    $feature = trim($node->text());
                    if ($feature) {
                        $productData['features'][] = $feature;
                    }
                });
            } catch (\Exception $e) {
                Log::warning('Failed to extract Amazon features: ' . $e->getMessage());
            }
            
            // Extract specifications (from product details or technical details)
            $productData['specifications'] = [];
            try {
                $crawler->filter('#productDetails_techSpec_section_1 tr, #productDetails_detailBullets_sections1 tr')->each(function (Crawler $node) use (&$productData) {
                    $key = trim($node->filter('th')->text());
                    $value = trim($node->filter('td')->text());
                    if ($key && $value) {
                        $productData['specifications'][$key] = $value;
                    }
                });
            } catch (\Exception $e) {
                Log::warning('Failed to extract Amazon specifications: ' . $e->getMessage());
            }
            
            // Extract brand
            $productData['brand'] = $this->getFirstAvailableText($crawler, [
                '#bylineInfo',
                '#brand',
                'a#bylineInfo'
            ]) ?? null;
            
            // Extract category (from breadcrumb)
            $productData['category'] = null;
            try {
                $categories = $crawler->filter('#wayfinding-breadcrumbs_feature_div ul li a')->each(function (Crawler $node) {
                    return trim($node->text());
                });
                $productData['category'] = !empty($categories) ? implode(' > ', $categories) : null;
            } catch (\Exception $e) {
                Log::warning('Failed to extract Amazon category: ' . $e->getMessage());
            }
            
            // Extract rating
            $ratingText = $this->getFirstAvailableText($crawler, [
                '#acrPopover .a-declarative .a-size-base',
                '.reviewCountTextLinkedHistogram .a-size-base'
            ]) ?? '';
            $productData['rating'] = $ratingText ? floatval(preg_replace('/[^0-9.]/', '', $ratingText)) : null;
            
            // Extract review count
            $reviewCountText = $this->getFirstAvailableText($crawler, [
                '#acrCustomerReviewText',
                '.averageStarRatingNumerical .a-size-base'
            ]) ?? '';
            $productData['review_count'] = $reviewCountText ? (int) preg_replace('/[^0-9]/', '', $reviewCountText) : 0;
            
            // Extract stock status
            $stockText = $this->getFirstAvailableText($crawler, [
                '#availability .a-size-medium',
                '#availabilityInsideBuyBox_feature_div .a-size-medium'
            ]) ?? '';
            $productData['in_stock'] = str_contains(strtolower($stockText), 'in stock') || empty($stockText);
            
            // Extract SKU (ASIN for Amazon)
            $productData['sku'] = $this->getFirstAvailableText($crawler, [
                '#productDetails_detailBullets_sections1 tr:contains("ASIN") td',
                '#ASIN'
            ]) ?? null;
            
            // Extract additional info (e.g., color, size, etc.)
            $productData['additional_info'] = [];
            try {
                $crawler->filter('#variation_color_name .selection, #variation_size_name .selection')->each(function (Crawler $node) use (&$productData) {
                    $key = str_contains($node->ancestors()->attr('id'), 'color') ? 'Color' : 'Size';
                    $productData['additional_info'][$key] = trim($node->text());
                });
            } catch (\Exception $e) {
                Log::warning('Failed to extract Amazon additional info: ' . $e->getMessage());
            }
            
            // Set store
            $productData['store'] = 'Amazon';
            
        } catch (\Exception $e) {
            Log::error('Error parsing Amazon product: ' . $e->getMessage());
            throw new \Exception('Failed to parse Amazon product details');
        }
        
        return $productData;
    }
    
    /**
     * Helper function to get the first available text from multiple selectors
     */
    private function getFirstAvailableText($crawler, array $selectors)
    {
        foreach ($selectors as $selector) {
            try {
                $node = $crawler->filter($selector);
                if ($node->count() > 0) {
                    return trim($node->text());
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return null;
    }
    
    /**
     * Helper function to get the first available attribute from multiple selectors
     */
    private function getFirstAvailableAttribute($crawler, array $selectors, string $attribute)
    {
        foreach ($selectors as $selector) {
            try {
                $node = $crawler->filter($selector);
                if ($node->count() > 0) {
                    return $node->attr($attribute);
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return null;
    }
    
    /**
     * Parse Walmart product
     *
     * @param Crawler $crawler
     * @param array $productData
     * @return array
     */
    private function parseWalmartProduct($crawler, $productData)
    {
        // Extract product name
        $productData['name'] = $crawler->filter('h1[itemprop="name"]')->text() ?? 'Unknown Product';
        
        // Extract price
        $priceText = $crawler->filter('[data-automation-id="product-price"]')->text() ?? '';
        $productData['original_price'] = $this->extractPrice($priceText);
        
        // Extract image
        $productData['image'] = $crawler->filter('.hover-zoom-hero-image')->attr('src') ?? '';
        
        // Extract description
        $productData['description'] = $crawler->filter('[data-automation-id="product-description"]')->text() ?? '';
        
        return $productData;
    }
    
    /**
     * Parse eBay product
     *
     * @param Crawler $crawler
     * @param array $productData
     * @return array
     */
    private function parseEbayProduct($crawler, $productData)
    {
        try {
            // Extract product name
            $productData['name'] = $this->getFirstAvailableText($crawler, [
                'h1.x-item-title__mainTitle',
                '.x-item-title__mainTitle .ux-textspans' // Fallback
            ]) ?? 'Unknown Product';
            
            // Extract price - prioritize Buy It Now price
            $priceText = $this->getFirstAvailableText($crawler, [
                '.x-bin-price__amount .ux-textspans', // Buy It Now price
                '.x-price-primary .ux-textspans',      // Primary price (might be bid or BIN)
                'span[itemprop="price"]'             // Microdata price
            ]) ?? '';
            $productData['original_price'] = $this->extractPrice($priceText);
            
            // Extract image
            $productData['image'] = $this->getFirstAvailableAttribute($crawler, [
                '.ux-image-carousel-item.active img', // Active carousel image
                '.ux-image-magnify__image--original', // Magnified image
                'img[data-zoom-src]',                // Zoom image source
                '.ux-image-grid-container img'      // First image in grid
            ], 'src') ?? '';
            
            // Fallback for image source attribute
            if (empty($productData['image'])) {
                $productData['image'] = $this->getFirstAvailableAttribute($crawler, [
                    'img[data-zoom-src]' 
                ], 'data-zoom-src') ?? '';
            }

            // Extract description - Check iframe first
            $descriptionHtml = '';
            try {
                $iframeNode = $crawler->filter('#desc_ifr');
                if ($iframeNode->count() > 0) {
                    $iframeSrc = $iframeNode->attr('src');
                    if ($iframeSrc) {
                        // Need to fetch the iframe content separately
                        $iframeResponse = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($iframeSrc);
                        if ($iframeResponse->successful()) {
                            $iframeCrawler = new Crawler($iframeResponse->body());
                            $descriptionHtml = $iframeCrawler->filter('#ds_div')->html() ?? '';
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not process eBay description iframe: ' . $e->getMessage());
            }
            
            // If iframe failed or doesn't exist, try direct selectors
            if (empty($descriptionHtml)) {
                 $descriptionHtml = $this->getFirstAvailableHtml($crawler, [
                    '#desc_div', 
                    '#ds_div', // Common description container IDs
                    '.item-description' // Original fallback
                ]) ?? '';
            }
            
            // Clean the description HTML (optional: remove unwanted tags)
            $productData['description'] = strip_tags($descriptionHtml); // Simple text version
            
            // Set store
            $productData['store'] = 'eBay';
            
        } catch (\Exception $e) {
            Log::error('Error parsing eBay product: ' . $e->getMessage());
            Log::error('Stack trace for eBay parsing error: ' . $e->getTraceAsString());
            throw new \Exception('Failed to parse eBay product details: ' . $e->getMessage());
        }
        
        return $productData;
    }
    
    /**
     * Helper function to get the first available HTML content from multiple selectors
     */
    private function getFirstAvailableHtml($crawler, array $selectors)
    {
        foreach ($selectors as $selector) {
            try {
                $node = $crawler->filter($selector);
                if ($node->count() > 0) {
                    return $node->html();
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return null;
    }
    
    /**
     * Parse AliExpress product
     *
     * @param Crawler $crawler
     * @param array $productData
     * @return array
     */
    private function parseAliexpressProduct($crawler, $productData)
    {
        try {
            $html = $crawler->html();
            
            // Debug: Log the patterns we're trying
            Log::info('Attempting to parse AliExpress product data');
            
            // Try different patterns to find the data
            $patterns = [
                '/window\.__INITIAL_DATA__\s*=\s*({.+?});/s',
                '/window\.__DATA__\s*=\s*({.+?});/s',
                '/window\.__INIT_DATA__\s*=\s*({.+?});/s',
                '/data:\s*({.+?})\s*};/s',
                '/"data":({.+?}),"csrfToken"/s',
                '/window\._dida_config_\._init_data_\s*=\s*({.+?});/s',
                '/window\._dida_config_\s*=\s*({.+?});/s',
                '/window\.__AER_DATA__\s*=\s*({.+?});/s',
                '/window\.__PRELOADED_STATE__\s*=\s*({.+?});/s'
            ];
            
            $jsonData = null;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    try {
                        $jsonData = json_decode($matches[1], true);
                        if ($jsonData) {
                            Log::info('Successfully matched pattern: ' . $pattern);
                            Log::info('JSON structure: ' . json_encode(array_keys($jsonData)));
                            break;
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to decode JSON from pattern: ' . $pattern . ' - ' . $e->getMessage());
                        continue;
                    }
                }
            }
            
            if ($jsonData) {
                // Try different known JSON structures
                $data = $jsonData['data'] ?? $jsonData;
                
                // Debug: Log the structure we found
                Log::info('Found data structure: ' . json_encode(array_keys($data)));
                
                // Extract product name
                if (!empty($data['metaDataModule']['title'])) {
                    $productData['name'] = $data['metaDataModule']['title'];
                } elseif (!empty($data['titleModule']['subject'])) {
                    $productData['name'] = $data['titleModule']['subject'];
                } elseif (!empty($data['productInfoComponent']['subject'])) {
                    $productData['name'] = $data['productInfoComponent']['subject'];
                }
                
                // Extract price - try multiple paths
                $priceFound = false;
                
                // Try new price structure paths
                $pricePaths = [
                    ['priceModule', 'formatedActivityPrice'],
                    ['priceModule', 'formatedPrice'],
                    ['priceComponent', 'origPrice', 'minAmount'],
                    ['priceComponent', 'discountPrice', 'minAmount'],
                    ['data', 'priceModule', 'formatedPrice'],
                    ['data', 'priceModule', 'minActivityAmount'],
                    ['data', 'priceModule', 'minAmount'],
                    ['data', 'priceModule', 'maxAmount'],
                    ['skuModule', 'skuPriceList', 0, 'skuVal', 'skuAmount', 'value'],
                    ['price', 'amount', 'value'],
                    ['price', 'minPrice'],
                    ['price', 'maxPrice']
                ];
                
                foreach ($pricePaths as $path) {
                    $price = $data;
                    foreach ($path as $key) {
                        if (isset($price[$key])) {
                            $price = $price[$key];
                        } else {
                            $price = null;
                            break;
                        }
                    }
                    
                    if ($price !== null) {
                        if (is_string($price)) {
                            $price = $this->extractPrice($price);
                        } elseif (is_array($price) && isset($price['value'])) {
                            $price = floatval($price['value']);
                        } else {
                            $price = floatval($price);
                        }
                        
                        if ($price > 0) {
                            $productData['original_price'] = $price;
                            $priceFound = true;
                            Log::info('Found price: ' . $price . ' in path: ' . implode('.', $path));
                            break;
                        }
                    }
                }
                
                // If still no price, try to find it in the raw HTML
                if (!$priceFound) {
                    // Try to find price in script tags
                    if (preg_match('/"formatedPrice":"([^"]+)"/', $html, $matches)) {
                        $productData['original_price'] = $this->extractPrice($matches[1]);
                        $priceFound = true;
                    } elseif (preg_match('/"minPrice":([0-9.]+)/', $html, $matches)) {
                        $productData['original_price'] = floatval($matches[1]);
                        $priceFound = true;
                    }
                }
                
                // Extract images
                if (!empty($data['imageModule']['imagePathList'])) {
                    $productData['images'] = $data['imageModule']['imagePathList'];
                    $productData['image'] = $productData['images'][0] ?? '';
                } elseif (!empty($data['mediaComponent']['imageUrls'])) {
                    $productData['images'] = $data['mediaComponent']['imageUrls'];
                    $productData['image'] = $productData['images'][0] ?? '';
                }
                
                // Extract description
                if (!empty($data['descriptionModule']['description'])) {
                    $productData['description'] = $data['descriptionModule']['description'];
                } elseif (!empty($data['productInfoComponent']['description'])) {
                    $productData['description'] = $data['productInfoComponent']['description'];
                }
            }
            
            // If JSON extraction failed, try meta tags
            if (empty($productData['name'])) {
                $productData['name'] = $crawler->filter('meta[property="og:title"]')->attr('content') ?? '';
            }
            
            if (empty($productData['description'])) {
                $productData['description'] = $crawler->filter('meta[property="og:description"]')->attr('content') ?? '';
            }
            
            if (empty($productData['image'])) {
                $productData['image'] = $crawler->filter('meta[property="og:image"]')->attr('content') ?? '';
            }
            
            // Try to find price in the DOM if still not found
            if ($productData['original_price'] == 0) {
                $priceSelectors = [
                    '.product-price-value',
                    '.uniform-banner-box-price',
                    '[class*="uniformBannerBoxPrice"]',
                    '[class*="priceValue"]',
                    '.product-price',
                    '.price-current'
                ];
                
                foreach ($priceSelectors as $selector) {
                    try {
                        $priceElement = $crawler->filter($selector);
                        if ($priceElement->count() > 0) {
                            $priceText = $priceElement->text();
                            $price = $this->extractPrice($priceText);
                            if ($price > 0) {
                                $productData['original_price'] = $price;
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            // Clean up the data
            $productData['name'] = html_entity_decode(trim($productData['name']));
            $productData['description'] = html_entity_decode(trim($productData['description'] ?? ''));
            
            // Ensure image URLs are absolute
            if (!empty($productData['image']) && !str_starts_with($productData['image'], 'http')) {
                $productData['image'] = 'https:' . $productData['image'];
            }
            
            foreach ($productData['images'] as &$img) {
                if (!empty($img) && !str_starts_with($img, 'http')) {
                    $img = 'https:' . $img;
                }
            }
            
            // Debug: Log the final product data
            Log::info('Parsed product data: ' . json_encode($productData));
            
            // Set store
            $productData['store'] = 'AliExpress';
            
        } catch (\Exception $e) {
            Log::error('Error parsing AliExpress product: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw new \Exception('Failed to parse AliExpress product details: ' . $e->getMessage());
        }
        
        return $productData;
    }
    
    /**
     * Parse Alibaba product
     *
     * @param Crawler $crawler
     * @param array $productData
     * @return array
     */
    private function parseAlibabaProduct($crawler, $productData)
    {
        try {
            $html = $crawler->html();
            
            // Try to extract JSON data from various script tags
            $patterns = [
                '/window\.__INIT_DATA__\s*=\s*({.+?});/s',
                '/window\.__DATA__\s*=\s*({.+?});/s',
                '/window\.__GLOBAL_DATA__\s*=\s*({.+?});/s',
                '/"data":\s*({.+?})\s*,[\s\n]*"csrfToken"/s',
                '/window\.__page_data__\s*=\s*({.+?});/s'
            ];
            
            $jsonData = null;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    try {
                        $jsonData = json_decode($matches[1], true);
                        if ($jsonData) {
                            Log::info('Successfully matched Alibaba pattern: ' . $pattern);
                            break;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            if ($jsonData) {
                // Extract data from JSON
                $data = $jsonData['data'] ?? $jsonData;
                
                // Extract product name
                if (!empty($data['productInfo']['subject'])) {
                    $productData['name'] = $data['productInfo']['subject'];
                } elseif (!empty($data['metaInfo']['title'])) {
                    $productData['name'] = $data['metaInfo']['title'];
                } elseif (!empty($data['title'])) {
                    $productData['name'] = $data['title'];
                }
                
                // Extract price
                if (!empty($data['priceInfo'])) {
                    $priceInfo = $data['priceInfo'];
                    if (!empty($priceInfo['price'])) {
                        $productData['original_price'] = floatval($priceInfo['price']);
                    } elseif (!empty($priceInfo['minPrice'])) {
                        $productData['original_price'] = floatval($priceInfo['minPrice']);
                    }
                }
                
                // Extract images
                if (!empty($data['imageList'])) {
                    $productData['images'] = array_map(function($img) {
                        return !empty($img['fullPathImageURI']) ? $img['fullPathImageURI'] : $img['imageURI'];
                    }, $data['imageList']);
                    $productData['image'] = $productData['images'][0] ?? '';
                } elseif (!empty($data['productInfo']['imageList'])) {
                    $productData['images'] = $data['productInfo']['imageList'];
                    $productData['image'] = $productData['images'][0] ?? '';
                }
                
                // Extract description
                if (!empty($data['productInfo']['description'])) {
                    $productData['description'] = $data['productInfo']['description'];
                } elseif (!empty($data['description'])) {
                    $productData['description'] = $data['description'];
                }
            }
            
            // Fallback to DOM selectors if JSON extraction fails
            if (empty($productData['name'])) {
                $nameSelectors = [
                    '.product-title',
                    '.product-name',
                    'h1.title',
                    '.mod-detail-title h1',
                    'meta[property="og:title"]'
                ];
                
                foreach ($nameSelectors as $selector) {
                    try {
                        $element = $crawler->filter($selector);
                        if ($element->count() > 0) {
                            $productData['name'] = $selector === 'meta[property="og:title"]' 
                                ? $element->attr('content')
                                : $element->text();
                            if (!empty($productData['name'])) break;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            // Fallback for price
            if ($productData['original_price'] == 0) {
                $priceSelectors = [
                    '.price',
                    '.product-price',
                    '.ma-reference-price',
                    '.price-range-container',
                    'meta[property="product:price:amount"]'
                ];
                
                foreach ($priceSelectors as $selector) {
                    try {
                        $element = $crawler->filter($selector);
                        if ($element->count() > 0) {
                            $priceText = $selector === 'meta[property="product:price:amount"]'
                                ? $element->attr('content')
                                : $element->text();
                            $price = $this->extractPrice($priceText);
                            if ($price > 0) {
                                $productData['original_price'] = $price;
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            // Fallback for image
            if (empty($productData['image'])) {
                $imageSelectors = [
                    '.main-image img',
                    '.detail-image img',
                    '.product-image img',
                    'meta[property="og:image"]'
                ];
                
                foreach ($imageSelectors as $selector) {
                    try {
                        $element = $crawler->filter($selector);
                        if ($element->count() > 0) {
                            $productData['image'] = $selector === 'meta[property="og:image"]'
                                ? $element->attr('content')
                                : $element->attr('src');
                            if (!empty($productData['image'])) break;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            // Fallback for description
            if (empty($productData['description'])) {
                $descriptionSelectors = [
                    '.product-description',
                    '.description',
                    '.detail-desc',
                    'meta[property="og:description"]'
                ];
                
                foreach ($descriptionSelectors as $selector) {
                    try {
                        $element = $crawler->filter($selector);
                        if ($element->count() > 0) {
                            $productData['description'] = $selector === 'meta[property="og:description"]'
                                ? $element->attr('content')
                                : $element->text();
                            if (!empty($productData['description'])) break;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            // Clean up the data
            $productData['name'] = html_entity_decode(trim($productData['name'] ?? 'Unknown Product'));
            $productData['description'] = html_entity_decode(trim($productData['description'] ?? ''));
            
            // Ensure image URLs are absolute
            if (!empty($productData['image']) && !str_starts_with($productData['image'], 'http')) {
                $productData['image'] = 'https:' . $productData['image'];
            }
            
            if (!empty($productData['images'])) {
                foreach ($productData['images'] as &$img) {
                    if (!empty($img) && !str_starts_with($img, 'http')) {
                        $img = 'https:' . $img;
                    }
                }
            }
            
            // Set store
            $productData['store'] = 'Alibaba';
            
            // Log the extracted data for debugging
            Log::info('Parsed Alibaba product data: ' . json_encode($productData));
            
        } catch (\Exception $e) {
            Log::error('Error parsing Alibaba product: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw new \Exception('Failed to parse Alibaba product details: ' . $e->getMessage());
        }
        
        return $productData;
    }
    
    /**
     * Parse Zara product
     *
     * @param Crawler $crawler
     * @param array $productData
     * @return array
     */
    private function parseZaraProduct($crawler, $productData)
    {
        // Extract product name
        $productData['name'] = $crawler->filter('.product-detail-info__header-name')->text() ?? 'Unknown Product';
        
        // Extract price
        $priceText = $crawler->filter('.money-amount__main')->text() ?? '';
        $productData['original_price'] = $this->extractPrice($priceText);
        
        // Extract image
        $productData['image'] = $crawler->filter('.product-detail-images__image')->attr('src') ?? '';
        
        // Extract description
        $productData['description'] = $crawler->filter('.product-detail-description')->text() ?? '';
        
        return $productData;
    }
    
    /**
     * Parse Etsy product
     *
     * @param Crawler $crawler
     * @param array $productData
     * @return array
     */
    private function parseEtsyProduct($crawler, $productData)
    {
        try {
            $html = $crawler->html();
            Log::info('Starting Etsy product parsing');
            
            // Try to extract JSON-LD data first
            $jsonLdData = null;
            if (preg_match_all('/<script type="application\/ld\+json">(.+?)<\/script>/s', $html, $matches)) {
                foreach ($matches[1] as $jsonStr) {
                    try {
                        $data = json_decode($jsonStr, true);
                        if ($data && isset($data['@type']) && $data['@type'] === 'Product') {
                            $jsonLdData = $data;
                            Log::info('Found Product JSON-LD data');
                            break;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            // Try to extract initial state data
            $initialState = null;
            $statePatterns = [
                '/window\.__INITIAL_STATE__\s*=\s*({.+?});/s',
                '/window\.__BOOTSTRAP_STATE__\s*=\s*({.+?});/s',
                '/window\.__INITIAL_DATA__\s*=\s*({.+?});/s',
                '/window\.__context\s*=\s*({.+?});/s'
            ];
            
            foreach ($statePatterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    try {
                        $initialState = json_decode($matches[1], true);
                        if ($initialState) {
                            Log::info('Found initial state data');
                            break;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            // Extract product name with multiple approaches
            if ($jsonLdData && !empty($jsonLdData['name'])) {
                $productData['name'] = $jsonLdData['name'];
            } else {
                $nameSelectors = [
                    'h1[data-buy-box-region="title"]',
                    'h1.wt-text-body-01',
                    '.wt-mb-xs-2 h1',
                    'meta[property="og:title"]',
                    'meta[name="title"]',
                    '[data-component="listing-page-title-component"] h1'
                ];
                
                foreach ($nameSelectors as $selector) {
                    try {
                        $element = $crawler->filter($selector);
                        if ($element->count() > 0) {
                            $productData['name'] = $selector === 'meta[property="og:title"]' || $selector === 'meta[name="title"]'
                                ? $element->attr('content')
                                : $element->text();
                            if (!empty($productData['name'])) {
                                Log::info('Found product name using selector: ' . $selector);
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            // Extract price with multiple approaches
            if ($jsonLdData && !empty($jsonLdData['offers']['price'])) {
                $productData['original_price'] = floatval($jsonLdData['offers']['price']);
                Log::info('Extracted price from JSON-LD');
            } else {
                $priceSelectors = [
                    '[data-buy-box-region="price"] .wt-text-title-01',
                    '.wt-text-title-01 span[data-currency-value]',
                    'meta[property="product:price:amount"]',
                    '.wt-text-title-01',
                    '[data-buy-box-region="price"] p.wt-text-title-03',
                    '[data-buy-box-region="price"] .wt-text-title-03 span'
                ];
                
                foreach ($priceSelectors as $selector) {
                    try {
                        $element = $crawler->filter($selector);
                        if ($element->count() > 0) {
                            $priceText = $selector === 'meta[property="product:price:amount"]'
                                ? $element->attr('content')
                                : $element->text();
                            $price = $this->extractPrice($priceText);
                            if ($price > 0) {
                                $productData['original_price'] = $price;
                                Log::info('Found price using selector: ' . $selector);
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            // Extract images with multiple approaches
            if ($jsonLdData && !empty($jsonLdData['image'])) {
                $productData['images'] = is_array($jsonLdData['image']) 
                    ? $jsonLdData['image'] 
                    : [$jsonLdData['image']];
                $productData['image'] = $productData['images'][0];
                Log::info('Extracted images from JSON-LD');
            } else {
                $imageSelectors = [
                    '.wt-max-width-full img.wt-max-width-full',
                    '.carousel-image',
                    'meta[property="og:image"]',
                    '.wt-max-width-full img[data-index="0"]',
                    '[data-carousel-first-image]',
                    '[data-image-zoom-url]',
                    'img.wt-max-width-full'
                ];
                
                foreach ($imageSelectors as $selector) {
                    try {
                        $elements = $crawler->filter($selector);
                        if ($elements->count() > 0) {
                            if ($selector === 'meta[property="og:image"]') {
                                $productData['image'] = $elements->attr('content');
                                $productData['images'] = [$productData['image']];
                            } else {
                                $productData['images'] = [];
                                $elements->each(function ($node) use (&$productData) {
                                    $imgSrc = $node->attr('src') ?: $node->attr('data-src') ?: $node->attr('data-image-zoom-url');
                                    if ($imgSrc && !in_array($imgSrc, $productData['images'])) {
                                        $productData['images'][] = $imgSrc;
                                    }
                                });
                                $productData['image'] = $productData['images'][0] ?? '';
                            }
                            if (!empty($productData['image'])) {
                                Log::info('Found images using selector: ' . $selector);
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            // Extract description with multiple approaches
            if ($jsonLdData && !empty($jsonLdData['description'])) {
                $productData['description'] = $jsonLdData['description'];
                Log::info('Extracted description from JSON-LD');
            } else {
                $descriptionSelectors = [
                    '[data-product-details-description-text-content]',
                    '.wt-content-toggle__body',
                    'meta[property="og:description"]',
                    '#product-details-content',
                    '[data-product-details-description-text]',
                    '.wt-text-body-01.wt-overflow-hidden'
                ];
                
                foreach ($descriptionSelectors as $selector) {
                    try {
                        $element = $crawler->filter($selector);
                        if ($element->count() > 0) {
                            $productData['description'] = $selector === 'meta[property="og:description"]'
                                ? $element->attr('content')
                                : $element->text();
                            if (!empty($productData['description'])) {
                                Log::info('Found description using selector: ' . $selector);
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
            // Clean up the data
            $productData['name'] = html_entity_decode(trim($productData['name'] ?? 'Unknown Product'));
            $productData['description'] = html_entity_decode(trim($productData['description'] ?? ''));
            
            // Ensure image URLs are absolute
            if (!empty($productData['image']) && !str_starts_with($productData['image'], 'http')) {
                $productData['image'] = 'https:' . $productData['image'];
            }
            
            if (!empty($productData['images'])) {
                foreach ($productData['images'] as &$img) {
                    if (!empty($img) && !str_starts_with($img, 'http')) {
                        $img = 'https:' . $img;
                    }
                }
            }
            
            // Set store
            $productData['store'] = 'Etsy';
            
            // Validate extracted data
            if (empty($productData['name']) || $productData['name'] === 'Unknown Product') {
                Log::warning('Failed to extract product name from Etsy page');
            }
            if ($productData['original_price'] === 0) {
                Log::warning('Failed to extract price from Etsy page');
            }
            if (empty($productData['image'])) {
                Log::warning('Failed to extract image from Etsy page');
            }
            if (empty($productData['description'])) {
                Log::warning('Failed to extract description from Etsy page');
            }
            
            // Log the extracted data for debugging
            Log::info('Parsed Etsy product data: ' . json_encode($productData));
            
        } catch (\Exception $e) {
            Log::error('Error parsing Etsy product: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw new \Exception('Failed to parse Etsy product details: ' . $e->getMessage());
        }
        
        return $productData;
    }
    
    /**
     * Parse generic product (for unknown platforms)
     *
     * @param Crawler $crawler
     * @param array $productData
     * @return array
     */
    private function parseGenericProduct($crawler, $productData)
    {
        $productData['name'] = $crawler->filter('meta[property="og:title"]')->attr('content') ?? 
                              $crawler->filter('h1')->text() ?? 
                              'Unknown Product';
        
        $priceText = $crawler->filter('meta[property="product:price:amount"]')->attr('content') ?? 
                    $crawler->filter('[class*="price"]')->text() ?? 
                    '';
        $productData['original_price'] = $this->extractPrice($priceText);
        
        $productData['image'] = $crawler->filter('meta[property="og:image"]')->attr('content') ?? 
                               $crawler->filter('img[class*="product"]')->attr('src') ?? 
                               '';
        
        $productData['description'] = $crawler->filter('meta[property="og:description"]')->attr('content') ?? 
                                    $crawler->filter('[class*="description"]')->text() ?? 
                                    '';
        
        $productData['brand'] = $crawler->filter('meta[property="product:brand"]')->attr('content') ?? null;
        
        $productData['category'] = $crawler->filter('.breadcrumb')->text() ?? null;
        
        $ratingText = $crawler->filter('[itemprop="ratingValue"]')->text() ?? '';
        $productData['rating'] = $ratingText ? floatval($ratingText) : null;
        
        $reviewCountText = $crawler->filter('[itemprop="reviewCount"]')->text() ?? '';
        $productData['review_count'] = $reviewCountText ? (int) $reviewCountText : 0;
        
        $stockText = $crawler->filter('[class*="stock"], [class*="availability"]')->text() ?? '';
        $productData['in_stock'] = str_contains(strtolower($stockText), 'in stock') || empty($stockText);
        
        $productData['sku'] = $crawler->filter('[itemprop="sku"]')->text() ?? null;
        
        return $productData;
    }
    
    /**
     * Extract price from text
     *
     * @param string $text
     * @return float
     */
    private function extractPrice($text)
    {
        if (empty($text)) {
            return 0;
        }
        
        // Remove currency symbols and other non-numeric characters except decimal point
        $price = preg_replace('/[^0-9.]/', '', $text);
        
        // Convert to float
        return (float) $price;
    }
    
    /**
     * Generate similar products based on the current product
     *
     * @param array $productData
     * @return array
     */
    private function generateSimilarProducts($productData)
    {
        // This is a placeholder. In a real application, you would:
        // 1. Search for products with similar names/categories
        // 2. Use a recommendation engine
        // 3. Query a product database
        return [];
    }
} 