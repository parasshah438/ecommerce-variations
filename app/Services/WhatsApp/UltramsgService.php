<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UltramsgService
{
    protected $baseUrl;
    protected $instanceId;
    protected $token;
    protected $timeout;
    
    public function __construct()
    {
        $this->baseUrl = config('whatsapp.ultramsg.base_url');
        $this->instanceId = config('whatsapp.ultramsg.instance_id');
        $this->token = config('whatsapp.ultramsg.token');
        $this->timeout = config('whatsapp.ultramsg.timeout', 30);
        
        // Use HTTP instead of HTTPS if configured (for local development)
        if (config('whatsapp.ultramsg.use_http', false)) {
            $this->baseUrl = str_replace('https://', 'http://', $this->baseUrl);
        }
    }
    
    /**
     * Send text message
     */
    public function sendText(string $phone, string $message): array
    {
        return $this->sendRequest('messages/chat', [
            'to' => $phone,
            'body' => $message,
            'priority' => 1
        ]);
    }
    
    /**
     * Send image message
     */
    public function sendImage(string $phone, string $imageUrl, ?string $caption = null): array
    {
        $data = [
            'to' => $phone,
            'image' => $imageUrl,
            'priority' => 1
        ];
        
        if ($caption) {
            $data['caption'] = $caption;
        }
        
        return $this->sendRequest('messages/image', $data);
    }
    
    /**
     * Send document
     */
    public function sendDocument(string $phone, string $documentUrl, string $filename): array
    {
        return $this->sendRequest('messages/document', [
            'to' => $phone,
            'document' => $documentUrl,
            'filename' => $filename,
            'priority' => 1
        ]);
    }
    
    /**
     * Send audio message
     */
    public function sendAudio(string $phone, string $audioUrl): array
    {
        return $this->sendRequest('messages/audio', [
            'to' => $phone,
            'audio' => $audioUrl,
            'priority' => 1
        ]);
    }
    
    /**
     * Send video message
     */
    public function sendVideo(string $phone, string $videoUrl, ?string $caption = null): array
    {
        $data = [
            'to' => $phone,
            'video' => $videoUrl,
            'priority' => 1
        ];
        
        if ($caption) {
            $data['caption'] = $caption;
        }
        
        return $this->sendRequest('messages/video', $data);
    }
    
    /**
     * Send contact
     */
    public function sendContact(string $phone, string $contactPhone, string $contactName): array
    {
        return $this->sendRequest('messages/contact', [
            'to' => $phone,
            'contact' => $contactPhone,
            'priority' => 1
        ]);
    }
    
    /**
     * Send location
     */
    public function sendLocation(string $phone, float $latitude, float $longitude, ?string $address = null): array
    {
        $data = [
            'to' => $phone,
            'lat' => $latitude,
            'lng' => $longitude,
            'priority' => 1
        ];
        
        if ($address) {
            $data['address'] = $address;
        }
        
        return $this->sendRequest('messages/location', $data);
    }
    
    /**
     * Send template message
     */
    public function sendTemplate(string $phone, string $template): array
    {
        return $this->sendRequest('messages/chat', [
            'to' => $phone,
            'body' => $template,
            'priority' => 1
        ]);
    }
    
    /**
     * Get message status
     */
    public function getMessageStatus(string $messageId): array
    {
        return $this->sendRequest("messages/{$messageId}/status", [], 'GET');
    }
    
    /**
     * Get instance status
     */
    public function getInstanceStatus(): array
    {
        $cacheKey = "whatsapp_instance_status_{$this->instanceId}";
        
        return Cache::remember($cacheKey, 60, function () {
            return $this->sendRequest('instance/status', [], 'GET');
        });
    }
    
    /**
     * Get QR code for authentication
     */
    public function getQRCode(): array
    {
        return $this->sendRequest('instance/qr', [], 'GET');
    }
    
    /**
     * Restart instance
     */
    public function restartInstance(): array
    {
        // Clear cache when restarting
        Cache::forget("whatsapp_instance_status_{$this->instanceId}");
        
        return $this->sendRequest('instance/restart', [], 'POST');
    }
    
    /**
     * Logout instance
     */
    public function logoutInstance(): array
    {
        // Clear cache when logging out
        Cache::forget("whatsapp_instance_status_{$this->instanceId}");
        
        return $this->sendRequest('instance/logout', [], 'POST');
    }
    
    /**
     * Get instance info
     */
    public function getInstanceInfo(): array
    {
        return $this->sendRequest('instance/me', [], 'GET');
    }
    
    /**
     * Set webhook URL
     */
    public function setWebhook(string $webhookUrl): array
    {
        return $this->sendRequest('instance/settings', [
            'webhook' => $webhookUrl,
            'webhookMessage' => 1,
            'webhookStatus' => 1
        ], 'POST');
    }
    
    /**
     * Get contacts from WhatsApp
     */
    public function getContacts(): array
    {
        return $this->sendRequest('contacts', [], 'GET');
    }
    
    /**
     * Get chats
     */
    public function getChats(): array
    {
        return $this->sendRequest('chats', [], 'GET');
    }
    
    /**
     * Create group
     */
    public function createGroup(string $groupName, array $participants): array
    {
        return $this->sendRequest('groups', [
            'name' => $groupName,
            'participants' => implode(',', $participants)
        ], 'POST');
    }
    
    /**
     * Add participant to group
     */
    public function addParticipantToGroup(string $groupId, string $participant): array
    {
        return $this->sendRequest("groups/{$groupId}/participants", [
            'participant' => $participant
        ], 'POST');
    }
    
    /**
     * Remove participant from group
     */
    public function removeParticipantFromGroup(string $groupId, string $participant): array
    {
        return $this->sendRequest("groups/{$groupId}/participants", [
            'participant' => $participant
        ], 'DELETE');
    }
    
    /**
     * Get group info
     */
    public function getGroupInfo(string $groupId): array
    {
        return $this->sendRequest("groups/{$groupId}", [], 'GET');
    }
    
    /**
     * Leave group
     */
    public function leaveGroup(string $groupId): array
    {
        return $this->sendRequest("groups/{$groupId}/leave", [], 'POST');
    }
    
    /**
     * Check if phone number has WhatsApp
     */
    public function checkWhatsAppNumber(string $phone): array
    {
        return $this->sendRequest('contacts/check', [
            'chatId' => $phone
        ], 'POST');
    }
    
    /**
     * Get message history
     */
    public function getMessageHistory(string $chatId, int $limit = 50): array
    {
        return $this->sendRequest('chats/messages', [
            'chatId' => $chatId,
            'limit' => $limit
        ], 'GET');
    }
    
    /**
     * Send request to Ultramsg API
     */
    protected function sendRequest(string $endpoint, array $data = [], string $method = 'POST'): array
    {
        try {
            $url = "{$this->baseUrl}/instance{$this->instanceId}/{$endpoint}";
            
            Log::info('Ultramsg API Request', [
                'url' => $url,
                'method' => $method,
                'data' => $data
            ]);
            
            // Configure HTTP client with SSL options
            $request = Http::timeout($this->timeout)
                ->withToken($this->token)
                ->withOptions([
                    'verify' => config('whatsapp.ultramsg.verify_ssl', false), // Disable SSL verification for local development
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => config('whatsapp.ultramsg.verify_ssl', false),
                        CURLOPT_SSL_VERIFYHOST => config('whatsapp.ultramsg.verify_ssl', false) ? 2 : 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_MAXREDIRS => 3,
                        CURLOPT_USERAGENT => 'WhatsApp-Laravel-Integration/1.0',
                    ]
                ]);
            
            if ($method === 'GET') {
                $response = $request->get($url, $data);
            } elseif ($method === 'POST') {
                $response = $request->post($url, $data);
            } elseif ($method === 'PUT') {
                $response = $request->put($url, $data);
            } elseif ($method === 'DELETE') {
                $response = $request->delete($url, $data);
            } else {
                throw new \InvalidArgumentException("Unsupported HTTP method: {$method}");
            }
            
            $responseData = $response->json();
            
            Log::info('Ultramsg API Response', [
                'status' => $response->status(),
                'data' => $responseData
            ]);
            
            if (!$response->successful()) {
                throw new \Exception("API request failed with status {$response->status()}: " . json_encode($responseData));
            }
            
            return $responseData ?? [];
            
        } catch (\Exception $e) {
            Log::error('Ultramsg API Error', [
                'endpoint' => $endpoint,
                'method' => $method,
                'error' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Ultramsg API error: {$e->getMessage()}");
        }
    }
    
    /**
     * Test API connection with fallback options
     */
    public function testConnection(): array
    {
        try {
            // First attempt with current configuration
            $response = $this->getInstanceStatus();
            
            return [
                'success' => true,
                'message' => 'Connection successful',
                'data' => $response
            ];
            
        } catch (\Exception $e) {
            // If SSL error, try with different configurations
            if (strpos($e->getMessage(), 'SSL certificate') !== false || 
                strpos($e->getMessage(), 'cURL error 60') !== false) {
                
                try {
                    // Try with a simple test endpoint bypassing SSL
                    $response = $this->testConnectionWithoutSSL();
                    
                    return [
                        'success' => true,
                        'message' => 'Connection successful (SSL verification disabled)',
                        'data' => $response,
                        'warning' => 'SSL verification is disabled. Enable SSL verification in production.'
                    ];
                    
                } catch (\Exception $e2) {
                    return [
                        'success' => false,
                        'message' => 'Connection failed even with SSL disabled: ' . $e2->getMessage(),
                        'data' => null,
                        'original_error' => $e->getMessage()
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Test connection without SSL verification
     */
    protected function testConnectionWithoutSSL(): array
    {
        $url = "{$this->baseUrl}/instance{$this->instanceId}/status";
        
        $response = Http::timeout($this->timeout)
            ->withToken($this->token)
            ->withOptions([
                'verify' => false,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_USERAGENT => 'WhatsApp-Laravel-Integration/1.0',
                ]
            ])
            ->get($url);
            
        if (!$response->successful()) {
            throw new \Exception("API request failed with status {$response->status()}");
        }
        
        return $response->json() ?? [];
    }
    
    /**
     * Format phone number for API
     */
    public function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Remove leading zeros
        $phone = ltrim($phone, '0');
        
        // Add country code if not present (assuming India +91)
        if (!str_starts_with($phone, '91')) {
            $phone = '91' . $phone;
        }
        
        return $phone . '@c.us'; // WhatsApp format
    }
    
    /**
     * Validate webhook signature
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->token);
        
        return hash_equals($expectedSignature, $signature);
    }
}