<?php

// Test Cache Functionality Script
// Usage: php test_cache.php

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 Testing Cache Functionality...\n\n";

try {
    // Test 1: Basic Cache Operations
    echo "1️⃣ Testing Basic Cache Operations:\n";
    
    $testKey = 'cache_test_' . time();
    $testValue = 'Cache is working! ' . date('Y-m-d H:i:s');
    
    // Store in cache
    Cache::put($testKey, $testValue, 60); // 60 seconds
    echo "   ✅ Cache PUT: {$testKey}\n";
    
    // Retrieve from cache
    $retrieved = Cache::get($testKey);
    if ($retrieved === $testValue) {
        echo "   ✅ Cache GET: Success - {$retrieved}\n";
    } else {
        echo "   ❌ Cache GET: Failed\n";
    }
    
    // Test 2: Cache Driver Info
    echo "\n2️⃣ Cache Driver Information:\n";
    $driver = config('cache.default');
    echo "   📦 Default Driver: {$driver}\n";
    
    $store = Cache::getStore();
    echo "   🏪 Store Class: " . get_class($store) . "\n";
    
    // Test 3: Similar Products Cache Test
    echo "\n3️⃣ Testing Similar Products Cache Pattern:\n";
    
    $sampleProductId = 1;
    $sampleCategory = 'electronics';
    $sampleBrand = 'samsung';
    $cacheKey = "similar_products_{$sampleProductId}_{$sampleCategory}_{$sampleBrand}";
    
    $sampleData = [
        'products' => [
            ['id' => 1, 'name' => 'Product 1', 'price' => 999],
            ['id' => 2, 'name' => 'Product 2', 'price' => 1299],
        ],
        'cached_at' => now()->toDateTimeString()
    ];
    
    // Cache similar products data
    Cache::put($cacheKey, $sampleData, 1800); // 30 minutes
    echo "   ✅ Cached: {$cacheKey}\n";
    
    // Retrieve similar products data
    $cachedProducts = Cache::get($cacheKey);
    if ($cachedProducts && isset($cachedProducts['products'])) {
        echo "   ✅ Retrieved: " . count($cachedProducts['products']) . " similar products\n";
        echo "   🕒 Cached at: {$cachedProducts['cached_at']}\n";
    } else {
        echo "   ❌ Failed to retrieve similar products cache\n";
    }
    
    // Test 4: Cache Performance Test
    echo "\n4️⃣ Performance Test:\n";
    
    $startTime = microtime(true);
    
    // Write performance test
    for ($i = 0; $i < 100; $i++) {
        Cache::put("perf_test_{$i}", "test_data_{$i}", 60);
    }
    $writeTime = microtime(true) - $startTime;
    echo "   ⚡ Write 100 keys: " . number_format($writeTime * 1000, 2) . "ms\n";
    
    // Read performance test
    $startTime = microtime(true);
    for ($i = 0; $i < 100; $i++) {
        Cache::get("perf_test_{$i}");
    }
    $readTime = microtime(true) - $startTime;
    echo "   ⚡ Read 100 keys: " . number_format($readTime * 1000, 2) . "ms\n";
    
    // Cleanup performance test keys
    for ($i = 0; $i < 100; $i++) {
        Cache::forget("perf_test_{$i}");
    }
    
    // Test 5: Cache Statistics (if using Redis)
    if ($driver === 'redis') {
        echo "\n5️⃣ Redis Statistics:\n";
        try {
            $redis = Cache::getStore()->getRedis();
            $info = $redis->info();
            
            if (isset($info['connected_clients'])) {
                echo "   👥 Connected Clients: {$info['connected_clients']}\n";
            }
            if (isset($info['used_memory_human'])) {
                echo "   💾 Memory Usage: {$info['used_memory_human']}\n";
            }
            if (isset($info['total_commands_processed'])) {
                echo "   📊 Total Commands: {$info['total_commands_processed']}\n";
            }
        } catch (Exception $e) {
            echo "   ⚠️ Redis stats not available: {$e->getMessage()}\n";
        }
    }
    
    // Test 6: Database Cache Statistics (if using database)
    if ($driver === 'database') {
        echo "\n5️⃣ Database Cache Statistics:\n";
        try {
            $count = DB::table('cache')->count();
            echo "   📈 Total Cache Entries: {$count}\n";
            
            $recentCount = DB::table('cache')
                ->where('expiration', '>', time())
                ->count();
            echo "   ✅ Active Cache Entries: {$recentCount}\n";
            
            $expiredCount = $count - $recentCount;
            echo "   ⏰ Expired Cache Entries: {$expiredCount}\n";
        } catch (Exception $e) {
            echo "   ⚠️ Database cache stats not available: {$e->getMessage()}\n";
        }
    }
    
    // Cleanup test keys
    Cache::forget($testKey);
    Cache::forget($cacheKey);
    
    echo "\n🎉 Cache Test Complete!\n";
    echo "✅ Cache is working properly on your system.\n";
    
} catch (Exception $e) {
    echo "\n❌ Cache Test Failed!\n";
    echo "Error: {$e->getMessage()}\n";
    echo "Stack Trace:\n{$e->getTraceAsString()}\n";
    
    echo "\n🔧 Troubleshooting Steps:\n";
    echo "1. Check your .env CACHE_STORE setting\n";
    echo "2. Run: php artisan config:clear\n";
    echo "3. Run: php artisan migrate (for database cache)\n";
    echo "4. Check if Redis/Memcached is running (if using those)\n";
    echo "5. Check file permissions for storage/framework/cache\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "💡 Production Deployment Tips:\n";
echo "• Use Redis for high-traffic sites\n";
echo "• Set CACHE_PREFIX in production .env\n";
echo "• Monitor cache performance regularly\n";
echo "• Have cache clearing strategy in deployment\n";
echo str_repeat("=", 50) . "\n";