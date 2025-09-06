@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['sent'] }}</h4>
                            <small>Sent</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['pending'] }}</h4>
                            <small>Pending</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['retry'] }}</h4>
                            <small>Retry</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['failed'] }}</h4>
                            <small>Failed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['total_emails'] }}</h4>
                            <small>Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-dark text-white">
                        <div class="card-body text-center">
                            <h4>{{ $stats['recent_failures'] }}</h4>
                            <small>Recent Failures</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Email Logs Management</h4>
                    <div class="btn-group">
                        <a href="{{ route('admin.email-logs.retry-all', ['status' => 'failed']) }}" 
                           class="btn btn-warning btn-sm" 
                           onclick="return confirm('Retry all failed emails?')">
                            Retry Failed
                        </a>
                        <a href="{{ route('admin.email-logs.process-retry-queue') }}" 
                           class="btn btn-info btn-sm">
                            Process Retry Queue
                        </a>
                        <a href="{{ route('admin.email-logs.export', request()->query()) }}" 
                           class="btn btn-success btn-sm">
                            Export CSV
                        </a>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card-body border-bottom">
                    <form method="GET" class="row g-3">
                        <div class="col-md-2">
                            <select name="status" class="form-control form-control-sm">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="retry" {{ request('status') == 'retry' ? 'selected' : '' }}>Retry</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="email_type" class="form-control form-control-sm">
                                <option value="">All Types</option>
                                <option value="welcome" {{ request('email_type') == 'welcome' ? 'selected' : '' }}>Welcome</option>
                                <option value="order_confirmation" {{ request('email_type') == 'order_confirmation' ? 'selected' : '' }}>Order Confirmation</option>
                                <option value="password_reset" {{ request('email_type') == 'password_reset' ? 'selected' : '' }}>Password Reset</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control form-control-sm" 
                                   value="{{ request('date_from') }}" placeholder="From Date">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control form-control-sm" 
                                   value="{{ request('date_to') }}" placeholder="To Date">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            <a href="{{ route('admin.email-logs.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <form id="bulkForm" method="POST" action="{{ route('admin.email-logs.bulk-delete') }}">
                            @csrf
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" onchange="toggleAll()">
                                        </th>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Recipient</th>
                                        <th>User</th>
                                        <th>Status</th>
                                        <th>Attempts</th>
                                        <th>Created</th>
                                        <th>Sent</th>
                                        <th>Next Retry</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($emailLogs as $log)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="email_ids[]" value="{{ $log->id }}" class="email-checkbox">
                                        </td>
                                        <td>{{ $log->id }}</td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $log->email_type }}</span>
                                        </td>
                                        <td>{{ $log->recipient_email }}</td>
                                        <td>
                                            @if($log->user)
                                                {{ $log->user->name }}
                                            @else
                                                <em>No user</em>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ 
                                                $log->status === 'sent' ? 'success' : 
                                                ($log->status === 'failed' ? 'danger' : 
                                                ($log->status === 'retry' ? 'warning' : 'secondary')) 
                                            }}">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $log->attempts }} / {{ $log->max_attempts }}
                                            @if($log->attempts >= $log->max_attempts && $log->status === 'failed')
                                                <small class="text-danger">(Max reached)</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $log->created_at->format('M d, H:i') }}</small>
                                        </td>
                                        <td>
                                            @if($log->sent_at)
                                                <small class="text-success">{{ $log->sent_at->format('M d, H:i') }}</small>
                                            @else
                                                <small class="text-muted">Not sent</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->next_retry_at)
                                                <small class="text-info">{{ $log->next_retry_at->format('M d, H:i') }}</small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.email-logs.show', $log) }}" 
                                                   class="btn btn-outline-primary btn-sm">View</a>
                                                
                                                @if($log->canRetry())
                                                <a href="{{ route('admin.email-logs.retry', $log) }}" 
                                                   class="btn btn-outline-warning btn-sm"
                                                   onclick="return confirm('Retry this email?')">Retry</a>
                                                @endif
                                                
                                                <a href="{{ route('admin.email-logs.delete', $log) }}" 
                                                   class="btn btn-outline-danger btn-sm"
                                                   onclick="return confirm('Delete this email log?')">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4">
                                            <div class="text-muted">No email logs found</div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </form>
                    </div>
                    
                    @if($emailLogs->hasPages())
                    <div class="card-footer">
                        {{ $emailLogs->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>

                <!-- Bulk Actions -->
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()" id="bulkDeleteBtn" style="display: none;">
                                Delete Selected
                            </button>
                        </div>
                        <div>
                            <small class="text-muted">
                                Showing {{ $emailLogs->firstItem() ?? 0 }} to {{ $emailLogs->lastItem() ?? 0 }} 
                                of {{ $emailLogs->total() }} results
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAll() {
    const checkboxes = document.querySelectorAll('.email-checkbox');
    const selectAll = document.getElementById('selectAll');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    bulkDeleteBtn.style.display = selectAll.checked ? 'inline-block' : 'none';
}

function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.email-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select emails to delete');
        return;
    }
    
    if (confirm(`Delete ${checkedBoxes.length} selected email logs?`)) {
        document.getElementById('bulkForm').submit();
    }
}

// Show/hide bulk delete button based on selections
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.email-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const anyChecked = document.querySelectorAll('.email-checkbox:checked').length > 0;
            bulkDeleteBtn.style.display = anyChecked ? 'inline-block' : 'none';
        });
    });
});
</script>
@endsection
