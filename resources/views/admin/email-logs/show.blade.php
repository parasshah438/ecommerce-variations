@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Email Log Details #{{ $emailLog->id }}</h4>
                    <div class="btn-group">
                        <a href="{{ route('admin.email-logs.index') }}" class="btn btn-secondary btn-sm">
                            ← Back to List
                        </a>
                        @if($emailLog->canRetry())
                        <a href="{{ route('admin.email-logs.retry', $emailLog) }}" 
                           class="btn btn-warning btn-sm"
                           onclick="return confirm('Retry this email?')">
                            Retry Email
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h5>Email Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Email Type:</strong></td>
                                    <td><span class="badge badge-secondary">{{ $emailLog->email_type }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Recipient:</strong></td>
                                    <td>{{ $emailLog->recipient_email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Subject:</strong></td>
                                    <td>{{ $emailLog->subject }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ 
                                            $emailLog->status === 'sent' ? 'success' : 
                                            ($emailLog->status === 'failed' ? 'danger' : 
                                            ($emailLog->status === 'retry' ? 'warning' : 'secondary')) 
                                        }}">
                                            {{ ucfirst($emailLog->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>User:</strong></td>
                                    <td>
                                        @if($emailLog->user)
                                            {{ $emailLog->user->name }} 
                                            <small class="text-muted">(ID: {{ $emailLog->user->id }})</small>
                                        @else
                                            <em>No user linked</em>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Timing Information -->
                        <div class="col-md-6">
                            <h5>Timing & Attempts</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Attempts:</strong></td>
                                    <td>
                                        {{ $emailLog->attempts }} / {{ $emailLog->max_attempts }}
                                        @if($emailLog->attempts >= $emailLog->max_attempts && $emailLog->status === 'failed')
                                            <small class="text-danger">(Max reached)</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $emailLog->created_at->format('F j, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Attempt:</strong></td>
                                    <td>
                                        @if($emailLog->last_attempt_at)
                                            {{ $emailLog->last_attempt_at->format('F j, Y g:i A') }}
                                        @else
                                            <em>No attempts yet</em>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Sent At:</strong></td>
                                    <td>
                                        @if($emailLog->sent_at)
                                            <span class="text-success">{{ $emailLog->sent_at->format('F j, Y g:i A') }}</span>
                                        @else
                                            <em>Not sent</em>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Next Retry:</strong></td>
                                    <td>
                                        @if($emailLog->next_retry_at)
                                            {{ $emailLog->next_retry_at->format('F j, Y g:i A') }}
                                            @if($emailLog->next_retry_at->isPast())
                                                <small class="text-warning">(Ready for retry)</small>
                                            @else
                                                <small class="text-info">({{ $emailLog->next_retry_at->diffForHumans() }})</small>
                                            @endif
                                        @else
                                            <em>No retry scheduled</em>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Error Message -->
                    @if($emailLog->error_message)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Error Details</h5>
                            <div class="alert alert-danger">
                                <strong>Error Message:</strong><br>
                                <code>{{ $emailLog->error_message }}</code>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Email Data -->
                    @if($emailLog->email_data)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Email Template Data</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <pre class="mb-0"><code>{{ json_encode($emailLog->email_data, JSON_PRETTY_PRINT) }}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Actions</h5>
                            <div class="btn-group">
                                @if($emailLog->canRetry())
                                <a href="{{ route('admin.email-logs.retry', $emailLog) }}" 
                                   class="btn btn-warning"
                                   onclick="return confirm('Retry this email now?')">
                                    <i class="fas fa-redo"></i> Retry Email
                                </a>
                                @endif
                                
                                <a href="{{ route('admin.email-logs.delete', $emailLog) }}" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Delete this email log? This cannot be undone.')">
                                    <i class="fas fa-trash"></i> Delete Log
                                </a>
                                
                                @if($emailLog->user)
                                <a href="#" class="btn btn-info" onclick="showUserEmails({{ $emailLog->user->id }})">
                                    <i class="fas fa-user"></i> View User's Emails
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Timeline</h5>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6>Email Created</h6>
                                        <small>{{ $emailLog->created_at->format('F j, Y g:i A') }}</small>
                                        <p class="mb-0">Email log entry created</p>
                                    </div>
                                </div>
                                
                                @if($emailLog->last_attempt_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6>Last Attempt</h6>
                                        <small>{{ $emailLog->last_attempt_at->format('F j, Y g:i A') }}</small>
                                        <p class="mb-0">
                                            Attempt #{{ $emailLog->attempts }}
                                            @if($emailLog->error_message)
                                                - <span class="text-danger">Failed</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                @endif
                                
                                @if($emailLog->sent_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6>Email Sent Successfully</h6>
                                        <small>{{ $emailLog->sent_at->format('F j, Y g:i A') }}</small>
                                        <p class="mb-0">Email delivered successfully</p>
                                    </div>
                                </div>
                                @endif
                                
                                @if($emailLog->next_retry_at && $emailLog->status === 'retry')
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6>Scheduled for Retry</h6>
                                        <small>{{ $emailLog->next_retry_at->format('F j, Y g:i A') }}</small>
                                        <p class="mb-0">
                                            @if($emailLog->next_retry_at->isPast())
                                                Ready for retry now
                                            @else
                                                Will retry {{ $emailLog->next_retry_at->diffForHumans() }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-weight: 600;
}

.timeline-content small {
    color: #6c757d;
}
</style>

<script>
function showUserEmails(userId) {
    window.open('{{ route("admin.email-logs.index") }}?user_id=' + userId, '_blank');
}
</script>
@endsection
