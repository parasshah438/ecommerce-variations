# Slider Cache Production Deployment

## Correct Deployment Steps:

### 1. Clear Application Cache (not config cache)
```bash
php artisan cache:clear
```

### 2. Clear Route Cache (if routes changed)
```bash
php artisan route:clear
php artisan route:cache
```

### 3. Clear View Cache (if views changed)
```bash
php artisan view:clear
php artisan view:cache
```

### 4. Config Cache (only if config files changed)
```bash
php artisan config:cache
```

## For Slider Cache Specifically:

The slider cache uses `Cache::remember()` which is **application cache**, not config cache.

**Just run:**
```bash
php artisan cache:clear
```

This will:
- ✅ Clear existing 'home_sliders' cache
- ✅ Allow new caching logic to work
- ✅ Reset all application cache

## Alternative: Clear Specific Cache Key

If you want to be surgical:
```bash
# In tinker or via code
Cache::forget('home_sliders');
```

## Production Deployment Script:
```bash
# Update code first
git pull origin main

# Clear caches
php artisan cache:clear
php artisan route:cache
php artisan config:cache
php artisan view:cache

# Restart queue workers if using
php artisan queue:restart
```