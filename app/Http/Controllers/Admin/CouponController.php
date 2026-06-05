<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of coupons.
     */
    public function index(Request $request)
    {
        $query = Coupon::query();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $today = now()->toDateString();

            if ($request->status === 'active') {
                $query->where(function ($q) use ($today) {
                    $q->where(function ($q2) use ($today) {
                        $q2->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today);
                    })->where(function ($q2) use ($today) {
                        $q2->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today);
                    })->where(function ($q2) {
                        $q2->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
                    });
                });
            } elseif ($request->status === 'expired') {
                $query->whereNotNull('valid_until')->whereDate('valid_until', '<', $today);
            } elseif ($request->status === 'scheduled') {
                $query->whereNotNull('valid_from')->whereDate('valid_from', '>', $today);
            } elseif ($request->status === 'used_up') {
                $query->whereNotNull('usage_limit')->whereColumn('used_count', '>=', 'usage_limit');
            }
        }

        $coupons = $query->orderByDesc('created_at')->paginate(15)->appends($request->all());

        $stats = [
            'total' => Coupon::count(),
            'active' => Coupon::where(function ($q) {
                $today = now()->toDateString();
                $q->where(function ($q2) use ($today) {
                    $q2->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today);
                })->where(function ($q2) use ($today) {
                    $q2->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today);
                })->where(function ($q2) {
                    $q2->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
                });
            })->count(),
            'percentage' => Coupon::where('type', 'percentage')->count(),
            'fixed' => Coupon::where('type', 'fixed')->count(),
        ];

        return view('admin.coupons.index', compact('coupons', 'stats'));
    }

    /**
     * Show the form for creating a new coupon.
     */
    public function create()
    {
        return view('admin.coupons.create');
    }

    /**
     * Store a newly created coupon.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:coupons,code'],
            'discount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'minimum_cart_value' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount_limit' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['minimum_cart_value'] = $validated['minimum_cart_value'] ?? 0;
        $validated['used_count'] = 0;

        if ($validated['type'] === 'percentage') {
            $validated['discount'] = min((float) $validated['discount'], 100);
        }

        Coupon::create($validated);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully!');
    }

    /**
     * Display the specified coupon.
     */
    public function show(Coupon $coupon)
    {
        $coupon->load('carts.user');

        return view('admin.coupons.show', compact('coupon'));
    }

    /**
     * Show the form for editing the specified coupon.
     */
    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * Update the specified coupon.
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('coupons', 'code')->ignore($coupon->id),
            ],
            'discount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'minimum_cart_value' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount_limit' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['minimum_cart_value'] = $validated['minimum_cart_value'] ?? 0;
        unset($validated['used_count']);

        if ($validated['type'] === 'percentage') {
            $validated['discount'] = min((float) $validated['discount'], 100);
        }

        $coupon->update($validated);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully!');
    }

    /**
     * Remove the specified coupon.
     */
    public function destroy(Coupon $coupon)
    {
        if ($coupon->carts()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete coupon because it is applied to one or more carts.']);
        }

        $coupon->delete();

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully!');
    }
}
