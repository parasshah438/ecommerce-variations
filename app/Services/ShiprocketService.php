<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\RequestException;

/**
 * Shiprocket API Service
 * Production-ready service class for Shiprocket API integration
 */
class ShiprocketService
{
    protected $baseUrl;
    protected $email;
    protected $password;
    protected $token;
    protected $tokenCacheKey = 'shiprocket_token';
    protected $tokenCacheDuration = 3600; // 1 hour

    public function __construct()
    {
        $this->baseUrl = config('services.shiprocket.base_url', 'https://apiv2.shiprocket.in/v1/external');
        $this->email = config('services.shiprocket.email');
        $this->password = config('services.shiprocket.password');
    }

    /**
     * Validate that credentials are configured
     */
    protected function validateCredentials(): void
    {
        if (empty($this->email) || empty($this->password)) {
            throw new Exception('Shiprocket credentials not configured. Please set SHIPROCKET_EMAIL and SHIPROCKET_PASSWORD in your .env file');
        }
    }

    /**
     * Generate authentication token
     */
    public function generateToken(): array
    {
        $this->validateCredentials();
        
        try {
            $response = Http::timeout(30)
                ->post($this->baseUrl . '/auth/login', [
                    'email' => $this->email,
                    'password' => $this->password,
                ]);

            $data = $response->json();
            
            if (isset($data['token'])) {
                $this->token = $data['token'];
                Cache::put($this->tokenCacheKey, $this->token, $this->tokenCacheDuration);
                
                Log::info('Shiprocket token generated successfully');
                return $data;
            }

            throw new Exception('Token not received from Shiprocket API');

        } catch (RequestException $e) {
            Log::error('Shiprocket token generation failed', [
                'error' => $e->getMessage(),
                'response' => $e->response?->body()
            ]);
            throw new Exception('Failed to generate Shiprocket token: ' . $e->getMessage());
        }
    }

    /**
     * Get authentication token (from cache or generate new)
     */
    public function getToken(): string
    {
        if ($this->token) {
            return $this->token;
        }

        $cachedToken = Cache::get($this->tokenCacheKey);
        if ($cachedToken) {
            $this->token = $cachedToken;
            return $this->token;
        }

        $tokenData = $this->generateToken();
        return $this->token;
    }

    /**
     * Logout and invalidate token
     */
    public function logout(): bool
    {
        try {
            $response = Http::timeout(30)
                ->withToken($this->getToken())
                ->post($this->baseUrl . '/auth/logout');

            Cache::forget($this->tokenCacheKey);
            $this->token = null;
            
            Log::info('Shiprocket logout successful');
            return true;

        } catch (RequestException $e) {
            Log::error('Shiprocket logout failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Make authenticated API request
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $request = Http::timeout(30)
                ->withToken($this->getToken());

            $response = $request->send($method, $this->baseUrl . $endpoint, [
                'json' => $data
            ]);
            
            $responseData = $response->json();

            Log::info('Shiprocket API request successful', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status_code' => $response->getStatusCode()
            ]);

            return $responseData;

        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            $responseBody = $e->response?->body();
            $statusCode = $e->response?->status();

            // Token expired, try to regenerate
            if ($statusCode === 401) {
                Log::warning('Shiprocket token expired, regenerating...');
                Cache::forget($this->tokenCacheKey);
                $this->token = null;
                
                // Retry the request once with new token
                return $this->makeRequest($method, $endpoint, $data);
            }

            Log::error('Shiprocket API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $errorMessage,
                'response' => $responseBody
            ]);

            throw new Exception('Shiprocket API request failed: ' . $errorMessage);
        }
    }

    /**
     * Create or update order (adhoc)
     */
    public function createOrder(array $orderData): array
    {
        $this->validateOrderData($orderData);
        return $this->makeRequest('POST', '/orders/create/adhoc', $orderData);
    }

    /**
     * Create channel specific order
     */
    public function createChannelOrder(array $orderData): array
    {
        $this->validateOrderData($orderData);
        return $this->makeRequest('POST', '/orders/create', $orderData);
    }

    /**
     * Update pickup location of created orders
     */
    public function updatePickupLocation(array $orderIds, string $pickupLocation): array
    {
        return $this->makeRequest('PATCH', '/orders/address/pickup', [
            'order_id' => $orderIds,
            'pickup_location' => $pickupLocation,
        ]);
    }

    /**
     * Update customer delivery address
     */
    public function updateDeliveryAddress(int $orderId, array $addressData): array
    {
        $data = array_merge(['order_id' => $orderId], $addressData);
        return $this->makeRequest('POST', '/orders/address/update', $data);
    }

    /**
     * Update order (adhoc)
     */
    public function updateOrder(array $orderData): array
    {
        $this->validateOrderData($orderData);
        return $this->makeRequest('POST', '/orders/update/adhoc', $orderData);
    }

    /**
     * Cancel orders
     */
    public function cancelOrders(array $orderIds): array
    {
        return $this->makeRequest('POST', '/orders/cancel', [
            'ids' => $orderIds,
        ]);
    }

    /**
     * Add inventory for ordered products
     */
    public function addInventory(array $inventoryData): array
    {
        return $this->makeRequest('PATCH', '/orders/fulfill', [
            'data' => $inventoryData,
        ]);
    }

    /**
     * Map unmapped products
     */
    public function mapProducts(array $mappingData): array
    {
        return $this->makeRequest('PATCH', '/orders/mapping', [
            'data' => $mappingData,
        ]);
    }

    /**
     * Import orders in bulk
     */
    public function importOrders(string $filePath): array
    {
        try {
            $response = Http::timeout(30)
                ->withToken($this->getToken())
                ->attach('file', fopen($filePath, 'r'), basename($filePath))
                ->post($this->baseUrl . '/orders/import');

            return $response->json();

        } catch (RequestException $e) {
            Log::error('Shiprocket bulk import failed', ['error' => $e->getMessage()]);
            throw new Exception('Failed to import orders: ' . $e->getMessage());
        }
    }

    /**
     * Get all orders with pagination
     */
    public function getOrders(int $page = 1, int $perPage = 10): array
    {
        return $this->makeRequest('GET', "/orders?page={$page}&per_page={$perPage}");
    }

    /**
     * Get specific order details
     */
    public function getOrderDetails(int $orderId): array
    {
        return $this->makeRequest('GET', "/orders/show/{$orderId}");
    }

    /**
     * Export orders
     */
    public function exportOrders(array $filters = []): array
    {
        return $this->makeRequest('POST', '/orders/export', $filters);
    }

    /**
     * Validate order data structure
     */
    protected function validateOrderData(array $orderData): void
    {
        $requiredFields = [
            'order_id', 'order_date', 'pickup_location', 'billing_customer_name',
            'billing_address', 'billing_city', 'billing_pincode', 'billing_state',
            'billing_country', 'billing_email', 'billing_phone', 'order_items',
            'payment_method', 'sub_total'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($orderData[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        // Validate order items
        if (empty($orderData['order_items']) || !is_array($orderData['order_items'])) {
            throw new Exception('Order items must be a non-empty array');
        }

        foreach ($orderData['order_items'] as $item) {
            $requiredItemFields = ['name', 'sku', 'units', 'selling_price'];
            foreach ($requiredItemFields as $field) {
                if (!isset($item[$field])) {
                    throw new Exception("Missing required order item field: {$field}");
                }
            }
        }
    }

    /**
     * Health check - verify API connectivity
     */
    public function healthCheck(): bool
    {
        try {
            $this->getToken();
            return true;
        } catch (Exception $e) {
            Log::error('Shiprocket health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}