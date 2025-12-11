<?php

namespace App\Jobs;

use App\Helpers\ImageOptimizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OptimizeImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    protected string $filePath;
    protected array $options;
    protected ?string $callbackUrl;

    public function __construct(string $filePath, array $options = [], ?string $callbackUrl = null)
    {
        $this->filePath = $filePath;
        $this->options = $options;
        $this->callbackUrl = $callbackUrl;
    }

    public function handle()
    {
        try {
            $result = ImageOptimizer::processStoredImage($this->filePath, $this->options);
            
            Log::info('Image optimization job completed', [
                'file_path' => $this->filePath,
                'compression_ratio' => $result['compression_ratio'] ?? 0
            ]);

            // Send callback if provided
            if ($this->callbackUrl) {
                $this->sendCallback($result);
            }

        } catch (\Exception $e) {
            Log::error('Image optimization job failed', [
                'file_path' => $this->filePath,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function sendCallback(array $result): void
    {
        try {
            // Send HTTP callback with optimization results
            $ch = curl_init($this->callbackUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($result),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            Log::warning('Callback failed', ['error' => $e->getMessage()]);
        }
    }
}