<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeWithMemory extends Command
{
    protected $signature = 'optimize:safe';
    protected $description = 'Run optimize command with increased memory limit';

    public function handle()
    {
        // Increase memory limit before running optimize
        $originalLimit = ini_get('memory_limit');
        ini_set('memory_limit', '1024M');
        set_time_limit(600); // 10 minutes
        
        $this->info('🚀 Running Laravel optimization with increased memory limit...');
        $this->info("Original memory limit: {$originalLimit}");
        $this->info("Current memory limit: " . ini_get('memory_limit'));
        
        try {
            // Clear all caches first
            $this->call('cache:clear');
            $this->call('config:clear');
            $this->call('route:clear');
            $this->call('view:clear');
            
            // Run optimize command
            $this->call('optimize');
            
            $this->info('✅ Laravel optimization completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Optimization failed: ' . $e->getMessage());
            return 1;
        } finally {
            // Restore original memory limit
            ini_set('memory_limit', $originalLimit);
        }
        
        return 0;
    }
}