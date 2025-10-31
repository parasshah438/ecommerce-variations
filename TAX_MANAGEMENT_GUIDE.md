# 🧾 Tax (GST) Management System Guide

## Current Tax Implementation Status

### ✅ What's Working:
- **Tax calculation** is perfect and working correctly
- **Fixed GST rate** of 18% (configurable)
- **Tax calculation** on checkout page
- **Two calculation methods**: Before discount vs After discount
- **Configuration-based** tax management

### ❌ What's Missing:
- **No database tables** for tax records
- **No tax audit trail** 
- **No product-specific tax rates**
- **No tax reporting**
- **No GST compliance features**

---

## Current Tax Configuration

### 📍 Location: `config/shop.php`

```php
'tax' => [
    'rate' => env('TAX_RATE', 0.18),              // 18% GST by default
    'calculate_on' => env('TAX_CALCULATE_ON', 'after_discount'),
    'enabled' => env('TAX_ENABLED', true),
    'name' => env('TAX_NAME', 'GST'),
    'inclusive' => env('TAX_INCLUSIVE', false),    // Tax exclusive
],
```

### 🔧 Environment Variables (.env):
```bash
TAX_RATE=0.18
TAX_CALCULATE_ON=after_discount
TAX_ENABLED=true
TAX_NAME=GST
TAX_INCLUSIVE=false
```

---

## How Tax Currently Works

### 1. **Calculation Logic (CartService.php)**

#### Method A: Tax AFTER Discount (Default)
```php
$taxableAmount = max($subtotal - $discountAmount, 0);
$taxAmount = $taxableAmount * $taxRate;
$total = $subtotal + $shippingCost + $taxAmount - $discountAmount;
```

**Example:**
- Subtotal: ₹1,000
- Discount (50%): ₹500
- Taxable Amount: ₹500
- Tax (18%): ₹90
- **Final Total: ₹590**

#### Method B: Tax BEFORE Discount
```php
$taxAmount = $subtotal * $taxRate;
$total = $subtotal + $shippingCost + $taxAmount - $discountAmount;
```

**Example:**
- Subtotal: ₹1,000
- Tax (18%): ₹180
- Discount (50%): ₹500
- **Final Total: ₹680**

### 2. **Where Tax is Applied**
- ✅ **Cart page** - Shows tax amount
- ✅ **Checkout page** - Displays tax calculation
- ✅ **Order summary** - Includes tax in total
- ❌ **Database** - Tax amount not stored separately

---

## Setting Up Advanced Tax Management

### 1. Create Tax Tables

#### A. Tax Rates Table
```sql
CREATE TABLE tax_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    rate DECIMAL(5,4) NOT NULL,
    type ENUM('percentage', 'fixed') DEFAULT 'percentage',
    applicable_on ENUM('product', 'category', 'shipping', 'all') DEFAULT 'all',
    state VARCHAR(255) NULL,
    country VARCHAR(255) DEFAULT 'India',
    is_active BOOLEAN DEFAULT TRUE,
    effective_from DATE NOT NULL,
    effective_to DATE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### B. Order Tax Records
```sql
CREATE TABLE order_taxes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    tax_name VARCHAR(255) NOT NULL,
    tax_rate DECIMAL(5,4) NOT NULL,
    taxable_amount DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL,
    tax_type ENUM('cgst', 'sgst', 'igst', 'gst', 'vat') DEFAULT 'gst',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
```

#### C. Product Tax Mapping
```sql
CREATE TABLE product_tax_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    tax_rate_id BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (tax_rate_id) REFERENCES tax_rates(id) ON DELETE CASCADE
);
```

### 2. Create Database Migration

```bash
php artisan make:migration create_tax_management_tables
```

### 3. Tax Models Setup

#### A. TaxRate Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = [
        'name', 'rate', 'type', 'applicable_on', 
        'state', 'country', 'is_active', 
        'effective_from', 'effective_to'
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function isEffective($date = null)
    {
        $date = $date ?: today();
        return $this->is_active && 
               $this->effective_from <= $date && 
               (!$this->effective_to || $this->effective_to >= $date);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_tax_rates')
                    ->withPivot('is_active')
                    ->wherePivot('is_active', true);
    }
}
```

