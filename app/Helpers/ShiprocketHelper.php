<?php

namespace App\Helpers;

/**
 * Shiprocket Data Helper
 * Helper class for transforming data to Shiprocket format
 */
class ShiprocketHelper
{
    /**
     * Transform Laravel order to Shiprocket order format
     */
    public static function transformOrder($order): array
    {
        return [
            'order_id' => $order->order_number ?? $order->id,
            'order_date' => $order->created_at->format('Y-m-d H:i'),
            'pickup_location' => $order->pickup_location ?? config('app.default_pickup_location'),
            'channel_id' => $order->channel_id ?? '',
            'comment' => $order->notes ?? '',
            
            // Billing details
            'billing_customer_name' => $order->billing_first_name ?? $order->customer_name,
            'billing_last_name' => $order->billing_last_name ?? '',
            'billing_address' => $order->billing_address ?? $order->billing_address_1,
            'billing_address_2' => $order->billing_address_2 ?? '',
            'billing_city' => $order->billing_city,
            'billing_pincode' => $order->billing_pincode,
            'billing_state' => $order->billing_state,
            'billing_country' => $order->billing_country ?? 'India',
            'billing_email' => $order->billing_email ?? $order->customer_email,
            'billing_phone' => $order->billing_phone ?? $order->customer_phone,
            
            // Shipping details
            'shipping_is_billing' => $order->shipping_is_billing ?? true,
            'shipping_customer_name' => $order->shipping_first_name ?? $order->billing_first_name ?? $order->customer_name,
            'shipping_last_name' => $order->shipping_last_name ?? $order->billing_last_name ?? '',
            'shipping_address' => $order->shipping_address ?? $order->shipping_address_1 ?? $order->billing_address,
            'shipping_address_2' => $order->shipping_address_2 ?? $order->billing_address_2 ?? '',
            'shipping_city' => $order->shipping_city ?? $order->billing_city,
            'shipping_pincode' => $order->shipping_pincode ?? $order->billing_pincode,
            'shipping_country' => $order->shipping_country ?? $order->billing_country ?? 'India',
            'shipping_state' => $order->shipping_state ?? $order->billing_state,
            'shipping_email' => $order->shipping_email ?? $order->billing_email ?? $order->customer_email,
            'shipping_phone' => $order->shipping_phone ?? $order->billing_phone ?? $order->customer_phone,
            
            // Order items
            'order_items' => self::transformOrderItems($order->items ?? $order->orderItems ?? []),
            
            // Payment and totals
            'payment_method' => self::transformPaymentMethod($order->payment_method),
            'shipping_charges' => $order->shipping_charges ?? 0,
            'giftwrap_charges' => $order->giftwrap_charges ?? 0,
            'transaction_charges' => $order->transaction_charges ?? 0,
            'total_discount' => $order->discount_amount ?? 0,
            'sub_total' => $order->subtotal ?? $order->total,
            
            // Package dimensions
            'length' => $order->length ?? config('shiprocket.default_dimensions.length', 10),
            'breadth' => $order->breadth ?? config('shiprocket.default_dimensions.breadth', 10),
            'height' => $order->height ?? config('shiprocket.default_dimensions.height', 10),
            'weight' => $order->weight ?? config('shiprocket.default_weight', 0.5),
        ];
    }

    /**
     * Transform order items to Shiprocket format
     */
    public static function transformOrderItems($items): array
    {
        $shiprocketItems = [];

        foreach ($items as $item) {
            $shiprocketItems[] = [
                'name' => $item->product_name ?? $item->name,
                'sku' => $item->sku ?? $item->product_sku ?? $item->id,
                'units' => $item->quantity ?? 1,
                'selling_price' => $item->price ?? $item->unit_price,
                'discount' => $item->discount ?? 0,
                'tax' => $item->tax ?? 0,
                'hsn' => $item->hsn_code ?? $item->hsn ?? null,
            ];
        }

        return $shiprocketItems;
    }

