<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheManagementController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Only allow authenticated admin users
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Show cache management dashboard
     */
    public function index()
    {
        $cacheStats = $this->getCacheStatistics();
        return view('admin.cache.index', compact('cacheStats'));
    }

    /**
     * Clear specific cache type
     */
    public function clearCache(Request $request)
    {
        $request->validate([
            'cache_type' => 'required|in:application,config,route,view,all'
        ]);

        $cacheType = $request->cache_type;
        $user = Auth::user();
        $results = [];

        try {
            switch ($cacheType) {
                case 'application':
                    $exitCode = Artisan::call('cache:clear');
                    $results['application'] = $exitCode === 0 ? 'success' : 'failed';
                    break;

                case 'config':
                    $exitCode = Artisan::call('config:clear');
                    $results['config_clear'] = $exitCode === 0 ? 'success' : 'failed';
                    $exitCode = Artisan::call('config:cache');
                    $results['config_cache'] = $exitCode === 0 ? 'success' : 'failed';
                    break;

                case 'route':
                    $exitCode = Artisan::call('route:clear');
                    $results['route_clear'] = $exitCode === 0 ? 'success' : 'failed';
                    $exitCode = Artisan::call('route:cache');
                    $results['route_cache'] = $exitCode === 0 ? 'success' : 'failed';
                    break;

                case 'view':
                    $exitCode = Artisan::call('view:clear');
                    $results['view_clear'] = $exitCode === 0 ? 'success' : 'failed';
                    $exitCode = Artisan::call('view:cache');
                    $results['view_cache'] = $exitCode === 0 ? 'success' : 'failed';
                    break;

                case 'all':
                    $commands = [
                        'cache:clear',
                        'config:clear',
                        'config:cache',
                        'route:clear',
                        'route:cache',
                        'view:clear',
                        'view:cache'
                    ];

                    foreach ($commands as $command) {
                        $exitCode = Artisan::call($command);
                        $results[$command] = $exitCode === 0 ? 'success' : 'failed';
                    }
                    break;
            }

            // Log the cache clear action
            Log::channel('single')->info('Cache cleared by admin', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'cache_type' => $cacheType,
                'results' => $results,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toDateTimeString()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Cache cleared successfully: {$cacheType}",
                'results' => $results,
                'timestamp' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Cache clear failed', [
                'user_id' => $user->id,
                'cache_type' => $cacheType,
                'error' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ], 500);
        }
    }

    /**
     * Clear specific application caches
     */
    public function clearSpecificCache(Request $request)
    {
        $request->validate([
            'cache_keys' => 'required|array',
            'cache_keys.*' => 'string'
        ]);

        $user = Auth::user();
        $cleared = [];
        $failed = [];

        foreach ($request->cache_keys as $key) {
            try {
                if (Cache::forget($key)) {
                    $cleared[] = $key;
                } else {
                    $failed[] = $key;
                }
            } catch (\Exception $e) {
                $failed[] = $key . ' (Error: ' . $e->getMessage() . ')';
            }
        }

        // Log the specific cache clear action
        Log::info('Specific cache keys cleared by admin', [
            'user_id' => $user->id,
            'cleared_keys' => $cleared,
            'failed_keys' => $failed,
            'timestamp' => now()->toDateTimeString()
        ]);

        return response()->json([
            'success' => true,
            'cleared' => $cleared,
            'failed' => $failed,
            'message' => count($cleared) . ' cache keys cleared, ' . count($failed) . ' failed'
        ]);
    }

    /**
     * Get cache statistics
     */
    private function getCacheStatistics()
    {
        $stats = [
            'driver' => config('cache.default'),
            'total_entries' => 0,
            'active_entries' => 0,
            'expired_entries' => 0,
            'size_estimate' => 'N/A',
            'hit_ratio' => 'N/A'
        ];

        try {
            // Database cache statistics
            if (config('cache.default') === 'database') {
                $total = DB::table('cache')->count();
                $active = DB::table('cache')->where('expiration', '>', time())->count();
                
                $stats['total_entries'] = $total;
                $stats['active_entries'] = $active;
                $stats['expired_entries'] = $total - $active;
                
                // Estimate size
                $sizeQuery = DB::table('cache')
                    ->selectRaw('SUM(LENGTH(value)) as total_size')
                    ->first();
                
                if ($sizeQuery && $sizeQuery->total_size) {
                    $stats['size_estimate'] = $this->formatBytes($sizeQuery->total_size);
                }
            }

            // Redis cache statistics
            if (config('cache.default') === 'redis') {
                try {
                    $redis = Cache::getStore()->getRedis();
                    $info = $redis->info();
                    
                    if (isset($info['db0'])) {
                        preg_match('/keys=(\d+)/', $info['db0'], $matches);
                        $stats['total_entries'] = isset($matches[1]) ? (int)$matches[1] : 0;
                        $stats['active_entries'] = $stats['total_entries']; // Redis auto-expires
                    }
                    
                    if (isset($info['used_memory'])) {
                        $stats['size_estimate'] = $this->formatBytes($info['used_memory']);
                    }
                } catch (\Exception $e) {
                    // Redis stats not available
                }
            }

        } catch (\Exception $e) {
            Log::warning('Could not retrieve cache statistics', [
                'error' => $e->getMessage()
            ]);
        }

        return $stats;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get cache management logs
     */
    public function getLogs(Request $request)
    {
        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:10|max:100'
        ]);

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        // This is a simple implementation - in production you might want to use a dedicated logging system
        try {
            $logFile = storage_path('logs/laravel.log');
            if (!file_exists($logFile)) {
                return response()->json([
                    'success' => true,
                    'logs' => [],
                    'message' => 'No log file found'
                ]);
            }

            $logs = [];
            $handle = fopen($logFile, 'r');
            
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (strpos($line, 'Cache cleared by admin') !== false || 
                        strpos($line, 'Specific cache keys cleared') !== false) {
                        $logs[] = $line;
                    }
                }
                fclose($handle);
            }

            // Reverse to show newest first
            $logs = array_reverse($logs);
            
            // Simple pagination
            $total = count($logs);
            $offset = ($page - 1) * $perPage;
            $logs = array_slice($logs, $offset, $perPage);

            return response()->json([
                'success' => true,
                'logs' => $logs,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve logs: ' . $e->getMessage()
            ], 500);
        }
    }
}