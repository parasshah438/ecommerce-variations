<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $query = Review::with([
            'product:id,name,slug',
            'user:id,name,email',
        ]);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', (int) $request->product_id);
        }

        $reviews = $query->latest()->paginate(20)->appends($request->all());

        $stats = [
            'total' => Review::count(),
            'pending' => Review::where('status', Review::STATUS_PENDING)->count(),
            'approved' => Review::where('status', Review::STATUS_APPROVED)->count(),
            'rejected' => Review::where('status', Review::STATUS_REJECTED)->count(),
            'reported' => Review::where('status', Review::STATUS_REPORTED)->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    public function show(Review $review)
    {
        $review->load([
            'product.category',
            'product.images',
            'user',
            'moderator:id,name',
        ]);

        return view('admin.reviews.show', compact('review'));
    }

    public function approve(Request $request, Review $review)
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $review->markApproved(auth()->id(), $request->input('admin_notes'));

        return $this->redirectAfterAction($review, 'Review approved successfully.');
    }

    public function reject(Request $request, Review $review)
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $review->markRejected(auth()->id(), $request->input('admin_notes'));

        return $this->redirectAfterAction($review, 'Review rejected successfully.');
    }

    public function report(Request $request, Review $review)
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ], [
            'admin_notes.required' => 'Please provide a reason for reporting this review.',
        ]);

        $review->markReported(auth()->id(), $request->input('admin_notes'));

        return $this->redirectAfterAction($review, 'Review marked as reported.');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject,delete'],
            'review_ids' => ['required', 'array', 'min:1'],
            'review_ids.*' => ['integer', 'exists:reviews,id'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $reviews = Review::whereIn('id', $validated['review_ids'])->get();
        $count = $reviews->count();

        foreach ($reviews as $review) {
            match ($validated['action']) {
                'approve' => $review->markApproved(auth()->id(), $validated['admin_notes'] ?? null),
                'reject' => $review->markRejected(auth()->id(), $validated['admin_notes'] ?? null),
                'delete' => $review->delete(),
            };
        }

        $message = match ($validated['action']) {
            'approve' => "{$count} review(s) approved.",
            'reject' => "{$count} review(s) rejected.",
            'delete' => "{$count} review(s) deleted.",
        };

        return redirect()
            ->route('admin.reviews.index', $request->only(['status', 'search', 'rating']))
            ->with('success', $message);
    }

    private function redirectAfterAction(Review $review, string $message)
    {
        if (request()->boolean('redirect_to_index')) {
            return redirect()
                ->route('admin.reviews.index')
                ->with('success', $message);
        }

        return redirect()
            ->route('admin.reviews.show', $review)
            ->with('success', $message);
    }
}
