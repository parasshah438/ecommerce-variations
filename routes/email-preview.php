<?php

use Illuminate\Support\Facades\Route;
use App\Mail\WelcomeEmail;
use App\Mail\OrderConfirmationMail;
use App\Mail\AdminOrderNotificationMail;
use App\Mail\OrderStatusMail;
use App\Models\User;
use App\Models\Address;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\OrderItem;
use App\Models\Order;

Route::get('/preview-welcome-email', function () {
    // Get the first user or create a sample one for preview
    $user = User::first() ?? new User([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'country_code' => '+91',
        'mobile_number' => '9876543210',
        'created_at' => now()
    ]);

    return new WelcomeEmail($user);
})->name('preview.welcome.email');

/**
 * Load a real order (or a sample) with everything the templates need.
 */
if (!function_exists('previewOrder')) {

function previewOrder(): Order
{
    $order = Order::with(['user', 'address', 'items.productVariation.product'])->first();

    if ($order) {
        return $order;
    }

    // Build a sample order for previewing without database data.
    $sampleUser = new User([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $sampleAddress = new Address([
        'name' => 'John Doe',
        'phone' => '+91 98765 43210',
        'address_line' => '123 Main Street, Andheri West',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'zip' => '400053',
    ]);

    $sampleProduct = new Product(['name' => 'Premium Cotton T-Shirt']);
    $sampleVariation = new ProductVariation([
        'sku' => 'CT-BLU-M-001',
        'price' => 999.00,
    ]);
    $sampleVariation->setRelation('product', $sampleProduct);

    $sampleOrder = new Order([
        'id' => 123,
        'status' => 'confirmed',
        'payment_status' => 'pending',
        'payment_method' => 'cod',
        'subtotal' => 1998.00,
        'tax_amount' => 359.64,
        'tax_name' => 'GST',
        'shipping_cost' => 50.00,
        'coupon_discount' => 100.00,
        'coupon_code' => 'SAVE100',
        'total' => 2307.64,
        'created_at' => now(),
    ]);
    $sampleOrder->setRelation('user', $sampleUser);
    $sampleOrder->setRelation('address', $sampleAddress);

    $sampleOrderItem = new OrderItem([
        'quantity' => 2,
        'price' => 999.00,
    ]);
    $sampleOrderItem->setRelation('productVariation', $sampleVariation);
    $sampleOrder->setRelation('items', collect([$sampleOrderItem]));

    return $sampleOrder;
}
} // end if (!function_exists('previewOrder'))

Route::get('/preview-order-confirmation', function () {
    return new OrderConfirmationMail(previewOrder());
})->name('preview.order.confirmation');

Route::get('/preview-admin-order-notification', function () {
    return new AdminOrderNotificationMail(previewOrder());
})->name('preview.admin.order');

Route::get('/preview-order-status', function () {
    return new OrderStatusMail(previewOrder(), 'Your order is being packed and will ship soon.');
})->name('preview.order.status');