#### B. OrderTax Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTax extends Model
{
    protected $fillable = [
        'order_id', 'tax_name', 'tax_rate', 
        'taxable_amount', 'tax_amount', 'tax_type'
    ];

    protected $casts = [
        'tax_rate' => 'decimal:4',
        'taxable_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
```

---

## Different Tax Scenarios

### 1. **Current Fixed Rate System**
```php
// config/shop.php
'tax' => [
    'rate' => 0.18,  // 18% GST for everything
    'name' => 'GST'
]
```

### 2. **Product-Specific Tax Rates**
```php
// In Product model
public function getTaxRate()
{
    $productTax = $this->taxRates()->where('is_active', true)->first();
    return $productTax ? $productTax->rate : config('shop.tax.rate');
}

// Usage in CartService
foreach ($items as $item) {
    $productTaxRate = $item->productVariation->product->getTaxRate();
    $itemTaxAmount = $item->price * $item->quantity * $productTaxRate;
    $totalTax += $itemTaxAmount;
}
```

### 3. **State-Based Tax (CGST/SGST vs IGST)**
```php
public function calculateGST($amount, $customerState, $businessState)
{
    if ($customerState === $businessState) {
        // Same state - CGST + SGST
        return [
            'cgst' => $amount * 0.09,  // 9%
            'sgst' => $amount * 0.09,  // 9%
            'total' => $amount * 0.18
        ];
    } else {
        // Different state - IGST
        return [
            'igst' => $amount * 0.18,  // 18%
            'total' => $amount * 0.18
        ];
    }
}
```

### 4. **Category-Based Tax Rates**
```php
// Different tax rates for different categories
$taxRates = [
    'electronics' => 0.18,      // 18%
    'books' => 0.05,            // 5%
    'medicines' => 0.12,        // 12%
    'food' => 0.05,             // 5%
    'luxury' => 0.28,           // 28%
];
```

---

## Implementation Steps

### Step 1: Add Tax Tables (Optional - For Advanced Features)

```bash
# Create migration
php artisan make:migration create_tax_management_tables
```

### Step 2: Update Order Model to Store Tax

```php
// Add to Order model
public function taxes()
{
    return $this->hasMany(OrderTax::class);
}

// In CheckoutController when creating order
foreach ($taxBreakdown as $tax) {
    OrderTax::create([
        'order_id' => $order->id,
        'tax_name' => $tax['name'],
        'tax_rate' => $tax['rate'],
        'taxable_amount' => $tax['taxable_amount'],
        'tax_amount' => $tax['amount'],
        'tax_type' => $tax['type']
    ]);
}
```

### Step 3: Enhanced Tax Service

```php
<?php

namespace App\Services;

class TaxService
{
    public function calculateTax($items, $customerAddress = null)
    {
        $taxes = [];
        $totalTax = 0;

        foreach ($items as $item) {
            $product = $item->productVariation->product;
            $taxRate = $this->getTaxRateForProduct($product, $customerAddress);
            
            $itemAmount = $item->price * $item->quantity;
            $itemTax = $itemAmount * $taxRate;
            
            $taxes[] = [
                'product_id' => $product->id,
                'amount' => $itemAmount,
                'rate' => $taxRate,
                'tax' => $itemTax
            ];
            
            $totalTax += $itemTax;
        }

        return [
            'breakdown' => $taxes,
            'total_tax' => $totalTax,
            'total_taxable' => collect($taxes)->sum('amount')
        ];
    }

    private function getTaxRateForProduct($product, $address = null)
    {
        // 1. Check product-specific tax rate
        $productTax = $product->taxRates()->where('is_active', true)->first();
        if ($productTax && $productTax->isEffective()) {
            return $productTax->rate;
        }

        // 2. Check category-specific tax rate
        $categoryTax = $product->category->taxRates()->where('is_active', true)->first();
        if ($categoryTax && $categoryTax->isEffective()) {
            return $categoryTax->rate;
        }

        // 3. Fall back to default tax rate
        return config('shop.tax.rate', 0.18);
    }
}
```

---

## Tax Configuration Options

### 1. **Simple Fixed Rate (Current)**
```bash
# .env
TAX_RATE=0.18
TAX_NAME=GST
TAX_ENABLED=true
```

### 2. **Different Rates by Category**
```php
// config/shop.php
'tax_rates' => [
    'default' => 0.18,
    'categories' => [
        'books' => 0.05,
        'food' => 0.05,
        'electronics' => 0.18,
        'luxury' => 0.28,
    ]
]
```

### 3. **State-Based Calculation**
```php
'tax' => [
    'business_state' => 'Gujarat',
    'cgst_rate' => 0.09,
    'sgst_rate' => 0.09,
    'igst_rate' => 0.18,
]
```

---

## Tax Display Examples

### 1. **Simple GST Display (Current)**
```
Subtotal: ₹1,000
Tax (GST): ₹180
Total: ₹1,180
```

### 2. **Detailed GST Breakdown**
```
Subtotal: ₹1,000
CGST (9%): ₹90
SGST (9%): ₹90
Total Tax: ₹180
Total: ₹1,180
```

### 3. **Product-wise Tax**
```
Product A (18%): ₹800 + ₹144 tax
Product B (5%): ₹200 + ₹10 tax
Total Tax: ₹154
Total: ₹1,154
```

---

## Tax Reporting & Compliance

### 1. **Tax Summary Report**
```php
// Monthly tax report
$monthlyTax = OrderTax::whereMonth('created_at', now()->month)
    ->selectRaw('
        SUM(taxable_amount) as total_taxable,
        SUM(tax_amount) as total_tax,
        tax_type,
        COUNT(*) as transactions
    ')
    ->groupBy('tax_type')
    ->get();
```

### 2. **GST Return Data**
```php
// GSTR-1 data preparation
$gstr1Data = Order::with('taxes', 'user')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get()
    ->map(function($order) {
        return [
            'invoice_no' => $order->id,
            'invoice_date' => $order->created_at->format('d-m-Y'),
            'customer_gstin' => $order->user->gstin ?? 'Unregistered',
            'taxable_value' => $order->taxes->sum('taxable_amount'),
            'cgst' => $order->taxes->where('tax_type', 'cgst')->sum('tax_amount'),
            'sgst' => $order->taxes->where('tax_type', 'sgst')->sum('tax_amount'),
            'igst' => $order->taxes->where('tax_type', 'igst')->sum('tax_amount'),
        ];
    });
```

---

## Testing Tax Calculations

### Run Current Test
```bash
php test_tax_calculation.php
```

### Test Different Scenarios
```php
// Test 1: No discount
$cart = ['subtotal' => 1000, 'discount' => 0];
// Expected: Tax = ₹180, Total = ₹1,180

// Test 2: 50% discount, tax after discount
$cart = ['subtotal' => 1000, 'discount' => 500];
// Expected: Tax = ₹90, Total = ₹590

// Test 3: 50% discount, tax before discount
config(['shop.tax.calculate_on' => 'before_discount']);
// Expected: Tax = ₹180, Total = ₹680
```

---

## Quick Setup for Advanced Tax

### 1. **Create Advanced Tax Migration**
```bash
php artisan make:migration create_advanced_tax_tables --create=tax_rates
```

### 2. **Add Tax Models**
```bash
php artisan make:model TaxRate
php artisan make:model OrderTax
```

### 3. **Create Tax Service**
```bash
php artisan make:service TaxService
```

### 4. **Update Frontend to Show Tax Breakdown**
```php
// In checkout view
@if($taxBreakdown)
    @foreach($taxBreakdown as $tax)
        <div class="d-flex justify-content-between">
            <span>{{ $tax['name'] }} ({{ $tax['rate'] * 100 }}%)</span>
            <span>₹{{ number_format($tax['amount'], 2) }}</span>
        </div>
    @endforeach
@endif
```

---

## Current Status Summary

| Feature | Status | Notes |
|---------|--------|-------|
| Basic Tax Calculation | ✅ Working | 18% GST applied correctly |
| Tax Configuration | ✅ Working | Via config/shop.php |
| Tax Display | ✅ Working | Shows on checkout |
| Tax Storage | ❌ Missing | No database records |
| Tax Reporting | ❌ Missing | No reports available |
| Product-specific Rates | ❌ Missing | All products use same rate |
| State-based GST | ❌ Missing | No CGST/SGST/IGST split |
| Tax Audit Trail | ❌ Missing | No historical records |

---

## Recommendations

### **For Simple Business (Current Setup is Fine)**
- Keep the current system
- Tax rate in config file
- Perfect for single tax rate scenarios

### **For Complex Business (Upgrade Needed)**
1. Create tax database tables
2. Implement product-specific rates
3. Add state-based calculations
4. Create tax reporting system
5. Add GST compliance features

### **Next Steps**
1. **Immediate**: Add tax amount to orders table for record keeping
2. **Short term**: Create basic tax reporting
3. **Long term**: Implement full GST compliance system

---

**Last Updated**: October 31, 2025  
**Tax System Version**: Basic Configuration-Based  
**Compliance Status**: Basic GST Calculation Only