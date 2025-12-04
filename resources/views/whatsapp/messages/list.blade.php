@extends('layouts.app')

@section('title', 'WhatsApp Messages')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">WhatsApp Messages</h1>
                    <p class="text-muted">View all sent messages and their status</p>
                </div>
                <div>
                    <a href="{{ route('whatsapp.send.form') }}" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Send New Message
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="text" {{ request('type') == 'text' ? 'selected' : '' }}>Text</option>
                                <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Image</option>
                                <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>Document</option>
                                <option value="audio" {{ request('type') == 'audio' ? 'selected' : '' }}>Audio</option>
                                <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Video</option>
                                <option value="contact" {{ request('type') == 'contact' ? 'selected' : '' }}>Contact</option>
                                <option value="location" {{ request('type') == 'location' ? 'selected' : '' }}>Location</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search phone or content..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    @if($messages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="5%">Type</th>
                                        <th width="15%">Phone</th>
                                        <th width="30%">Content</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">Batch</th>
                                        <th width="15%">Sent At</th>
                                        <th width="10%">Duration</th>
                                        <th width="5%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $message)
                                    <tr>
                                        <td class="text-center">
                                            {!! $message->type_icon !!}
                                            <br>
                                            <small>{{ ucfirst($message->message_type) }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $message->formatted_phone }}</strong>
                                            @if($message->contact)
                                                <br><small class="text-muted">{{ $message->contact->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="message-content">
                                                @if($message->message_type == 'text')
                                                    {{ $message->short_content }}
                                                @elseif($message->message_type == 'image')
                                                    @if($message->hasMedia())
                                                        <img src="{{ $message->getMediaDisplayUrl() }}" class="img-thumbnail" style="max-width: 100px; max-height: 60px;">
                                                        <br>
                                                    @endif
                                                    <small class="text-muted">{{ $message->content ?: 'Image message' }}</small>
                                                @elseif($message->message_type == 'document')
                                                    <i class="bi bi-file-text me-1"></i>
                                                    <small>{{ $message->content ?: 'Document' }}</small>
                                                @elseif($message->message_type == 'audio')
                                                    <i class="bi bi-mic me-1"></i>
                                                    <small>Audio message</small>
                                                @elseif($message->message_type == 'contact')
                                                    <i class="bi bi-person-circle me-1"></i>
                                                    <small>Contact shared</small>
                                                @else
                                                    <small class="text-muted">{{ ucfirst($message->message_type) }} message</small>
                                                @endif
                                            </div>
                                            @if($message->template)
                                                <br><span class="badge bg-info">{{ $message->template->name }}</span>
                                            @endif
                                        </td>
                                        <td>{!! $message->status_badge !!}</td>
                                        <td>
                                            @if($message->batch_id)
                                                <a href="{{ route('whatsapp.bulk.status', $message->batch_id) }}" class="text-decoration-none">
                                                    <small>{{ substr($message->batch_id, -8) }}</small>
                                                </a>
                                            @else
                                                <small class="text-muted">Single</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                {{ $message->created_at->format('M d, Y') }}<br>
                                                {{ $message->created_at->format('H:i:s') }}
                                            </small>
                                            <br>
                                            <small class="text-muted">{{ $message->time_ago }}</small>
                                        </td>
                                        <td>
                                            @if($message->delivered_at)
                                                <small class="text-success">
                                                    {{ $message->created_at->diffInSeconds($message->delivered_at) }}s
                                                </small>
                                            @elseif($message->failed_at)
                                                <small class="text-danger">
                                                    {{ $message->created_at->diffInSeconds($message->failed_at) }}s
                                                </small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="viewMessage({{ $message->id }})">
                                                        <i class="bi bi-eye me-1"></i> View Details
                                                    </a></li>
                                                    @if($message->hasMedia())
                                                    <li><a class="dropdown-item" href="{{ $message->getMediaDisplayUrl() }}" target="_blank">
                                                        <i class="bi bi-download me-1"></i> Download Media
                                                    </a></li>
                                                    @endif
                                                    <li><a class="dropdown-item" href="#" onclick="resendMessage({{ $message->id }})">
                                                        <i class="bi bi-arrow-clockwise me-1"></i> Resend
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteMessage({{ $message->id }})">
                                                        <i class="bi bi-trash me-1"></i> Delete
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $messages->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-chat-square-dots fa-3x text-gray-400 mb-3"></i>
                            <h5 class="text-gray-600">No messages found</h5>
                            @if(request()->hasAny(['status', 'type', 'date_from', 'date_to', 'search']))
                                <p class="text-gray-500">Try adjusting your filters to find messages.</p>
                                <a href="{{ route('whatsapp.messages.list') }}" class="btn btn-outline-primary">Clear Filters</a>
                            @else
                                <p class="text-gray-500">Start sending messages to see them here!</p>
                                <a href="{{ route('whatsapp.send.form') }}" class="btn btn-primary">Send Message</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Message Details Modal -->
<div class="modal fade" id="messageDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="messageDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewMessage(messageId) {
    $('#messageDetailsModal').modal('show');
    $('#messageDetailsContent').html('<div class="text-center"><div class="spinner-border"></div></div>');
    
    // In real implementation, you'd fetch message details via AJAX
    setTimeout(function() {
        $('#messageDetailsContent').html(`
            <div class="row">
                <div class="col-md-6">
                    <h6>Message Information</h6>
                    <p><strong>ID:</strong> ${messageId}</p>
                    <p><strong>Type:</strong> Text</p>
                    <p><strong>Status:</strong> <span class="badge bg-success">Delivered</span></p>
                    <p><strong>Phone:</strong> +91 98765 43210</p>
                </div>
                <div class="col-md-6">
                    <h6>Timestamps</h6>
                    <p><strong>Sent:</strong> 2024-01-15 10:30:00</p>
                    <p><strong>Delivered:</strong> 2024-01-15 10:30:15</p>
                    <p><strong>Read:</strong> 2024-01-15 10:35:22</p>
                </div>
            </div>
            <hr>
            <h6>Message Content</h6>
            <div class="border p-3 bg-light rounded">
                This is a sample message content that would be displayed here.
            </div>
            <hr>
            <h6>API Response</h6>
            <pre class="bg-dark text-white p-3 rounded"><code>{
    "id": "message_12345",
    "sent": true,
    "status": "delivered"
}</code></pre>
        `);
    }, 1000);
}

function resendMessage(messageId) {
    if (confirm('Are you sure you want to resend this message?')) {
        showAlert('info', 'Resend message functionality would be implemented here.');
    }
}

function deleteMessage(messageId) {
    if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
        showAlert('info', 'Delete message functionality would be implemented here.');
    }
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-danger' : 'alert-info');
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('.alert').remove();
    $('.container-fluid').prepend(alertHtml);
    
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

// Auto refresh every 30 seconds for real-time status updates
setInterval(function() {
    if (!$('.modal').hasClass('show')) { // Only refresh if no modal is open
        // You could add AJAX call here to refresh message statuses
    }
}, 30000);
</script>
@endpush
@endsection