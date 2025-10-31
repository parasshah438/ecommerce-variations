<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class TaxSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'tax_rate' => config('shop.tax.rate', 0.18),
            'tax_enabled' => config('shop.tax.enabled', true),
            'tax_name' => config('shop.tax.name', 'GST'),
            'tax_calculate_on' => config('shop.tax.calculate_on', 'after_discount'),
            'tax_inclusive' => config('shop.tax.inclusive', false),
        ];

        return view('admin.tax-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:1',
            'tax_enabled' => 'boolean',
            'tax_name' => 'required|string|max:50',
            'tax_calculate_on' => 'required|in:before_discount,after_discount',
            'tax_inclusive' => 'boolean',
        ]);

        try {
            // Update .env file
            $this->updateEnvFile([
                'TAX_RATE' => $request->tax_rate,
                'TAX_ENABLED' => $request->tax_enabled ? 'true' : 'false',
                'TAX_NAME' => $request->tax_name,
                'TAX_CALCULATE_ON' => $request->tax_calculate_on,
                'TAX_INCLUSIVE' => $request->tax_inclusive ? 'true' : 'false',
            ]);

            // Clear config cache
            Artisan::call('config:clear');
            Cache::forget('tax_settings');

            return redirect()->route('admin.tax-settings.index')
                           ->with('success', 'Tax settings updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Failed to update tax settings: ' . $e->getMessage());
        }
    }

    private function updateEnvFile(array $data)
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envFile, $envContent);
    }

    public function testCalculation(Request $request)
    {
        $request->validate([
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
        ]);

        $subtotal = $request->subtotal;
        $discount = $request->discount ?? 0;
        $shipping = $request->shipping ?? 0;
        $taxRate = config('shop.tax.rate', 0.18);
        $calculateOn = config('shop.tax.calculate_on', 'after_discount');

        if ($calculateOn === 'after_discount') {
            $taxableAmount = max($subtotal - $discount, 0);
            $taxAmount = $taxableAmount * $taxRate;
            $total = $subtotal + $shipping + $taxAmount - $discount;
        } else {
            $taxAmount = $subtotal * $taxRate;
            $total = $subtotal + $shipping + $taxAmount - $discount;
        }

        return response()->json([
            'subtotal' => number_format($subtotal, 2),
            'discount' => number_format($discount, 2),
            'taxable_amount' => number_format($taxableAmount ?? $subtotal, 2),
            'tax_amount' => number_format($taxAmount, 2),
            'shipping' => number_format($shipping, 2),
            'total' => number_format($total, 2),
            'tax_rate_percentage' => ($taxRate * 100) . '%',
            'calculation_method' => $calculateOn
        ]);
    }
}