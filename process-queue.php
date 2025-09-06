<?php

/**
 * Enhanced Queue processor for shared hosting (cPanel/GoDaddy)
 * Processes both regular queue and email retries
 */

// Set time limit
set_time_limit(300); // 5 minutes max

// Change to Laravel directory
$laravelPath = __DIR__;
chdir($laravelPath);

// Bootstrap Laravel
require $laravelPath . '/vendor/autoload.php';
$app = require_once $laravelPath . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check if another instance is running
$lockFile = $laravelPath . '/storage/app/queue.lock';

if (file_exists($lockFile)) {
    $lockTime = filemtime($lockFile);
    // If lock file is older than 10 minutes, remove it (probably stale)
    if (time() - $lockTime > 600) {
        unlink($lockFile);
    } else {
        // Another process is running, exit
        logMessage('Another queue process is running, exiting');
        exit();
    }
}

// Create lock file
file_put_contents($lockFile, time());

try {
    logMessage('Starting queue processing...');
    
    // 1. Process regular queue jobs (limit to 5 jobs per run)
    $queueOutput = '';
    for ($i = 0; $i < 5; $i++) {
        $output = shell_exec('php artisan queue:work --once --stop-when-empty --timeout=60 2>&1');
        if (strpos($output, 'No jobs') !== false) {
            break; // No more jobs
        }
        $queueOutput .= $output . "\n";
    }
    
    if (!empty(trim($queueOutput))) {
        logMessage('Queue jobs processed: ' . $queueOutput);
    }
    
    // 2. Process email retries using ReliableEmailService
    try {
        $emailService = app(\App\Services\ReliableEmailService::class);
        $retryResults = $emailService->processRetryQueue();
        
        if ($retryResults['processed'] > 0) {
            logMessage("Email retries processed: {$retryResults['processed']} total, {$retryResults['successful']} successful, {$retryResults['failed']} failed");
            
            if (!empty($retryResults['errors'])) {
                logMessage('Email retry errors: ' . implode('; ', $retryResults['errors']));
            }
        }
        
    } catch (Exception $e) {
        logMessage('Email retry processing failed: ' . $e->getMessage());
    }
    
    // 3. Clean up old completed jobs (older than 7 days)
    try {
        $cleanupCount = \DB::table('jobs')->where('created_at', '<', now()->subDays(7))->delete();
        if ($cleanupCount > 0) {
            logMessage("Cleaned up {$cleanupCount} old job records");
        }
    } catch (Exception $e) {
        logMessage('Job cleanup failed: ' . $e->getMessage());
    }
    
    // 4. Log email statistics
    try {
        $emailService = app(\App\Services\ReliableEmailService::class);
        $stats = $emailService->getStats();
        logMessage("Email stats - Total: {$stats['total_emails']}, Sent: {$stats['sent']}, Pending: {$stats['pending']}, Retry: {$stats['retry']}, Failed: {$stats['failed']}");
    } catch (Exception $e) {
        logMessage('Stats logging failed: ' . $e->getMessage());
    }
    
    logMessage('Queue processing completed successfully');
    
} catch (Exception $e) {
    logMessage('CRITICAL ERROR in queue processing: ' . $e->getMessage());
    logMessage('Stack trace: ' . $e->getTraceAsString());
} finally {
    // Always remove lock file
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
}

/**
 * Log message to both file and database
 */
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "{$timestamp}: {$message}\n";
    
    // Log to file
    $logFile = __DIR__ . '/storage/logs/queue-cron.log';
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    // Also log to Laravel log
    try {
        \Log::info('Cron Queue: ' . $message);
    } catch (Exception $e) {
        // Fallback if Laravel logging fails
        error_log('Queue Cron: ' . $message);
    }
    
    // Output for cron job logs
    echo $logMessage;
}

echo "Queue processing completed at " . date('Y-m-d H:i:s') . "\n";
?>
