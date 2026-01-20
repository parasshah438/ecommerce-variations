# Shiprocket Laravel Service Classes

A comprehensive, production-ready Laravel package for Shiprocket API integration. This package provides a complete set of service classes for managing orders, shipments, couriers, and returns through Shiprocket's API.

## Features

- **Complete API Coverage**: All Shiprocket APIs implemented with proper request/response handling
- **Production Ready**: Error handling, logging, caching, and retry logic
- **Modular Architecture**: Separate service classes for different functionalities
- **Helper Classes**: Data transformation utilities for easy integration
- **Comprehensive Validation**: Input validation and data sanitization
- **Performance Optimized**: Caching, bulk operations, and efficient API usage
- **Laravel Integration**: Follows Laravel conventions and best practices

## Installation

### 1. Add Service Classes

All service classes are already created in your `app/Services` directory:
- `ShiprocketService.php` - Main order management
- `ShiprocketCourierService.php` - Courier operations
- `ShiprocketShipmentService.php` - Shipment tracking and management
- `ShiprocketReturnService.php` - Return and exchange handling
- `ShiprocketManager.php` - Main facade class

### 2. Configuration

Add the configuration file `config/shiprocket.php` and set your environment variables:

```env
SHIPROCKET_EMAIL=your-shiprocket-email@example.com
SHIPROCKET_PASSWORD=your-shiprocket-password
SHIPROCKET_BASE_URL=https://apiv2.shiprocket.in/v1/external
SHIPROCKET_TOKEN_CACHE_DURATION=3600
SHIPROCKET_RETURN_WINDOW_DAYS=7
SHIPROCKET_AUTO_ASSIGN_COURIER=false
SHIPROCKET_PREFER_CHEAPEST=true
```

### 3. Install Dependencies

Add GuzzleHttp to your composer.json if not already present:

```bash
composer require guzzlehttp/guzzle
```

### 4. Register Service Provider (Optional)

Create a service provider to bind the services:

```php
// app/Providers/ShiprocketServiceProvider.php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ShiprocketManager;

class ShiprocketServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ShiprocketManager::class, function ($app) {
            return new ShiprocketManager();
        });
    }
}
```

Add to `config/app.php`:

```php
'providers' => [
    // Other providers...
    App\Providers\ShiprocketServiceProvider::class,
],
```

## Usage Examples

### Basic Order Creation

```php
use App\Services\ShiprocketManager;

$shiprocket = new ShiprocketManager();

$orderData = [
    'order_id' => 'ORD-12345',
    'order_date' => '2024-01-20 10:30',
    'pickup_location' => 'Delhi',
    'billing_customer_name' => 'John',
    'billing_last_name' => 'Doe',
    'billing_address' => '123 Main Street',
    'billing_city' => 'Delhi',
    'billing_pincode' => '110001',
    'billing_state' => 'Delhi',
    'billing_country' => 'India',
    'billing_email' => 'john@example.com',
    'billing_phone' => '9876543210',
    'shipping_is_billing' => true,
    'order_items' => [
        [
            'name' => 'Product 1',
            'sku' => 'PROD-001',
            'units' => 2,
            'selling_price' => 500,
            'discount' => 0,
            'tax' => 0,
            'hsn' => '1234'
        ]
    ],
    'payment_method' => 'Prepaid',
    'sub_total' => 1000,
    'length' => 10,
    'breadth' => 10,
    'height' => 5,
    'weight' => 0.5
];

// Create order with automatic courier assignment
$result = $shiprocket->createOrder($orderData, true);

if ($result['success']) {
    echo "Order created: " . $result['order_id'];
    if ($result['courier_assigned']) {
        echo "AWB: " . $result['awb']['awb_code'];
    }
}
```

### Courier Services

```php
// Check serviceability
$serviceability = $shiprocket->couriers()->checkServiceabilityForLocation(
    '110001', // pickup postcode
    '400001', // delivery postcode
    0.5,      // weight in kg
    0         // COD (0 = prepaid, 1 = COD)
);

// Get cheapest courier
$cheapest = $shiprocket->couriers()->getCheapestCourier('110001', '400001', 0.5, 0);

// Get fastest courier
$fastest = $shiprocket->couriers()->getFastestCourier('110001', '400001', 0.5, 0);

// Get recommended courier with preferences
$recommended = $shiprocket->couriers()->getRecommendedCourier(
    '110001', 
    '400001', 
    0.5, 
    0,
    [
        'price_weight' => 0.5,  // 50% weightage to price
        'speed_weight' => 0.3,  // 30% weightage to speed
        'rating_weight' => 0.2, // 20% weightage to rating
        'filters' => [
            'max_rate' => 100,
            'max_delivery_days' => 3,
            'min_rating' => 4.0
        ]
    ]
);
```

### Shipment Tracking

```php
// Track single shipment
$tracking = $shiprocket->shipments()->trackShipment('AWB123456789');

// Bulk track multiple shipments
$awbCodes = ['AWB123456789', 'AWB987654321', 'AWB456789123'];
$bulkTracking = $shiprocket->shipments()->bulkTrack($awbCodes);

// Get current status
$status = $shiprocket->shipments()->getCurrentStatus('AWB123456789');

// Check if delivered
$isDelivered = $shiprocket->shipments()->isDelivered('AWB123456789');

// Get performance metrics
$metrics = $shiprocket->shipments()->getPerformanceMetrics([12345, 67890]);
```

