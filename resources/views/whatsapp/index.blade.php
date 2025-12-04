@extends('layouts.app')

@section('title', 'WhatsApp Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">WhatsApp Dashboard</h1>
                    <p class="text-muted">Manage your WhatsApp messaging campaigns</p>
                </div>
                <div>
                    <a href="{{ route('whatsapp.send.form') }}" class="btn btn-success me-2">
                        <i class="bi bi-send me-1"></i> Send Message
                    </a>
                    <a href="{{ route('whatsapp.bulk.form') }}" class="btn btn-primary">
                        <i class="bi bi-broadcast me-1"></i> Bulk Send
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Messages
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_messages']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-chat-dots fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Sent Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['sent_today']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Delivered Messages
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['delivered_messages']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Contacts
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_contacts']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('whatsapp.send.form') }}" class="btn btn-outline-primary btn-block h-100 d-flex align-items-center justify-content-center">
                                <div class="text-center">
                                    <i class="bi bi-chat-text fa-2x mb-2"></i>
                                    <div>Send Text Message</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('whatsapp.send.form') }}?type=image" class="btn btn-outline-success btn-block h-100 d-flex align-items-center justify-content-center">
                                <div class="text-center">
                                    <i class="bi bi-image fa-2x mb-2"></i>
                                    <div>Send Image</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('whatsapp.templates.index') }}" class="btn btn-outline-info btn-block h-100 d-flex align-items-center justify-content-center">
                                <div class="text-center">
                                    <i class="bi bi-file-text fa-2x mb-2"></i>
                                    <div>Manage Templates</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('whatsapp.contacts.index') }}" class="btn btn-outline-warning btn-block h-100 d-flex align-items-center justify-content-center">
                                <div class="text-center">
                                    <i class="bi bi-person-lines-fill fa-2x mb-2"></i>
                                    <div>Manage Contacts</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Messages</h6>
                    <a href="{{ route('whatsapp.messages.list') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($recentMessages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Phone</th>
                                        <th>Type</th>
                                        <th>Content</th>
                                        <th>Status</th>
                                        <th>Sent At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentMessages as $message)
                                    <tr>
                                        <td>{{ $message->phone }}</td>
                                        <td>
                                            <span class="badge badge-{{ $message->message_type == 'text' ? 'primary' : 'secondary' }}">
                                                {{ ucfirst($message->message_type) }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($message->content, 50) }}</td>
                                        <td>
                                            @if($message->status == 'sent')
                                                <span class="badge badge-success">Sent</span>
                                            @elseif($message->status == 'delivered')
                                                <span class="badge badge-info">Delivered</span>
                                            @elseif($message->status == 'failed')
                                                <span class="badge badge-danger">Failed</span>
                                            @else
                                                <span class="badge badge-warning">{{ ucfirst($message->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $message->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-chat-square-dots fa-3x text-gray-400 mb-3"></i>
                            <h5 class="text-gray-600">No messages sent yet</h5>
                            <p class="text-gray-500">Start by sending your first WhatsApp message!</p>
                            <a href="{{ route('whatsapp.send.form') }}" class="btn btn-primary">Send Message</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Instance Status -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Instance Status</h6>
                </div>
                <div class="card-body">
                    <div id="instance-status" class="text-center py-3">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Checking status...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.border-left-primary { border-left: 0.25rem solid #4e73df!important; }
.border-left-success { border-left: 0.25rem solid #1cc88a!important; }
.border-left-info { border-left: 0.25rem solid #36b9cc!important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e!important; }
.text-xs { font-size: .7rem; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Check instance status
    checkInstanceStatus();
    
    // Auto refresh every 30 seconds
    setInterval(checkInstanceStatus, 30000);
    
    function checkInstanceStatus() {
        $.ajax({
            url: '{{ route("whatsapp.instance.status") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const status = response.data;
                    let statusHtml = '';
                    
                    if (status.accountStatus === 'authenticated') {
                        statusHtml = `
                            <div class="text-success">
                                <i class="bi bi-check-circle fa-2x mb-2"></i>
                                <h5>Connected</h5>
                                <p class="small">WhatsApp instance is active and ready</p>
                            </div>
                        `;
                    } else {
                        statusHtml = `
                            <div class="text-warning">
                                <i class="bi bi-exclamation-triangle fa-2x mb-2"></i>
                                <h5>Not Connected</h5>
                                <p class="small">Please scan QR code to authenticate</p>
                                <button class="btn btn-sm btn-primary" onclick="getQRCode()">Get QR Code</button>
                            </div>
                        `;
                    }
                    
                    $('#instance-status').html(statusHtml);
                } else {
                    $('#instance-status').html(`
                        <div class="text-danger">
                            <i class="bi bi-x-circle fa-2x mb-2"></i>
                            <h5>Connection Failed</h5>
                            <p class="small">${response.message}</p>
                        </div>
                    `);
                }
            },
            error: function() {
                $('#instance-status').html(`
                    <div class="text-danger">
                        <i class="bi bi-wifi-off fa-2x mb-2"></i>
                        <h5>Status Unknown</h5>
                        <p class="small">Unable to check instance status</p>
                    </div>
                `);
            }
        });
    }
    
    function getQRCode() {
        $.ajax({
            url: '{{ route("whatsapp.qr.code") }}',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data.qr) {
                    const qrModal = `
                        <div class="modal fade" id="qrModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Scan QR Code</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img src="${response.data.qr}" class="img-fluid mb-3" alt="QR Code">
                                        <p>Open WhatsApp on your phone and scan this QR code</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('body').append(qrModal);
                    $('#qrModal').modal('show');
                    
                    // Remove modal when closed
                    $('#qrModal').on('hidden.bs.modal', function () {
                        $(this).remove();
                    });
                } else {
                    alert('Failed to get QR code: ' + (response.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('Failed to get QR code. Please try again.');
            }
        });
    }
});
</script>
@endpush
@endsection