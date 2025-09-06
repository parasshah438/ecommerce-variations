<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Services\ReliableEmailService;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    protected $emailService;

    public function __construct(ReliableEmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function index(Request $request)
    {
        $query = EmailLog::with('user');

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by email type
        if ($request->has('email_type') && $request->email_type != '') {
            $query->where('email_type', $request->email_type);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $emailLogs = $query->orderBy('created_at', 'desc')->paginate(50);
        $stats = $this->emailService->getStats();

        return view('admin.email-logs.index', compact('emailLogs', 'stats'));
    }

    public function show(EmailLog $emailLog)
    {
        $emailLog->load('user');
        return view('admin.email-logs.show', compact('emailLog'));
    }

    public function retry(EmailLog $emailLog)
    {
        if (!$emailLog->canRetry()) {
            return redirect()->back()->with('error', 'This email cannot be retried');
        }

        // Reset for retry
        $emailLog->update([
            'status' => EmailLog::STATUS_RETRY,
            'next_retry_at' => now(),
            'error_message' => null
        ]);

        // Attempt immediate retry
        $success = $this->emailService->attemptSend($emailLog);

        if ($success) {
            return redirect()->back()->with('success', 'Email sent successfully');
        } else {
            return redirect()->back()->with('warning', 'Email queued for retry');
        }
    }

    public function retryAll(Request $request)
    {
        $status = $request->input('status', 'failed');
        
        if ($status === 'failed') {
            $results = $this->emailService->retryFailedEmails();
            return redirect()->back()->with('success', 
                "Retried {$results['retried']} failed emails. {$results['successful']} sent successfully.");
        }

        return redirect()->back()->with('error', 'Invalid retry type');
    }

    public function processRetryQueue()
    {
        $results = $this->emailService->processRetryQueue();
        
        return redirect()->back()->with('success', 
            "Processed {$results['processed']} emails. {$results['successful']} successful, {$results['failed']} failed.");
    }

    public function delete(EmailLog $emailLog)
    {
        $emailLog->delete();
        return redirect()->back()->with('success', 'Email log deleted');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('email_ids', []);
        $count = EmailLog::whereIn('id', $ids)->delete();
        
        return redirect()->back()->with('success', "Deleted {$count} email logs");
    }

    public function export(Request $request)
    {
        $query = EmailLog::with('user');

        // Apply same filters as index
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('email_type') && $request->email_type != '') {
            $query->where('email_type', $request->email_type);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $emailLogs = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $csv = "ID,Email Type,Recipient,Status,Attempts,Error,Created At,Sent At\n";
        
        foreach ($emailLogs as $log) {
            $csv .= implode(',', [
                $log->id,
                $log->email_type,
                $log->recipient_email,
                $log->status,
                $log->attempts,
                '"' . str_replace('"', '""', $log->error_message ?? '') . '"',
                $log->created_at->format('Y-m-d H:i:s'),
                $log->sent_at ? $log->sent_at->format('Y-m-d H:i:s') : ''
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="email-logs-' . date('Y-m-d') . '.csv"');
    }
}
