# 🚀 ImageOptimizer Production Deployment Guide

## 📋 **Complete Production Setup**

### 1. **Queue Workers (NOT Cron Jobs)**

**❌ Don't use cron jobs** - Use Laravel Queue Workers instead:

```bash
# Start queue workers (run as system service)
php artisan queue:work --queue=images --timeout=300 --memory=512 --tries=3 --sleep=3

# For high traffic, run multiple workers:
php artisan queue:work --queue=images --timeout=300 --memory=512 --tries=3 --sleep=3 &
php artisan queue:work --queue=images --timeout=300 --memory=512 --tries=3 --sleep=3 &
php artisan queue:work --queue=images --timeout=300 --memory=512 --tries=3 --sleep=3 &
```

### 2. **System Service Setup (Ubuntu/CentOS)**

Create `/etc/systemd/system/laravel-queue-images.service`:

```ini
[Unit]
Description=Laravel Image Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=5
ExecStart=/usr/bin/php /var/www/your-app/artisan queue:work --queue=images --timeout=300 --memory=512 --tries=3 --sleep=3
WorkingDirectory=/var/www/your-app
Environment=PHP_CLI_SERVER_WORKERS=4

[Install]
WantedBy=multi-user.target
```

**Enable and start:**
```bash
sudo systemctl enable laravel-queue-images
sudo systemctl start laravel-queue-images
sudo systemctl status laravel-queue-images
```

### 3. **Environment Configuration (.env)**

```env
# Queue Configuration
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0

# Image Optimization Settings
IMAGE_QUEUE_ENABLED=true
IMAGE_QUEUE_NAME=images
IMAGE_CACHE_ENABLED=true
IMAGE_CACHE_DRIVER=redis

# CDN Configuration (Optional)
CDN_ENABLED=true
CDN_URL=https://cdn.yourdomain.com
CDN_PATH_PREFIX=images

# Binary Paths (Install these first)
JPEGOPTIM_PATH=/usr/bin/jpegoptim
OPTIPNG_PATH=/usr/bin/optipng
PNGQUANT_PATH=/usr/bin/pngquant
GIFSICLE_PATH=/usr/bin/gifsicle
```

### 4. **Install Image Optimization Binaries**

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install -y jpegoptim optipng pngquant gifsicle webp
```

**CentOS/RHEL:**
```bash
sudo yum install -y epel-release
sudo yum install -y jpegoptim optipng pngquant gifsicle libwebp-tools
```

**Verify installation:**
```bash
which jpegoptim  # Should return: /usr/bin/jpegoptim
which optipng    # Should return: /usr/bin/optipng
which pngquant   # Should return: /usr/bin/pngquant
which gifsicle   # Should return: /usr/bin/gifsicle
```

### 5. **Redis Configuration**

**Install Redis:**
```bash
# Ubuntu/Debian
sudo apt-get install redis-server

# CentOS/RHEL  
sudo yum install redis
```

**Configure Redis (`/etc/redis/redis.conf`):**
```
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

**Start Redis:**
```bash
sudo systemctl enable redis
sudo systemctl start redis
```

### 6. **Supervisor Configuration (Alternative to systemd)**

Install Supervisor:
```bash
sudo apt-get install supervisor  # Ubuntu
sudo yum install supervisor      # CentOS
```

Create `/etc/supervisor/conf.d/laravel-image-queue.conf`:
```ini
[program:laravel-image-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/your-app/artisan queue:work --queue=images --timeout=300 --memory=512 --tries=3
directory=/var/www/your-app
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/your-app/storage/logs/queue-worker.log
```

**Start Supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-image-queue:*
```

### 7. **Monitoring & Health Checks**

**Queue Monitoring:**
```bash
# Check queue status
php artisan queue:monitor images --max=100

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

**Create health check endpoint:**
```php
// routes/web.php
Route::get('/health/images', function() {
    return response()->json([
        'status' => 'ok',
        'optimizer_status' => \App\Helpers\ImageOptimizer::getStatus(),
        'queue_size' => \Illuminate\Support\Facades\Queue::size('images'),
        'timestamp' => now()
    ]);
});
```

### 8. **Performance Tuning**

**PHP Configuration (`php.ini`):**
```ini
memory_limit = 512M
upload_max_filesize = 5M
post_max_size = 6M
max_execution_time = 300
max_input_time = 300
```

**Laravel Configuration:**
```bash
# Optimize Laravel for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 9. **The ONLY Cron Job You Need**

```cron
# Add to crontab: crontab -e
# Laravel's built-in scheduler (NOT for image processing)
* * * * * cd /var/www/your-app && php artisan schedule:run >> /dev/null 2>&1
```

This handles Laravel's internal scheduling, NOT image processing!

### 10. **Deployment Checklist**

**Pre-deployment:**
- [ ] Install Redis
- [ ] Install image optimization binaries  
- [ ] Configure environment variables
- [ ] Set up queue workers (systemd/supervisor)
- [ ] Set proper file permissions

**Post-deployment testing:**
```bash
# Test image upload
curl -X POST -F "image=@test.jpg" https://yourdomain.com/api/upload

# Check queue worker status  
sudo systemctl status laravel-queue-images

# Test optimization status
curl https://yourdomain.com/health/images

# Monitor queue
php artisan queue:monitor images
```

**File Permissions:**
```bash
sudo chown -R www-data:www-data storage/app/public/
sudo chmod -R 755 storage/app/public/
```

## 🎯 **Summary**

**✅ DO:**
- Use Laravel Queue Workers (not cron for images)
- Run workers as system services  
- Monitor queue health
- Use Redis for caching
- Only use cron for Laravel scheduler

**❌ DON'T:**
- Use cron jobs for image processing
- Run without queue workers
- Forget to monitor failed jobs
- Skip Redis setup
- Ignore file permissions

**🚀 Your production setup is complete!**