### Return Management

```php
// Check return eligibility
$eligibility = $shiprocket->returns()->checkReturnEligibility([
    'original_order_id' => 12345
]);

if ($eligibility['eligible']) {
    // Create return order
    $returnData = [
        'order_id' => 'RET-12345',
        'order_date' => '2024-01-20',
        'pickup_customer_name' => 'John Doe',
        'pickup_address' => '123 Main Street',
        'pickup_city' => 'Delhi',
        'pickup_state' => 'Delhi',
        'pickup_pincode' => '110001',
        'pickup_email' => 'john@example.com',
        'pickup_phone' => '9876543210',
        'shipping_customer_name' => 'Company Warehouse',
        'shipping_address' => '456 Warehouse St',
        'shipping_city' => 'Mumbai',
        'shipping_state' => 'Maharashtra',
        'shipping_pincode' => '400001',
        'shipping_email' => 'warehouse@company.com',
        'shipping_phone' => '9876543210',
        'order_items' => [
            [
                'name' => 'Product 1',
                'sku' => 'PROD-001',
                'units' => 1,
                'selling_price' => 500,
                'qc_enable' => true,
                'qc_product_name' => 'Product 1',
                'qc_brand' => 'Brand Name'
            ]
        ],
        'payment_method' => 'PREPAID',
        'sub_total' => 500,
        'length' => 10,
        'breadth' => 10,
        'height' => 5,
        'weight' => 0.5
    ];
    
    $returnResult = $shiprocket->returns()->processReturn($returnData);
}
```

### Data Transformation Helper

```php
use App\Helpers\ShiprocketHelper;

// Transform Laravel order model to Shiprocket format
$order = Order::find(1); // Your Laravel order model
$shiprocketOrder = ShiprocketHelper::transformOrder($order);

// Transform address
$address = $order->shippingAddress;
$shiprocketAddress = ShiprocketHelper::transformAddress($address, 'shipping');

// Calculate package dimensions from items
$dimensions = ShiprocketHelper::calculatePackageDimensions($order->items);

// Format phone number
$formattedPhone = ShiprocketHelper::formatPhoneNumber('+91-98765-43210');

// Validate pincode
$isValid = ShiprocketHelper::validatePincode('110001');
```

### Dashboard and Monitoring

```php
// Get comprehensive dashboard data
$dashboard = $shiprocket->getDashboardData();

// Health check for all services
$health = $shiprocket->healthCheck();

// Get delayed shipments
$delayed = $shiprocket->shipments()->getDelayedShipments();
```

## API Routes

The package includes pre-defined API routes in `routes/shiprocket.php`. Include this in your `routes/api.php`:

```php
// routes/api.php
require __DIR__.'/shiprocket.php';
```

Available endpoints:

- `GET /api/shiprocket/health` - Health check
- `GET /api/shiprocket/dashboard` - Dashboard data
- `POST /api/shiprocket/orders` - Create order
- `GET /api/shiprocket/orders/{id}` - Get order details
- `DELETE /api/shiprocket/orders/cancel` - Cancel orders
- `POST /api/shiprocket/couriers/serviceability` - Check serviceability
- `POST /api/shiprocket/couriers/recommendations` - Get courier recommendations
- `GET /api/shiprocket/shipments/track/{awb}` - Track shipment
- `POST /api/shiprocket/returns` - Create return
- And many more...

## Error Handling

All services include comprehensive error handling:

```php
try {
    $result = $shiprocket->orders()->createOrder($orderData);
} catch (Exception $e) {
    Log::error('Shiprocket order creation failed', [
        'error' => $e->getMessage(),
        'order_data' => $orderData
    ]);
    
    // Handle the error appropriately
    return response()->json(['error' => 'Order creation failed'], 500);
}
```

## Logging

All API interactions are logged automatically. Configure logging levels in your `.env`:

```env
SHIPROCKET_LOG_REQUESTS=true
LOG_LEVEL=info
```

## Caching

Authentication tokens are automatically cached to reduce API calls:

```env
SHIPROCKET_TOKEN_CACHE_DURATION=3600  # 1 hour
```

## Rate Limiting

The service includes built-in rate limiting awareness:

```env
SHIPROCKET_RATE_LIMIT=60  # requests per minute
```

## Production Considerations

1. **Environment Variables**: Never commit credentials to version control
2. **SSL Verification**: Enabled in production, disabled in local development
3. **Timeout Settings**: Configurable timeout for API requests
4. **Error Logging**: Comprehensive logging for debugging
5. **Input Validation**: All inputs are validated before API calls
6. **Retry Logic**: Automatic token refresh on 401 errors

## Testing

Create feature tests for your Shiprocket integration:

```php
// tests/Feature/ShiprocketTest.php
public function test_can_create_order()
{
    $response = $this->postJson('/api/shiprocket/orders', [
        // order data
    ]);

    $response->assertStatus(200)
            ->assertJson(['success' => true]);
}
```

## Support

This package covers all major Shiprocket APIs based on the provided ShiprocketApis.txt file:

1. ✅ Authentication (login/logout)
2. ✅ Order Management (create, update, cancel)
3. ✅ Courier Services (list, serviceability, AWB generation)
4. ✅ Shipment Tracking and Management
5. ✅ Return and Exchange Orders
6. ✅ Label and Invoice Generation
7. ✅ Manifest Generation
8. ✅ Bulk Operations
9. ✅ Performance Monitoring

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).