    /**
     * Transform payment method to Shiprocket format
     */
    public static function transformPaymentMethod(?string $paymentMethod): string
    {
        if (!$paymentMethod) {
            return 'Prepaid';
        }

        $method = strtolower($paymentMethod);
        
        if (in_array($method, ['cod', 'cash_on_delivery', 'cash on delivery'])) {
            return 'COD';
        }

        return 'Prepaid';
    }

    /**
     * Transform Laravel address to Shiprocket format
     */
    public static function transformAddress($address, string $type = 'shipping'): array
    {
        $prefix = $type === 'billing' ? 'billing' : 'shipping';
        
        return [
            "{$prefix}_customer_name" => $address->first_name ?? $address->name,
            "{$prefix}_last_name" => $address->last_name ?? '',
            "{$prefix}_address" => $address->address ?? $address->address_1,
            "{$prefix}_address_2" => $address->address_2 ?? '',
            "{$prefix}_city" => $address->city,
            "{$prefix}_pincode" => $address->pincode ?? $address->postal_code,
            "{$prefix}_state" => $address->state,
            "{$prefix}_country" => $address->country ?? 'India',
            "{$prefix}_email" => $address->email,
            "{$prefix}_phone" => $address->phone,
        ];
    }

    /**
     * Transform return request data
     */
    public static function transformReturnRequest($returnRequest): array
    {
        return [
            'order_id' => $returnRequest['return_order_id'],
            'order_date' => $returnRequest['return_date'] ?? now()->format('Y-m-d'),
            'channel_id' => $returnRequest['channel_id'] ?? '',
            
            // Pickup details (customer)
            'pickup_customer_name' => $returnRequest['customer_name'],
            'pickup_last_name' => $returnRequest['customer_last_name'] ?? '',
            'company_name' => $returnRequest['company_name'] ?? '',
            'pickup_address' => $returnRequest['pickup_address'],
            'pickup_address_2' => $returnRequest['pickup_address_2'] ?? '',
            'pickup_city' => $returnRequest['pickup_city'],
            'pickup_state' => $returnRequest['pickup_state'],
            'pickup_country' => $returnRequest['pickup_country'] ?? 'India',
            'pickup_pincode' => $returnRequest['pickup_pincode'],
            'pickup_email' => $returnRequest['pickup_email'],
            'pickup_phone' => $returnRequest['pickup_phone'],
            'pickup_isd_code' => $returnRequest['pickup_isd_code'] ?? '91',
            
            // Shipping details (return destination)
            'shipping_customer_name' => $returnRequest['return_to_name'],
            'shipping_last_name' => $returnRequest['return_to_last_name'] ?? '',
            'shipping_address' => $returnRequest['return_to_address'],
            'shipping_address_2' => $returnRequest['return_to_address_2'] ?? '',
            'shipping_city' => $returnRequest['return_to_city'],
            'shipping_country' => $returnRequest['return_to_country'] ?? 'India',
            'shipping_pincode' => $returnRequest['return_to_pincode'],
            'shipping_state' => $returnRequest['return_to_state'],
            'shipping_email' => $returnRequest['return_to_email'],
            'shipping_isd_code' => $returnRequest['return_to_isd_code'] ?? '91',
            'shipping_phone' => $returnRequest['return_to_phone'],
            
            // Return items
            'order_items' => self::transformReturnItems($returnRequest['items']),
            
            // Other details
            'payment_method' => 'PREPAID',
            'total_discount' => 0,
            'sub_total' => $returnRequest['sub_total'],
            'length' => $returnRequest['length'] ?? config('shiprocket.default_dimensions.length', 10),
            'breadth' => $returnRequest['breadth'] ?? config('shiprocket.default_dimensions.breadth', 10),
            'height' => $returnRequest['height'] ?? config('shiprocket.default_dimensions.height', 10),
            'weight' => $returnRequest['weight'] ?? config('shiprocket.default_weight', 0.5),
        ];
    }

