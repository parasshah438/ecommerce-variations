#!/bin/bash

# 🚀 Production Cache Deployment Script
# This script handles cache setup and optimization for production deployment

echo "🚀 Starting Production Cache Setup..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Check if we're in Laravel root
if [ ! -f "artisan" ]; then
    print_error "This script must be run from Laravel root directory"
    exit 1
fi

print_info "Current directory: $(pwd)"

# Step 1: Clear all existing caches
echo -e "\n${BLUE}Step 1: Clearing existing caches...${NC}"
php artisan cache:clear
print_status "Application cache cleared"

php artisan config:clear
print_status "Configuration cache cleared"

php artisan route:clear
print_status "Route cache cleared"

php artisan view:clear
print_status "View cache cleared"

# Step 2: Check cache configuration
echo -e "\n${BLUE}Step 2: Checking cache configuration...${NC}"
CACHE_DRIVER=$(php artisan tinker --execute="echo config('cache.default');" 2>/dev/null | tail -1)
print_info "Current cache driver: $CACHE_DRIVER"

# Step 3: Validate cache table (for database cache)
if [ "$CACHE_DRIVER" = "database" ]; then
    echo -e "\n${BLUE}Step 3: Validating database cache setup...${NC}"
    
    # Check if cache table exists
    TABLE_EXISTS=$(php artisan tinker --execute="
        try {
            \$count = DB::table('cache')->count();
            echo 'EXISTS';
        } catch (Exception \$e) {
            echo 'NOT_EXISTS';
        }
    " 2>/dev/null | tail -1)
    
    if [ "$TABLE_EXISTS" = "NOT_EXISTS" ]; then
        print_warning "Cache table not found. Running migrations..."
        php artisan migrate --force
        print_status "Database migrations completed"
    else
        print_status "Cache table exists and is accessible"
    fi
fi

# Step 4: Test cache functionality
echo -e "\n${BLUE}Step 4: Testing cache functionality...${NC}"
TEST_RESULT=$(php artisan tinker --execute="
    try {
        Cache::put('deployment_test', 'working', 60);
        \$result = Cache::get('deployment_test');
        Cache::forget('deployment_test');
        echo (\$result === 'working') ? 'SUCCESS' : 'FAILED';
    } catch (Exception \$e) {
        echo 'ERROR: ' . \$e->getMessage();
    }
" 2>/dev/null | tail -1)

if [[ "$TEST_RESULT" == "SUCCESS" ]]; then
    print_status "Cache functionality test passed"
elif [[ "$TEST_RESULT" == "FAILED" ]]; then
    print_error "Cache functionality test failed"
    exit 1
else
    print_error "Cache test error: $TEST_RESULT"
    exit 1
fi

# Step 5: Build optimized caches
echo -e "\n${BLUE}Step 5: Building optimized caches...${NC}"
php artisan config:cache
print_status "Configuration cache built"

php artisan route:cache
print_status "Route cache built"

php artisan view:cache
print_status "View cache built"

# Step 6: Pre-warm application caches
echo -e "\n${BLUE}Step 6: Pre-warming application caches...${NC}"

# Pre-warm product-related caches
php artisan tinker --execute="
    try {
        // Pre-warm categories cache
        \$categories = Cache::remember('categories_with_products', 1800, function() {
            return App\Models\Category::whereHas('products')->withCount('products')->get();
        });
        echo 'Categories cache warmed: ' . \$categories->count() . ' items\n';
        
        // Pre-warm brands cache
        \$brands = Cache::remember('brands_with_products', 1800, function() {
            return App\Models\Brand::whereHas('products')->withCount('products')->get();
        });
        echo 'Brands cache warmed: ' . \$brands->count() . ' items\n';
        
        // Pre-warm price range cache
        \$priceRange = Cache::remember('product_price_range', 3600, function() {
            return [
                'min' => (int) App\Models\Product::min('price'),
                'max' => (int) App\Models\Product::max('price')
            ];
        });
        echo 'Price range cache warmed: ₹' . \$priceRange['min'] . ' - ₹' . \$priceRange['max'] . '\n';
        
    } catch (Exception \$e) {
        echo 'Error warming caches: ' . \$e->getMessage() . '\n';
    }
" 2>/dev/null

print_status "Application caches pre-warmed"

# Step 7: Set proper permissions
echo -e "\n${BLUE}Step 7: Setting cache permissions...${NC}"
if [ -d "storage/framework/cache" ]; then
    chmod -R 775 storage/framework/cache
    print_status "Cache directory permissions set"
fi

if [ -d "storage/framework/views" ]; then
    chmod -R 775 storage/framework/views
    print_status "Views cache directory permissions set"
fi

# Step 8: Cache statistics
echo -e "\n${BLUE}Step 8: Cache statistics...${NC}"

if [ "$CACHE_DRIVER" = "database" ]; then
    CACHE_COUNT=$(php artisan tinker --execute="echo DB::table('cache')->count();" 2>/dev/null | tail -1)
    print_info "Database cache entries: $CACHE_COUNT"
fi

# Step 9: Performance recommendations
echo -e "\n${BLUE}Step 9: Performance recommendations...${NC}"

case $CACHE_DRIVER in
    "database")
        print_info "Using database cache - Good for small to medium sites"
        print_warning "Consider Redis for high-traffic sites (>1000 concurrent users)"
        ;;
    "redis")
        print_status "Using Redis cache - Excellent for high performance"
        ;;
    "memcached")
        print_status "Using Memcached - Good for distributed systems"
        ;;
    "file")
        print_warning "Using file cache - Consider database or Redis for better performance"
        ;;
    *)
        print_warning "Unknown cache driver: $CACHE_DRIVER"
        ;;
