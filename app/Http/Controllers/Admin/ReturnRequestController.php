<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderReturnRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReturnRequestController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $query = OrderReturnRequest::with(['order', 'user', 'reviewer']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by order ID or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhereHas('order', function ($oq) use ($search) {
                      $oq->where('id', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $returnRequests = $query->paginate(20)->appends($request->query());

        $stats = [
            'total' => OrderReturnRequest::count(),
            'pending' => OrderReturnRequest::where('status', OrderReturnRequest::STATUS_PENDING)->count(),
            'approved' => OrderReturnRequest::where('status', OrderReturnRequest::STATUS_APPROVED)->count(),
            'rejected' => OrderReturnRequest::where('status', OrderReturnRequest::STATUS_REJECTED)->count(),
            'refunded' => OrderReturnRequest::where('status', OrderReturnRequest::STATUS_REFUNDED)->count(),
        ];

        return view('admin.return-requests.index', compact('returnRequests', 'stats'));
    }

    public function show(OrderReturnRequest $returnRequest)
    {
        $returnRequest->load(['order.items.productVariation.product', 'user', 'reviewer']);
        return view('admin.return-requests.show', compact('returnRequest'));
    }

    public function approve(Request $request, OrderReturnRequest $returnRequest)
    {
        if (!$returnRequest->canBeApproved()) {
            return $this->jsonError('This return request has already been processed.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:500',
            'schedule_pickup' => 'boolean',
        ]);

        try {
            $this->orderService->approveReturnRequest($returnRequest, auth()->user(), $request->admin_note);

            $message = 'Return request approved successfully.';
            if ($request->boolean('schedule_pickup') && config('shiprocket.enabled', false)) {
                try {
                    $this->orderService->scheduleReturnPickup($returnRequest);
                    $message .= ' Shiprocket pickup has been scheduled.';
                } catch (\Exception $e) {
                    Log::warning("Return pickup scheduling failed: " . $e->getMessage());
                    $message .= ' However, pickup scheduling failed: ' . $e->getMessage();
                }
            }

            return $this->jsonSuccess($message, ['status' => $returnRequest->fresh()->formatted_status]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to approve return: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, OrderReturnRequest $returnRequest)
    {
        if (!$returnRequest->canBeRejected()) {
            return $this->jsonError('This return request has already been processed.');
        }

        $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        try {
            $returnRequest->update([
                'status' => OrderReturnRequest::STATUS_REJECTED,
                'admin_note' => $request->admin_note,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return $this->jsonSuccess('Return request rejected.', ['status' => $returnRequest->fresh()->formatted_status]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to reject return: ' . $e->getMessage());
        }
    }

    public function schedulePickup(Request $request, OrderReturnRequest $returnRequest)
    {
        if (!$returnRequest->isApproved()) {
            return $this->jsonError('Only approved return requests can have pickups scheduled.');
        }

        $request->validate([
            'pickup_date' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            $this->orderService->scheduleReturnPickup($returnRequest, $request->pickup_date);
            return $this->jsonSuccess('Return pickup scheduled successfully.', [
                'status' => $returnRequest->fresh()->formatted_status,
                'pickup_date' => $returnRequest->pickup_scheduled_date?->format('M d, Y H:i'),
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to schedule pickup: ' . $e->getMessage());
        }
    }

    public function markPickedUp(OrderReturnRequest $returnRequest)
    {
        if (!in_array($returnRequest->status, [OrderReturnRequest::STATUS_APPROVED, OrderReturnRequest::STATUS_PICKUP_SCHEDULED])) {
            return $this->jsonError('Return must be approved or have a scheduled pickup first.');
        }

        try {
            $returnRequest->update([
                'status' => OrderReturnRequest::STATUS_PICKED_UP,
                'picked_up_at' => now(),
            ]);

            return $this->jsonSuccess('Return marked as picked up.', [
                'status' => $returnRequest->fresh()->formatted_status,
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to mark as picked up: ' . $e->getMessage());
        }
    }

    public function processRefund(Request $request, OrderReturnRequest $returnRequest)
    {
        if ($returnRequest->status !== OrderReturnRequest::STATUS_PICKED_UP) {
            return $this->jsonError('Items must be picked up before processing refund.');
        }

        $request->validate([
            'refund_amount' => 'nullable|numeric|min:0|max:' . ($returnRequest->order->total),
        ]);

        try {
            $refundAmount = $request->filled('refund_amount') ? (float) $request->refund_amount : $returnRequest->refund_amount;
            $this->orderService->processReturnRefund($returnRequest, $refundAmount);

            return $this->jsonSuccess('Refund processed successfully.', [
                'status' => $returnRequest->fresh()->formatted_status,
                'refund_amount' => number_format($refundAmount, 2),
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to process refund: ' . $e->getMessage());
        }
    }

    protected function jsonSuccess(string $message, array $extra = [])
    {
        $response = ['success' => true, 'message' => $message] + $extra;
        if (request()->expectsJson()) {
            return response()->json($response);
        }
        return redirect()->back()->with('success', $message);
    }

    protected function jsonError(string $message)
    {
        if (request()->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }
        return redirect()->back()->with('error', $message);
    }
}
