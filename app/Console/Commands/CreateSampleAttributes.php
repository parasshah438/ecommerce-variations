<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attribute;
use App\Models\AttributeValue;

class CreateSampleAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:sample-attributes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample attributes and values for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating sample attributes and values...');

        // Create Color attribute
        $colorAttr = Attribute::firstOrCreate([
            'name' => 'Color',
        ], [
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'color',
            'is_required' => true,
            'is_filterable' => true
        ]);

        $colors = [
            ['value' => 'Red', 'hex_color' => '#FF0000'],
            ['value' => 'Blue', 'hex_color' => '#0000FF'],
            ['value' => 'Green', 'hex_color' => '#008000'],
            ['value' => 'Black', 'hex_color' => '#000000'],
            ['value' => 'White', 'hex_color' => '#FFFFFF'],
        ];

        foreach ($colors as $color) {
            AttributeValue::firstOrCreate([
                'attribute_id' => $colorAttr->id,
                'value' => $color['value'],
                'hex_color' => $color['hex_color']
            ]);
        }

        // Create Size attribute
        $sizeAttr = Attribute::firstOrCreate([
            'name' => 'Size',
        ], [
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'size',
            'is_required' => true,
            'is_filterable' => true
        ]);

        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        foreach ($sizes as $size) {
            AttributeValue::firstOrCreate([
                'attribute_id' => $sizeAttr->id,
                'value' => $size,
                'is_default' => $size === 'M'
            ]);
        }

        // Create Material attribute
        $materialAttr = Attribute::firstOrCreate([
            'name' => 'Material',
        ], [
            'name' => 'Material',
            'slug' => 'material',
            'type' => 'text',
            'is_required' => false,
            'is_filterable' => true
        ]);

        $materials = ['Cotton', 'Polyester', 'Silk', 'Wool', 'Leather', 'Denim'];
        foreach ($materials as $material) {
            AttributeValue::firstOrCreate([
                'attribute_id' => $materialAttr->id,
                'value' => $material
            ]);
        }

        // Create Storage attribute
        $storageAttr = Attribute::firstOrCreate([
            'name' => 'Storage',
        ], [
            'name' => 'Storage',
            'slug' => 'storage',
            'type' => 'number',
            'is_required' => false,
            'is_filterable' => true
        ]);

        $storages = ['32GB', '64GB', '128GB', '256GB', '512GB', '1TB'];
        foreach ($storages as $storage) {
            AttributeValue::firstOrCreate([
                'attribute_id' => $storageAttr->id,
                'value' => $storage
            ]);
        }

        $this->info('Sample attributes and values created successfully!');
        $this->line('Created attributes:');
        $this->line('- Color (5 values)');
        $this->line('- Size (6 values)'); 
        $this->line('- Material (6 values)');
        $this->line('- Storage (6 values)');
    }
}