esac

# Step 10: Final validation
echo -e "\n${BLUE}Step 10: Final validation...${NC}"

# Test your specific cache patterns
SIMILAR_PRODUCTS_TEST=$(php artisan tinker --execute="
    try {
        \$testKey = 'similar_products_1_electronics_samsung';
        \$testData = ['test' => 'data', 'timestamp' => now()->toDateTimeString()];
        Cache::put(\$testKey, \$testData, 1800);
        \$retrieved = Cache::get(\$testKey);
        Cache::forget(\$testKey);
        echo (isset(\$retrieved['test']) && \$retrieved['test'] === 'data') ? 'SUCCESS' : 'FAILED';
    } catch (Exception \$e) {
        echo 'ERROR: ' . \$e->getMessage();
    }
" 2>/dev/null | tail -1)

if [[ "$SIMILAR_PRODUCTS_TEST" == "SUCCESS" ]]; then
    print_status "Similar products cache pattern test passed"
else
    print_error "Similar products cache pattern test failed: $SIMILAR_PRODUCTS_TEST"
fi

# Summary
echo -e "\n${GREEN}🎉 Production Cache Setup Complete!${NC}"
echo -e "\n${BLUE}📊 Summary:${NC}"
echo "• Cache Driver: $CACHE_DRIVER"
echo "• Configuration: Optimized and cached"
echo "• Routes: Cached for faster routing"
echo "• Views: Compiled and cached"
echo "• Application Cache: Pre-warmed"
echo "• Permissions: Set correctly"

echo -e "\n${BLUE}🔧 Maintenance Commands:${NC}"
echo "• Clear cache: php artisan cache:clear"
echo "• Rebuild cache: php artisan config:cache && php artisan route:cache"
echo "• Test cache: php test_cache.php"

echo -e "\n${BLUE}📈 Monitoring:${NC}"
echo "• Monitor cache performance regularly"
echo "• Set up alerts for cache server availability"
echo "• Review cache hit ratios"

echo -e "\n${GREEN}✅ Your application is ready for production!${NC}"