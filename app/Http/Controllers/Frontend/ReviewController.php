<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request, Product $product)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to write a review.'
            ], 401);
        }

        // Check if user already reviewed this product
        $existingReview = Review::where('product_id', $product->id)
                               ->where('user_id', Auth::id())
                               ->first();

        if ($existingReview) {
            // Update existing review instead of creating new one
            return $this->updateExistingReview($request, $existingReview, $product);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create the review
            $review = Review::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'verified_purchase' => $this->checkVerifiedPurchase($product->id, Auth::id()),
                'is_approved' => true // Auto-approve for now, you can add moderation later
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully!',
                'review' => [
                    'id' => $review->id,
                    'user_id' => $review->user_id,
                    'rating' => $review->rating,
                    'title' => $review->title,
                    'comment' => $review->comment,
                    'user_name' => Auth::user()->name,
                    'created_at' => $review->created_at->format('M j, Y'),
                    'verified_purchase' => $review->verified_purchase
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review. Please try again.'
            ], 500);
        }
    }

    /**
     * Get reviews for a specific product
     */
    public function index(Request $request, Product $product)
    {
        $perPage = $request->get('per_page', 10);
        
        $reviews = Review::where('product_id', $product->id)
                        ->where('is_approved', true)
                        ->with('user:id,name')
                        ->latest()
                        ->paginate($perPage);

        $reviewsData = $reviews->map(function($review) {
            return [
                'id' => $review->id,
                'user_id' => $review->user_id,
                'rating' => $review->rating,
                'title' => $review->title,
                'comment' => $review->comment,
                'user_name' => $review->user ? $review->user->name : 'Anonymous',
                'created_at' => $review->created_at->format('M j, Y'),
                'verified_purchase' => $review->verified_purchase
            ];
        });

        return response()->json([
            'success' => true,
            'reviews' => $reviewsData,
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage()
            ]
        ]);
    }

    /**
     * Check if user has purchased this product (simplified logic)
     */
    private function checkVerifiedPurchase($productId, $userId)
    {
        // Check if user has any completed orders with this product
        // Since order items reference product_variation_id, we need to check through variations
        return \App\Models\Order::whereHas('items.productVariation', function($query) use ($productId) {
            $query->where('product_id', $productId);
        })->where('user_id', $userId)
          ->where('status', 'delivered') // Only count delivered orders
          ->exists();
    }

    /**
     * Get review statistics for a product
     */
    public function statistics(Product $product)
    {
        $reviews = Review::where('product_id', $product->id)
                        ->where('is_approved', true);

        $totalReviews = $reviews->count();
        $averageRating = $totalReviews > 0 ? $reviews->avg('rating') : 0;

        // Rating breakdown
        $ratingBreakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $reviews->where('rating', $i)->count();
            $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
            $ratingBreakdown[$i] = [
                'count' => $count,
                'percentage' => $percentage
            ];
        }

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_reviews' => $totalReviews,
                'average_rating' => round($averageRating, 1),
                'rating_breakdown' => $ratingBreakdown
            ]
        ]);
    }

    /**
     * Update existing review
     */
    private function updateExistingReview(Request $request, Review $existingReview, Product $product)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update the existing review
            $existingReview->update([
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'verified_purchase' => $this->checkVerifiedPurchase($product->id, Auth::id()),
                'is_approved' => true
            ]);

            // Update product statistics
            $product->updateReviewStats();

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully!',
                'review' => [
                    'id' => $existingReview->id,
                    'user_id' => $existingReview->user_id,
                    'rating' => $existingReview->rating,
                    'title' => $existingReview->title,
                    'comment' => $existingReview->comment,
                    'user_name' => Auth::user()->name,
                    'created_at' => $existingReview->created_at->format('M j, Y'),
                    'updated_at' => $existingReview->updated_at->format('M j, Y'),
                    'verified_purchase' => $existingReview->verified_purchase,
                    'is_updated' => true
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review. Please try again.'
            ], 500);
        }
    }

    /**
     * Update a review
     */
    public function update(Request $request, Product $product, Review $review)
    {
        // Check if user owns this review
        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only update your own reviews.'
            ], 403);
        }

        // Check if review belongs to this product
        if ($review->product_id !== $product->id) {
            return response()->json([
                'success' => false,
                'message' => 'Review does not belong to this product.'
            ], 422);
        }

        return $this->updateExistingReview($request, $review, $product);
    }

    /**
     * Delete a review
     */
    public function destroy(Product $product, Review $review)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to delete a review.'
            ], 401);
        }

        // Check if user owns this review
        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own reviews.'
            ], 403);
        }

        // Check if review belongs to this product
        if ($review->product_id !== $product->id) {
            return response()->json([
                'success' => false,
                'message' => 'Review does not belong to this product.'
            ], 422);
        }

        try {
            $review->delete();
            
            // Update product statistics
            $product->updateReviewStats();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review. Please try again.'
            ], 500);
        }
    }
}