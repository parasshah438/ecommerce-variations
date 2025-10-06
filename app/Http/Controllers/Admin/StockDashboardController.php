<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\VariationStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockDashboardController extends Controller
{
    public function index()
    {
        // Get stock statistics
        $totalVariations = ProductVariation::count();
        $outOfStock = VariationStock::where('quantity', '<=', 0)->count();
        $lowStock = VariationStock::whereBetween('quantity', [1, 10])->count();
        $inStock = VariationStock::where('quantity', '>', 10)->count();
        
        // Get recent stock movements (you may need to create this table)
        // $recentMovements = StockMovement::latest()->limit(10)->get();
        
        // Get low stock products
        $lowStockProducts = ProductVariation::with(['product', 'stock'])
            ->whereHas('stock', function ($query) {
                $query->whereBetween('quantity', [1, 10]);
            })
            ->limit(10)
            ->get();
            
        // Get out of stock products
        $outOfStockProducts = ProductVariation::with(['product', 'stock'])
            ->whereHas('stock', function ($query) {
                $query->where('quantity', '<=', 0);
            })
            ->limit(10)
            ->get();
            
        // Get top selling variations (you may need to implement this based on your order system)
        $topSellingVariations = ProductVariation::with(['product', 'stock'])
            ->limit(10)
            ->get();

        return view('admin.stock.dashboard', compact(
            'totalVariations',
            'outOfStock',
            'lowStock',
            'inStock',
            'lowStockProducts',
            'outOfStockProducts',
            'topSellingVariations'
        ));
    }

    public function lowStockReport()
    {
        $lowStockProducts = ProductVariation::with(['product', 'stock'])
            ->whereHas('stock', function ($query) {
                $query->whereBetween('quantity', [1, 10]);
            })
            ->paginate(20);

        return view('admin.stock.low-stock-report', compact('lowStockProducts'));
    }

    public function outOfStockReport()
    {
        $outOfStockProducts = ProductVariation::with(['product', 'stock'])
            ->whereHas('stock', function ($query) {
                $query->where('quantity', '<=', 0);
            })
            ->paginate(20);

        return view('admin.stock.out-of-stock-report', compact('outOfStockProducts'));
    }

    public function stockMovementReport()
    {
        // This would require a stock_movements table
        // For now, return basic variation data
        $variations = ProductVariation::with(['product', 'stock'])
            ->paginate(20);

        return view('admin.stock.movement-report', compact('variations'));
    }

    public function bulkStockUpdate(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.variation_id' => 'required|exists:product_variations,id',
            'updates.*.quantity' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->updates as $update) {
                VariationStock::updateOrCreate(
                    ['product_variation_id' => $update['variation_id']],
                    [
                        'quantity' => $update['quantity'],
                        'in_stock' => $update['quantity'] > 0,
                        'updated_at' => now()
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully for ' . count($request->updates) . ' variations.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStockAlerts()
    {
        $alerts = [];
        
        // Out of stock alerts
        $outOfStock = VariationStock::with('variation.product')
            ->where('quantity', '<=', 0)
            ->get();
            
        foreach ($outOfStock as $stock) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Out of Stock',
                'message' => $stock->variation->product->name . ' (' . $stock->variation->sku . ') is out of stock',
                'variation_id' => $stock->product_variation_id,
                'created_at' => $stock->updated_at
            ];
        }
        
        // Low stock alerts
        $lowStock = VariationStock::with('variation.product')
            ->whereBetween('quantity', [1, 10])
            ->get();
            
        foreach ($lowStock as $stock) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Stock',
                'message' => $stock->variation->product->name . ' (' . $stock->variation->sku . ') has only ' . $stock->quantity . ' items left',
                'variation_id' => $stock->product_variation_id,
                'quantity' => $stock->quantity,
                'created_at' => $stock->updated_at
            ];
        }

        return response()->json(['alerts' => $alerts]);
    }

    public function updateStock(Request $request, ProductVariation $variation)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        try {
            VariationStock::updateOrCreate(
                ['product_variation_id' => $variation->id],
                [
                    'quantity' => $request->quantity,
                    'in_stock' => $request->quantity > 0,
                    'updated_at' => now()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'new_quantity' => $request->quantity
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportStock()
    {
        $variations = ProductVariation::with(['product', 'stock'])->get();
        
        $csvData = [];
        $csvData[] = ['Product Name', 'SKU', 'Variation', 'Current Stock', 'Status', 'Last Updated'];
        
        foreach ($variations as $variation) {
            $attributes = collect($variation->attributeValues())->map(function ($attr) {
                return $attr->attribute->name . ': ' . $attr->value;
            })->implode(', ');
            
            $stock = $variation->stock;
            $quantity = $stock ? $stock->quantity : 0;
            $status = $quantity > 10 ? 'In Stock' : ($quantity > 0 ? 'Low Stock' : 'Out of Stock');
            
            $csvData[] = [
                $variation->product->name,
                $variation->sku,
                $attributes,
                $quantity,
                $status,
                $stock ? $stock->updated_at->format('Y-m-d H:i:s') : 'N/A'
            ];
        }
        
        $filename = 'stock-report-' . date('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}