    /**
     * Transform return items
     */
    public static function transformReturnItems($items): array
    {
        $returnItems = [];

        foreach ($items as $item) {
            $returnItem = [
                'name' => $item['name'],
                'sku' => $item['sku'],
                'units' => $item['quantity'] ?? 1,
                'selling_price' => $item['price'],
                'discount' => 0,
                'qc_brand' => $item['brand'] ?? '',
            ];

            // Add QC (Quality Check) fields if enabled
            if (isset($item['qc_enable']) && $item['qc_enable']) {
                $returnItem['qc_enable'] = true;
                $returnItem['qc_product_name'] = $item['qc_product_name'] ?? $item['name'];
                $returnItem['qc_product_image'] = $item['qc_product_image'] ?? '';
                $returnItem['qc_brand'] = $item['qc_brand'] ?? $item['brand'] ?? '';
            }

            $returnItems[] = $returnItem;
        }

        return $returnItems;
    }

    /**
     * Calculate package dimensions from items
     */
    public static function calculatePackageDimensions($items): array
    {
        $totalLength = 0;
        $totalBreadth = 0;
        $totalHeight = 0;
        $totalWeight = 0;

        foreach ($items as $item) {
            $quantity = $item->quantity ?? 1;
            $totalLength = max($totalLength, ($item->length ?? 0));
            $totalBreadth = max($totalBreadth, ($item->breadth ?? 0));
            $totalHeight += ($item->height ?? 0) * $quantity;
            $totalWeight += ($item->weight ?? 0) * $quantity;
        }

        return [
            'length' => max($totalLength, config('shiprocket.default_dimensions.length', 10)),
            'breadth' => max($totalBreadth, config('shiprocket.default_dimensions.breadth', 10)),
            'height' => max($totalHeight, config('shiprocket.default_dimensions.height', 10)),
            'weight' => max($totalWeight, config('shiprocket.default_weight', 0.5)),
        ];
    }

    /**
     * Format phone number for Shiprocket
     */
    public static function formatPhoneNumber(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Remove country code if present
        if (substr($phone, 0, 2) === '91' && strlen($phone) === 12) {
            $phone = substr($phone, 2);
        }

        return $phone;
    }

    /**
     * Validate Indian pincode
     */
    public static function validatePincode(string $pincode): bool
    {
        return preg_match('/^[1-9][0-9]{5}$/', $pincode);
    }

    /**
     * Get state code from state name
     */
    public static function getStateCode(string $stateName): string
    {
        $states = [
            'andhra pradesh' => 'AP',
            'arunachal pradesh' => 'AR',
            'assam' => 'AS',
            'bihar' => 'BR',
            'chhattisgarh' => 'CG',
            'goa' => 'GA',
            'gujarat' => 'GJ',
            'haryana' => 'HR',
            'himachal pradesh' => 'HP',
            'jharkhand' => 'JH',
            'karnataka' => 'KA',
            'kerala' => 'KL',
            'madhya pradesh' => 'MP',
            'maharashtra' => 'MH',
            'manipur' => 'MN',
            'meghalaya' => 'ML',
            'mizoram' => 'MZ',
            'nagaland' => 'NL',
            'odisha' => 'OR',
            'punjab' => 'PB',
            'rajasthan' => 'RJ',
            'sikkim' => 'SK',
            'tamil nadu' => 'TN',
            'telangana' => 'TG',
            'tripura' => 'TR',
            'uttar pradesh' => 'UP',
            'uttarakhand' => 'UT',
            'west bengal' => 'WB',
            'delhi' => 'DL',
            'jammu and kashmir' => 'JK',
            'ladakh' => 'LA',
        ];

        return $states[strtolower($stateName)] ?? $stateName;
    }

    /**
     * Generate tracking URL
     */
    public static function getTrackingUrl(string $awbCode): string
    {
        return "https://shiprocket.co/tracking/{$awbCode}";
    }
}