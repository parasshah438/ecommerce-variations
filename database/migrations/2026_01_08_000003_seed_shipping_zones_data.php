<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\ShippingZone;
use App\Models\ShippingRate;

return new class extends Migration
{
    public function up(): void
    {
        $this->seedShippingZones();
    }

    public function down(): void
    {
        ShippingRate::truncate();
        ShippingZone::truncate();
    }

    private function seedShippingZones()
    {
        // Local Zone (Same City)
        $localZone = ShippingZone::create([
            'name' => 'Local (Same City)',
            'type' => 'domestic',
            'pincodes' => ['400001', '400002', '400003', '400004', '400005'], // Mumbai example
            'description' => 'Same city delivery',
            'active' => true
        ]);

        $this->createShippingRates($localZone->id, [
            ['min_weight' => 0, 'max_weight' => 500, 'base_rate' => 30, 'additional_rate' => 0],
            ['min_weight' => 501, 'max_weight' => 1000, 'base_rate' => 40, 'additional_rate' => 0],
            ['min_weight' => 1001, 'max_weight' => 2000, 'base_rate' => 60, 'additional_rate' => 0],
            ['min_weight' => 2001, 'max_weight' => 5000, 'base_rate' => 80, 'additional_rate' => 0],
            ['min_weight' => 5001, 'max_weight' => null, 'base_rate' => 120, 'additional_rate' => 15]
        ]);

        // Regional Zone (Same State)
        $regionalZone = ShippingZone::create([
            'name' => 'Regional (Same State)',
            'type' => 'domestic',
            'pincodes' => ['400', '401', '402', '403', '404'], // Maharashtra first 3 digits
            'description' => 'Same state delivery',
            'active' => true
        ]);

        $this->createShippingRates($regionalZone->id, [
            ['min_weight' => 0, 'max_weight' => 500, 'base_rate' => 50, 'additional_rate' => 0],
            ['min_weight' => 501, 'max_weight' => 1000, 'base_rate' => 70, 'additional_rate' => 0],
            ['min_weight' => 1001, 'max_weight' => 2000, 'base_rate' => 90, 'additional_rate' => 0],
            ['min_weight' => 2001, 'max_weight' => 5000, 'base_rate' => 130, 'additional_rate' => 0],
            ['min_weight' => 5001, 'max_weight' => null, 'base_rate' => 180, 'additional_rate' => 20]
        ]);

        // National Zone (Other States)
        $nationalZone = ShippingZone::create([
            'name' => 'National (Other States)',
            'type' => 'domestic',
            'pincodes' => ['110', '560', '600', '700', '500'], // Delhi, Bangalore, Chennai, Kolkata, Hyderabad
            'description' => 'Other states delivery',
            'active' => true
        ]);

        $this->createShippingRates($nationalZone->id, [
            ['min_weight' => 0, 'max_weight' => 500, 'base_rate' => 70, 'additional_rate' => 0],
            ['min_weight' => 501, 'max_weight' => 1000, 'base_rate' => 95, 'additional_rate' => 0],
            ['min_weight' => 1001, 'max_weight' => 2000, 'base_rate' => 125, 'additional_rate' => 0],
            ['min_weight' => 2001, 'max_weight' => 5000, 'base_rate' => 170, 'additional_rate' => 0],
            ['min_weight' => 5001, 'max_weight' => null, 'base_rate' => 250, 'additional_rate' => 25]
        ]);

        // Remote Zone (North East & Islands)
        $remoteZone = ShippingZone::create([
            'name' => 'Remote (North East & Islands)',
            'type' => 'domestic',
            'pincodes' => ['790', '791', '792', '793', '794', '744'], // NE states and A&N Islands
            'description' => 'Remote areas delivery',
            'active' => true
        ]);

        $this->createShippingRates($remoteZone->id, [
            ['min_weight' => 0, 'max_weight' => 500, 'base_rate' => 100, 'additional_rate' => 0],
            ['min_weight' => 501, 'max_weight' => 1000, 'base_rate' => 140, 'additional_rate' => 0],
            ['min_weight' => 1001, 'max_weight' => 2000, 'base_rate' => 180, 'additional_rate' => 0],
            ['min_weight' => 2001, 'max_weight' => 5000, 'base_rate' => 240, 'additional_rate' => 0],
            ['min_weight' => 5001, 'max_weight' => null, 'base_rate' => 350, 'additional_rate' => 35]
        ]);

        // Default Zone (Fallback)
        $defaultZone = ShippingZone::create([
            'name' => 'Default',
            'type' => 'domestic',
            'pincodes' => ['*'], // Wildcard for any unmatched pincode
            'description' => 'Default shipping rates',
            'active' => true
        ]);

        $this->createShippingRates($defaultZone->id, [
            ['min_weight' => 0, 'max_weight' => 1000, 'base_rate' => 50, 'additional_rate' => 0],
            ['min_weight' => 1001, 'max_weight' => null, 'base_rate' => 80, 'additional_rate' => 25]
        ]);
    }

    private function createShippingRates($zoneId, $rates)
    {
        foreach ($rates as $rate) {
            ShippingRate::create([
                'zone_id' => $zoneId,
                'min_weight' => $rate['min_weight'],
                'max_weight' => $rate['max_weight'],
                'base_rate' => $rate['base_rate'],
                'additional_rate' => $rate['additional_rate'],
                'active' => true
            ]);
        }
    }
};