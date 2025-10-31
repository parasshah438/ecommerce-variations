# 🛍️ E-Commerce Sale System Guide

## Table of Contents
1. [Overview](#overview)
2. [Sale Flow Architecture](#sale-flow-architecture)
3. [Creating Sales](#creating-sales)
4. [Discount Types Explained](#discount-types-explained)
5. [Sale Configuration](#sale-configuration)
6. [Product Assignment](#product-assignment)
7. [Frontend Display](#frontend-display)
8. [Price Calculation Flow](#price-calculation-flow)
9. [Common Use Cases](#common-use-cases)
10. [Troubleshooting](#troubleshooting)
11. [Best Practices](#best-practices)

---

## Overview

This e-commerce platform includes a comprehensive sale system similar to Amazon/Flipkart that allows you to:
- Create time-based sales (flash sales, festival sales, etc.)
- Apply different discount types (percentage, fixed amount, BOGO)
- Set minimum order values and usage limits
- Display sale prices across the entire platform
- Track sale performance and usage

---

## Sale Flow Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Admin Panel   │───▶│   Sale Engine   │───▶│  Frontend       │
│                 │    │                 │    │                 │
│ • Create Sale   │    │ • Calculate     │    │ • Show Prices   │
│ • Set Rules     │    │   Discounts     │    │ • Apply Timers  │
│ • Assign        │    │ • Check Rules   │    │ • Cart Updates  │
│   Products      │    │ • Track Usage   │    │ • Sale Pages    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Key Components:
1. **Sale Model**: Core sale logic and rules
2. **Product-Sale Relationship**: Many-to-many with custom discounts
3. **Price Calculation Engine**: Determines final sale prices
4. **Frontend Display**: Shows sale prices and timers
5. **Cart Integration**: Applies sale prices during checkout

---

## Creating Sales

### Step 1: Access Admin Panel
Navigate to: `http://your-domain.com/admin/sales`

### Step 2: Create New Sale
Click **"Create Sale"** and fill out the form:

#### Basic Information
```
Sale Name: "Weekend Flash Sale"
Description: "Get amazing discounts on selected products"
Banner Image: Upload promotional banner (optional)
```

#### Time Settings
```
Start Date: 2025-10-31 10:00 AM
End Date: 2025-11-02 11:59 PM
Status: Active ✓
```

#### Discount Configuration
```
Discount Type: [Select from dropdown]
Discount Value: [Enter amount/percentage]
Maximum Discount: [Optional cap]
Minimum Order Value: [Optional threshold]
Usage Limit: [Optional limit per customer]
```

### Step 3: Assign Products
1. Save the sale first
2. Go to "Products" tab
3. Select products to include in the sale
4. Set custom discounts if needed (overrides sale discount)

---

## Discount Types Explained

### 1. 📊 Percentage Discount
**Best for**: General sales, seasonal promotions

```yaml
Configuration:
  discount_type: "percentage"
  discount_value: 20
  max_discount: 2000  # Optional cap

Example:
  Product Price: ₹5,000
  Discount: 20% = ₹1,000
  Final Price: ₹4,000
  
  Expensive Product: ₹15,000
  Discount: 20% = ₹3,000
  Capped at: ₹2,000
  Final Price: ₹13,000
```

### 2. 💰 Fixed Amount Discount
**Best for**: Clearance sales, minimum purchase promotions

```yaml
Configuration:
  discount_type: "fixed"
  discount_value: 500
  min_order_value: 1000  # Recommended

Example:
  Product Price: ₹2,000
  Discount: ₹500
  Final Price: ₹1,500
  
  Product Price: ₹800
  Result: No discount (below minimum)
```

### 3. 🎁 Buy One Get One (BOGO)
**Best for**: Inventory clearance, bundle promotions

```yaml
Configuration:
  discount_type: "bogo"
  discount_value: 50  # 50% off on additional items
  
Example:
  Buy 2 items of ₹1,000 each
  Pay: ₹1,000 + ₹500 = ₹1,500
  Save: ₹500
```

---

## Sale Configuration

### Essential Settings

#### 🕐 Time-Based Sales
```php
Start Date: When sale becomes active
End Date: When sale automatically ends
Timezone: Asia/Kolkata (IST) - Already configured
```

#### 💡 Smart Pricing Rules
```php
// Maximum Discount Cap
max_discount: 1000    // Prevents excessive discounts
max_discount: null    // No limit (use carefully)

// Minimum Order Value
min_order_value: 500  // Customer must spend at least ₹500
min_order_value: 0    // No minimum required
```

#### 📊 Usage Controls
```php
// Usage Limit
usage_limit: 100     // Sale can be used 100 times total
usage_limit: null    // Unlimited usage

// Per-Customer Limit (future enhancement)
customer_limit: 1    // Each customer can use once
```

### Advanced Configuration

#### Category-Specific Sales
```php
applicable_categories: [1, 2, 5]  // Only apply to specific categories
applicable_brands: [3, 7]         // Only apply to specific brands
```

#### Product-Level Overrides
```php
// In sale_products pivot table
product_id: 123
custom_discount: 25  // This product gets 25% instead of sale default
```

---

## Product Assignment

### Method 1: Bulk Assignment
1. Go to Sale → Products tab
2. Select multiple products using checkboxes
3. Click "Add to Sale"
4. Set bulk discount (optional)

### Method 2: Individual Assignment
1. Go to specific product edit page
2. Scroll to "Sales" section
3. Select active sales to include product
4. Set custom discount if needed

### Method 3: Programmatic Assignment
```php
// Add product to sale
$sale = Sale::find(1);
$product = Product::find(123);

$sale->products()->attach($product->id, [
    'custom_discount' => 15  // Override sale discount
]);
```

---

## Frontend Display

### Sale Pages
```
Main Sale Page: /sales/{sale-slug}
Product Detail: Shows sale prices automatically
Product Listings: Displays sale badges and prices
Cart: Applies sale prices automatically
```

### Price Display Logic
```php
// Product shows different prices based on context:

1. Regular Price: ₹1,000 (when no sale)
2. Sale Price: ₹800 (when sale active)
3. Display: "₹800 ₹1,000 20% OFF"
```

### Countdown Timers
```javascript
// Automatic countdown timers on:
- Sale pages
- Product cards
- Product detail pages
- Cart (if sale expires soon)
```

---

## Price Calculation Flow

### 1. Product Level Calculation
```php
class Product {
    public function getBestSalePrice() {
        $activeSales = $this->activeSales();
        $bestPrice = $this->price;
        
        foreach ($activeSales as $sale) {
            $salePrice = $sale->calculateSalePrice($this->price);
            $bestPrice = min($bestPrice, $salePrice);
        }
        
        return $bestPrice;
    }
}
```

### 2. Sale Level Calculation
```php
class Sale {
    public function calculateSalePrice($originalPrice, $customDiscount = null) {
        $discount = $customDiscount ?? $this->discount_value;
        
        if ($this->type === 'percentage') {
            $discountAmount = ($originalPrice * $discount) / 100;
            
            // Apply maximum discount cap
            if ($this->max_discount) {
                $discountAmount = min($discountAmount, $this->max_discount);
            }
            
            return $originalPrice - $discountAmount;
        }
        
        if ($this->type === 'fixed') {
            return max(0, $originalPrice - $discount);
        }
        
        return $originalPrice; // BOGO handled separately
    }
}
```

### 3. Cart Integration
```php
// Cart automatically uses sale prices
class CartService {
    public function addItem($cart, $variationId, $quantity) {
        $variation = ProductVariation::find($variationId);
        $salePrice = $variation->getBestSalePrice(); // Uses sale price
        
        $cart->items()->create([
            'product_variation_id' => $variationId,
            'quantity' => $quantity,
            'price' => $salePrice  // Stored at sale price
        ]);
    }
}
```

---

## Common Use Cases

### 🔥 Flash Sale (24-48 hours)
```yaml
Name: "24-Hour Flash Sale"
Duration: 1-2 days
Discount: 25-40%
Target: High-traffic products
Settings:
  discount_type: "percentage"
  discount_value: 30
  max_discount: 3000
  min_order_value: 500
  usage_limit: null
```

### 🎉 Festival Sale (1 week)
```yaml
Name: "Diwali Mega Sale"
Duration: 7 days
Discount: 15-25%
Target: Entire catalog
Settings:
  discount_type: "percentage"
  discount_value: 20
  max_discount: 2000
  min_order_value: 1000
  usage_limit: null
```

### 💰 Clearance Sale
```yaml
Name: "End of Season Clearance"
Duration: 1 month
Discount: Fixed amounts
Target: Old inventory
Settings:
  discount_type: "fixed"
  discount_value: 1000
  max_discount: null
  min_order_value: 1500
  usage_limit: null
```

### 🛒 New Customer Sale
```yaml
Name: "Welcome Offer"
Duration: Ongoing
Discount: 15%
Target: First-time buyers
Settings:
  discount_type: "percentage"
  discount_value: 15
  max_discount: 1500
  min_order_value: 0
  usage_limit: 1  # Per customer
```

---

## Troubleshooting

### Issue 1: Sale Price Not Showing
**Problem**: Product shows regular price instead of sale price

**Solutions**:
```bash
# Check if sale is active
php artisan tinker
$sale = Sale::find(1);
echo $sale->isActive() ? 'Active' : 'Inactive';

# Check if product is assigned to sale
$product = Product::find(123);
echo $product->activeSales()->count();

# Clear caches
php artisan cache:clear
php artisan view:clear
```

### Issue 2: Incorrect Discount Amount
**Problem**: Discount is too small (like ₹10 instead of ₹700)

**Solution**: Check Maximum Discount setting
```php
// In admin panel, increase max_discount
max_discount: 1000  // Instead of 10
// OR
max_discount: null  // Remove cap entirely
```

### Issue 3: Sale Not Visible on Frontend
**Problem**: Sale page shows "No Active Sales"

**Solutions**:
```php
// Check timezone settings (should be Asia/Kolkata)
// Check if sale dates are in IST
// Verify sale status is 'Active'
// Check if sale has products assigned
```

### Issue 4: Cart Shows Wrong Price
**Problem**: Cart shows different price than product page

**Solution**: Clear cart and re-add products
```php
// Cart stores prices when items are added
// If sale changed after adding to cart, prices won't update
// Customer needs to remove and re-add items
```

---

## Best Practices

### 1. 🎯 Planning Sales
```
✅ Plan sale timing carefully (avoid conflicts)
✅ Set realistic discount percentages (10-30% typically)
✅ Use maximum discount caps for expensive items
✅ Set minimum order values to maintain profitability
✅ Test thoroughly before launching
```

### 2. 📊 Monitoring Performance
```
✅ Track sale usage and conversion rates
✅ Monitor impact on profit margins
✅ Analyze customer behavior during sales
✅ Adjust parameters based on performance
```

### 3. 🔄 Sale Lifecycle
```
Pre-Sale:
- Create teaser campaigns
- Prepare inventory
- Test all functionality

During Sale:
- Monitor performance
- Handle customer queries
- Ensure system stability

Post-Sale:
- Analyze results
- Collect feedback
- Plan improvements
```

### 4. 🛡️ Risk Management
```
✅ Set usage limits for flash sales
✅ Monitor for abuse (same customer multiple accounts)
✅ Have fallback plans for technical issues
✅ Maintain minimum profit margins
```

### 5. 📱 User Experience
```
✅ Clear sale messaging
✅ Prominent countdown timers
✅ Easy-to-understand pricing
✅ Mobile-friendly design
✅ Fast loading times
```

---

## Quick Reference Commands

### Creating a Sale Programmatically
```php
Sale::create([
    'name' => 'Weekend Flash Sale',
    'slug' => 'weekend-flash-sale',
    'description' => 'Amazing weekend deals',
    'type' => 'percentage',
    'discount_value' => 20,
    'max_discount' => 2000,
    'min_order_value' => 500,
    'start_date' => now(),
    'end_date' => now()->addDays(2),
    'is_active' => true
]);
```

### Adding Products to Sale
```php
$sale = Sale::find(1);
$sale->products()->attach([1, 2, 3]); // Add products 1, 2, 3
```

### Checking Sale Status
```php
$product = Product::find(1);
echo "Regular Price: ₹" . $product->price;
echo "Sale Price: ₹" . $product->getBestSalePrice();
echo "Has Sale: " . ($product->hasActiveSale() ? 'Yes' : 'No');
```

### Clearing Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Support

For technical issues or questions about the sale system:

1. Check this documentation first
2. Test in development environment
3. Review error logs in `storage/logs/`
4. Verify database entries in `sales` and `sale_products` tables

---

**Last Updated**: October 31, 2025  
**Version**: 1.0  
**Compatibility**: Laravel 8.x+