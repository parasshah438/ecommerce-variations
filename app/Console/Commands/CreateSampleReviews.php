<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\User;
use App\Models\Review;

class CreateSampleReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:sample-reviews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample reviews for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get first product and multiple users
        $product = Product::first();
        $users = User::take(5)->get();

        if (!$product) {
            $this->error('No products found. Please create a product first.');
            return;
        }

        if ($users->count() < 5) {
            $this->error('Need at least 5 users. Found only ' . $users->count());
            return;
        }

        // Clear existing reviews for this product
        Review::where('product_id', $product->id)->delete();

        // Create some sample reviews
        $reviews = [
            [
                'rating' => 5,
                'title' => 'Excellent Product!',
                'comment' => 'Really love this product. High quality and fast delivery. Highly recommend!'
            ],
            [
                'rating' => 4,
                'title' => 'Very Good',
                'comment' => 'Good quality product. Arrived on time. Would buy again.'
            ],
            [
                'rating' => 5,
                'title' => 'Amazing Quality',
                'comment' => 'Outstanding quality and craftsmanship. Exceeded my expectations!'
            ],
            [
                'rating' => 3,
                'title' => 'Average',
                'comment' => 'Product is okay. Could be better for the price.'
            ],
            [
                'rating' => 5,
                'title' => 'Perfect!',
                'comment' => 'Exactly what I was looking for. Perfect fit and finish.'
            ]
        ];

        $this->info("Creating sample reviews for product: {$product->name}");

        foreach ($reviews as $index => $reviewData) {
            $review = Review::create([
                'product_id' => $product->id,
                'user_id' => $users[$index]->id,
                'rating' => $reviewData['rating'],
                'title' => $reviewData['title'],
                'comment' => $reviewData['comment']
            ]);
            
            $this->line("Created review: {$review->title} (Rating: {$review->rating}) by User ID: {$review->user_id}");
        }

        // Check updated product stats
        $product->refresh();
        $this->info("\nProduct statistics updated:");
        $this->line("Reviews Count: {$product->reviews_count}");
        $this->line("Average Rating: {$product->average_rating}");

        $this->info('Sample reviews created successfully!');
    }
}
