<?php

require_once 'vendor/autoload.php';

// Create Laravel Application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Cart;
use App\Services\CartService;

echo "=== TAX CALCULATION DEMONSTRATION ===\n\n";

// Test current configuration (after discount)
echo "CURRENT: Tax AFTER Discount (Default)\n";
echo "==========================================\n";

$cart = Cart::with('coupon', 'items')->where('user_id', 1)->first();

if ($cart) {
    $service = app(CartService::class);
    $summary = $service->cartSummary($cart);
    
    echo "Subtotal: ₹" . number_format($summary['subtotal'], 2) . "\n";
    echo "Discount: ₹" . number_format($summary['discount_amount'], 2) . "\n";
    echo "Taxable Amount: ₹" . number_format($summary['subtotal'] - $summary['discount_amount'], 2) . "\n";
    echo "Tax (18%): ₹" . number_format($summary['tax_amount'], 2) . "\n";
    echo "Final Total: ₹" . number_format($summary['total'], 2) . "\n\n";
    
    // Temporarily change config to test "before discount" calculation
    config(['shop.tax.calculate_on' => 'before_discount']);
    
    echo "COMPARISON: Tax BEFORE Discount\n";
    echo "==========================================\n";
    
    $summaryBefore = $service->cartSummary($cart);
    
    echo "Subtotal: ₹" . number_format($summaryBefore['subtotal'], 2) . "\n";
    echo "Tax (18% on full subtotal): ₹" . number_format($summaryBefore['tax_amount'], 2) . "\n";
    echo "Discount: ₹" . number_format($summaryBefore['discount_amount'], 2) . "\n";
    echo "Final Total: ₹" . number_format($summaryBefore['total'], 2) . "\n\n";
    
    // Show difference
    $difference = $summary['total'] - $summaryBefore['total'];
    echo "DIFFERENCE: ₹" . number_format(abs($difference), 2);
    echo " (" . ($difference > 0 ? "More expensive" : "Less expensive") . " with tax after discount)\n\n";
    
    // Show coupon info
    if ($cart->coupon) {
        echo "APPLIED COUPON DETAILS:\n";
        echo "Code: " . $cart->coupon->code . "\n";
        echo "Type: " . $cart->coupon->type . "\n";
        echo "Value: " . ($cart->coupon->type == 'percentage' ? $cart->coupon->value . '%' : '₹' . $cart->coupon->value) . "\n";
    }
    
} else {
    echo "No cart found for user 1\n";
    
    // Show sample calculation
    echo "\nSAMPLE CALCULATION (₹1000 cart, 50% coupon):\n";
    echo "==========================================\n";
    
    $subtotal = 1000;
    $discountPercent = 50;
    $taxRate = 18;
    
    // After discount tax
    $discount = $subtotal * ($discountPercent / 100);
    $taxableAmount = $subtotal - $discount;
    $taxAfter = $taxableAmount * ($taxRate / 100);
    $totalAfter = $taxableAmount + $taxAfter;
    
    echo "TAX AFTER DISCOUNT:\n";
    echo "Subtotal: ₹" . number_format($subtotal, 2) . "\n";
    echo "Discount (50%): ₹" . number_format($discount, 2) . "\n";
    echo "Taxable Amount: ₹" . number_format($taxableAmount, 2) . "\n";
    echo "Tax (18%): ₹" . number_format($taxAfter, 2) . "\n";
    echo "Final Total: ₹" . number_format($totalAfter, 2) . "\n\n";
    
    // Before discount tax
    $taxBefore = $subtotal * ($taxRate / 100);
    $totalBefore = $subtotal + $taxBefore - $discount;
    
    echo "TAX BEFORE DISCOUNT:\n";
    echo "Subtotal: ₹" . number_format($subtotal, 2) . "\n";
    echo "Tax (18% on full): ₹" . number_format($taxBefore, 2) . "\n";
    echo "Discount (50%): ₹" . number_format($discount, 2) . "\n";
    echo "Final Total: ₹" . number_format($totalBefore, 2) . "\n\n";
    
    $sampleDifference = $totalAfter - $totalBefore;
    echo "DIFFERENCE: ₹" . number_format(abs($sampleDifference), 2);
    echo " (" . ($sampleDifference > 0 ? "More expensive" : "Less expensive") . " with tax after discount)\n";
}

echo "\n=== CONFIGURATION INFO ===\n";
echo "Current tax method: " . config('shop.tax.calculate_on') . "\n";
echo "Tax rate: " . config('shop.tax.rate') . "%\n";
echo "Tax enabled: " . (config('shop.tax.enabled') ? 'Yes' : 'No') . "\n";

?>