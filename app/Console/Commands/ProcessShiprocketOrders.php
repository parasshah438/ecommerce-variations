<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\ShiprocketOrderProcessor;
use Illuminate\Console\Command;

class ProcessShiprocketOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shiprocket:process-orders 
                            {--order-id= : Process specific order ID}
                            {--auto-assign : Auto assign couriers}
                            {--dry-run : Show what would be processed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process confirmed orders for Shiprocket shipping';

    protected $shiprocketProcessor;

    /**
     * Create a new command instance.
     */
    public function __construct(ShiprocketOrderProcessor $shiprocketProcessor)
    {
        parent::__construct();
        $this->shiprocketProcessor = $shiprocketProcessor;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->option('order-id');
        $autoAssign = $this->option('auto-assign');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        if ($orderId) {
            // Process specific order
            $order = Order::find($orderId);
            if (!$order) {
                $this->error("Order #{$orderId} not found");
                return 1;
            }

            $this->processOrder($order, $autoAssign, $dryRun);
        } else {
            // Process all eligible orders
            $this->processEligibleOrders($autoAssign, $dryRun);
        }

        return 0;
    }

    /**
     * Process all eligible orders
     */
    protected function processEligibleOrders(bool $autoAssign, bool $dryRun): void
    {
        // Find confirmed orders without shipments
        $orders = Order::where('status', Order::STATUS_CONFIRMED)
            ->where('payment_status', Order::PAYMENT_PAID)
            ->whereDoesntHave('shipments', function ($query) {
                $query->where('status', '!=', 'cancelled');
            })
            ->with(['user', 'address', 'items'])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('✅ No eligible orders found for processing');
            return;
        }

        $this->info("📦 Found {$orders->count()} eligible orders for processing");

        $progressBar = $this->output->createProgressBar($orders->count());
        $progressBar->start();

        $successful = 0;
        $failed = 0;

        foreach ($orders as $order) {
            $result = $this->processOrder($order, $autoAssign, $dryRun, false);
            
            if ($result) {
                $successful++;
            } else {
                $failed++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Processing completed:");
        $this->info("   - Successful: {$successful}");
        if ($failed > 0) {
            $this->error("   - Failed: {$failed}");
        }
    }

    /**
     * Process individual order
     */
    protected function processOrder(Order $order, bool $autoAssign, bool $dryRun, bool $showDetails = true): bool
    {
        if ($showDetails) {
            $this->info("🔄 Processing Order #{$order->id}");
        }

        if ($dryRun) {
            $this->line("   Would process: Order #{$order->id} - {$order->user->name} - ₹{$order->total}");
            return true;
        }

        try {
            // Process for shipping
            $result = $this->shiprocketProcessor->processConfirmedOrder($order);

            if ($result['success']) {
                if ($showDetails) {
                    $this->info("   ✅ Shipment created successfully");
                    $this->info("   📋 Shiprocket Order ID: " . ($result['shiprocket_order_id'] ?? 'N/A'));
                }

                // Auto-assign courier if requested
                if ($autoAssign) {
                    $courierResult = $this->shiprocketProcessor->assignBestCourier($order);
                    
                    if ($courierResult['success'] && $showDetails) {
                        $this->info("   🚚 Courier assigned: " . $courierResult['courier']['courier_name']);
                    } elseif (!$courierResult['success'] && $showDetails) {
                        $this->warn("   ⚠️  Courier assignment failed: " . $courierResult['error']);
                    }
                }

                return true;
            } else {
                if ($showDetails) {
                    $this->error("   ❌ Processing failed: " . $result['message']);
                }
                return false;
            }

        } catch (\Exception $e) {
            if ($showDetails) {
                $this->error("   ❌ Exception: " . $e->getMessage());
            }
            return false;
        }
    }
}