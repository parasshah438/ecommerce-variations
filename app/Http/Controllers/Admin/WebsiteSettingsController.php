<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EnvFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class WebsiteSettingsController extends Controller
{
    public function __construct(private EnvFileService $envFile)
    {
        $this->middleware(['auth', 'admin']);
    }

    private const UTILITY_TABS = ['cache', 'backup'];

    public function index(Request $request)
    {
        $tabs = config('website_settings.tabs', []);
        $tab = $request->get('tab', 'general');

        $isUtilityTab = in_array($tab, self::UTILITY_TABS, true);

        if (!array_key_exists($tab, $tabs) && !$isUtilityTab) {
            $tab = 'general';
        }

        $settings = $isUtilityTab ? [] : $this->envFile->getTabValues($tab);
        $envBackups = $this->envFile->listBackups();
        $readonly = $this->envFile->getMany(config('website_settings.readonly_keys', []));
        $cacheStats = $this->getCacheStatistics();

        return view('admin.settings.index', compact('tabs', 'tab', 'settings', 'envBackups', 'readonly', 'cacheStats'));
    }

    public function update(Request $request, string $tab)
    {
        $tabs = config('website_settings.tabs', []);

        if (!array_key_exists($tab, $tabs)) {
            abort(404);
        }

        $tabConfig = $tabs[$tab];
        $rules = [];
        $sensitiveKeys = [];

        foreach ($tabConfig['keys'] as $key => $meta) {
            if (!empty($meta['rules'])) {
                $rules[$key] = $meta['rules'];
            }
            if (!empty($meta['sensitive'])) {
                $sensitiveKeys[] = $key;
            }
        }

        $validated = $request->validate($rules);

        foreach ($tabConfig['keys'] as $key => $meta) {
            if (($meta['type'] ?? '') === 'boolean') {
                $validated[$key] = $request->boolean($key) ? 'true' : 'false';
            }
        }

        try {
            $this->envFile->setMany($validated, $sensitiveKeys);

            Artisan::call('config:clear');

            Log::info('Website settings updated via admin', [
                'tab' => $tab,
                'keys' => array_keys($validated),
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.settings.index', ['tab' => $tab])
                ->with('success', ucfirst($tabConfig['label']) . ' settings saved to .env successfully. Config cache cleared.');
        } catch (\Throwable $e) {
            Log::error('Website settings update failed', [
                'tab' => $tab,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.settings.index', ['tab' => $tab])
                ->with('error', 'Failed to update .env: ' . $e->getMessage());
        }
    }

    public function testDatabase(Request $request)
    {
        $data = $request->validate([
            'DB_CONNECTION' => ['required', Rule::in(['mysql', 'sqlite', 'pgsql'])],
            'DB_HOST' => ['nullable', 'string', 'max:255'],
            'DB_PORT' => ['nullable', 'integer'],
            'DB_DATABASE' => ['nullable', 'string', 'max:255'],
            'DB_USERNAME' => ['nullable', 'string', 'max:255'],
            'DB_PASSWORD' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->filled('DB_PASSWORD')) {
            $password = $request->input('DB_PASSWORD');
        } else {
            $password = $this->envFile->get('DB_PASSWORD', '');
        }

        try {
            config([
                'database.connections.settings_test' => [
                    'driver' => $data['DB_CONNECTION'],
                    'host' => $data['DB_HOST'] ?? '127.0.0.1',
                    'port' => $data['DB_PORT'] ?? '3306',
                    'database' => $data['DB_DATABASE'] ?? '',
                    'username' => $data['DB_USERNAME'] ?? '',
                    'password' => $password,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => true,
                ],
            ]);

            DB::connection('settings_test')->getPdo();

            return response()->json([
                'success' => true,
                'message' => 'Database connection successful.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function testMail(Request $request)
    {
        $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        try {
            Mail::raw('This is a test email from ' . config('app.name') . ' website settings.', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('SMTP Test - ' . config('app.name'));
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent to ' . $request->test_email,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mail failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function runDatabaseBackup()
    {
        try {
            if (!class_exists(\Spatie\Backup\BackupServiceProvider::class)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Spatie Backup package is not available.',
                ], 500);
            }

            Artisan::call('backup:run', ['--only-db' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Database backup started/completed. Check storage/app/backups or your backup disk.',
                'output' => trim(Artisan::output()),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function backupEnv()
    {
        try {
            $path = $this->envFile->backup();

            return response()->json([
                'success' => true,
                'message' => 'Environment file backed up successfully.',
                'file' => basename($path),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function clearCache(Request $request)
    {
        $request->validate([
            'cache_type' => 'required|in:application,config,route,view,all',
        ]);

        $cacheType = $request->cache_type;

        try {
            $results = $this->runCacheClears($cacheType);
            $failed = collect($results)->filter(fn ($ok) => !$ok)->keys()->all();

            Log::info('Cache cleared from website settings', [
                'cache_type' => $cacheType,
                'user_id' => auth()->id(),
                'results' => $results,
            ]);

            if (!empty($failed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some cache commands failed: ' . implode(', ', $failed),
                    'results' => $results,
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => "Cache cleared successfully ({$cacheType}).",
                'results' => $results,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear caches only — do not run route:cache / config:cache from the web UI.
     * This project uses many closure routes; rebuilding route cache can exhaust memory and break JSON responses.
     */
    private function runCacheClears(string $cacheType): array
    {
        $commands = match ($cacheType) {
            'application' => ['cache:clear'],
            'config' => ['config:clear'],
            'route' => ['route:clear'],
            'view' => ['view:clear'],
            'all' => ['cache:clear', 'config:clear', 'route:clear', 'view:clear'],
            default => [],
        };

        $results = [];

        foreach ($commands as $command) {
            $results[$command] = $this->runArtisanSilently($command);
        }

        return $results;
    }

    private function runArtisanSilently(string $command): bool
    {
        ob_start();
        try {
            return Artisan::call($command) === 0;
        } finally {
            ob_end_clean();
        }
    }

    private function getCacheStatistics(): array
    {
        $stats = [
            'driver' => config('cache.default'),
            'total_entries' => 0,
            'active_entries' => 0,
            'expired_entries' => 0,
            'size_estimate' => 'N/A',
        ];

        try {
            if (config('cache.default') === 'database') {
                $total = DB::table('cache')->count();
                $active = DB::table('cache')->where('expiration', '>', time())->count();
                $stats['total_entries'] = $total;
                $stats['active_entries'] = $active;
                $stats['expired_entries'] = $total - $active;
            }
        } catch (\Throwable) {
            // ignore
        }

        return $stats;
    }
}
