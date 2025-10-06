<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->bootstrap();

use App\Models\Product;
use App\Models\User;
use App\Models\Review;

// Get first product and user
$product = Product::first();
$user = User::first();

if (!$product) {
    echo "No products found. Please create a product first.\n";
    exit;
}

if (!$user) {
    echo "No users found. Please create a user first.\n";
    exit;
}

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

echo "Creating sample reviews for product: {$product->name}\n";

foreach ($reviews as $reviewData) {
    $review = Review::create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'rating' => $reviewData['rating'],
        'title' => $reviewData['title'],
        'comment' => $reviewData['comment']
    ]);
    
    echo "Created review: {$review->title} (Rating: {$review->rating})\n";
}

// Check updated product stats
$product->refresh();
echo "\nProduct statistics updated:\n";
echo "Reviews Count: {$product->reviews_count}\n";
echo "Average Rating: {$product->average_rating}\n";

echo "\nSample reviews created successfully!\n";