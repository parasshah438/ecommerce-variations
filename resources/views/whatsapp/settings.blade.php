@extends('layouts.app')

@section('title', 'WhatsApp Settings')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">WhatsApp Settings</h1>
                    <p class="text-muted">Configure your WhatsApp integration settings</p>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="testConnection()">
                        <i class="bi bi-wifi me-1"></i> Test Connection
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- API Configuration -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">API Configuration</h6>
                </div>
                <div class="card-body">
                    <form id="settingsForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Instance ID *</label>
                                <input type="text" class="form-control" name="instance_id" 
                                       value="{{ config('whatsapp.ultramsg.instance_id', '') }}" 
                                       placeholder="instance12345" required>
                                <div class="form-text">Your Ultramsg instance ID</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">API Token *</label>
                                <input type="password" class="form-control" name="token" 
                                       value="{{ config('whatsapp.ultramsg.token', '') }}" 
                                       placeholder="Your API token" required>
                                <div class="form-text">Your Ultramsg API token</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Base URL</label>
                                <input type="url" class="form-control" name="base_url" 
                                       value="{{ config('whatsapp.ultramsg.base_url', 'https://api.ultramsg.com') }}" 
                                       placeholder="https://api.ultramsg.com">
                                <div class="form-text">Ultramsg API base URL</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Timeout (seconds)</label>
                                <input type="number" class="form-control" name="timeout" 
                                       value="{{ config('whatsapp.ultramsg.timeout', 30) }}" 
                                       min="10" max="300" placeholder="30">
                                <div class="form-text">API request timeout</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Webhook Secret</label>
                            <input type="text" class="form-control" name="webhook_secret" 
                                   value="{{ config('whatsapp.ultramsg.webhook_secret', '') }}" 
                                   placeholder="Optional webhook secret">
                            <div class="form-text">Used to verify webhook requests (optional)</div>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i> Save Configuration
                        </button>
                    </form>
                </div>
            </div>

            <!-- Message Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Message Settings</h6>
                </div>
                <div class="card-body">
                    <form id="messageSettingsForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Max Text Length</label>
                                <input type="number" class="form-control" name="max_text_length" 
                                       value="{{ config('whatsapp.message_settings.max_text_length', 1000) }}" 
                                       min="100" max="4000">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Max Caption Length</label>
                                <input type="number" class="form-control" name="max_caption_length" 
                                       value="{{ config('whatsapp.message_settings.max_caption_length', 500) }}" 
                                       min="50" max="1000">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Max Bulk Recipients</label>
                                <input type="number" class="form-control" name="max_bulk_recipients" 
                                       value="{{ config('whatsapp.message_settings.max_bulk_recipients', 100) }}" 
                                       min="1" max="1000">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rate Limit (per minute)</label>
                                <input type="number" class="form-control" name="rate_limit" 
                                       value="{{ config('whatsapp.message_settings.rate_limit_per_minute', 20) }}" 
                                       min="1" max="100">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Default Country Code</label>
                                <input type="text" class="form-control" name="country_code" 
                                       value="{{ config('whatsapp.country_settings.default_country_code', '91') }}" 
                                       placeholder="91">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-1"></i> Save Message Settings
                        </button>
                    </form>
                </div>
            </div>

            <!-- Feature Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Feature Settings</h6>
                </div>
                <div class="card-body">
                    <form id="featureSettingsForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Messaging Features</h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="bulk_messaging" id="bulk_messaging" 
                                           {{ config('whatsapp.features.bulk_messaging', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bulk_messaging">Bulk Messaging</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="templates" id="templates" 
                                           {{ config('whatsapp.features.templates', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="templates">Message Templates</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="media_messages" id="media_messages" 
                                           {{ config('whatsapp.features.media_messages', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="media_messages">Media Messages</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="group_messaging" id="group_messaging" 
                                           {{ config('whatsapp.features.group_messaging', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="group_messaging">Group Messaging</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Advanced Features</h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="auto_responses" id="auto_responses" 
                                           {{ config('whatsapp.features.auto_responses', false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_responses">Auto Responses</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="scheduled_messages" id="scheduled_messages" 
                                           {{ config('whatsapp.features.scheduled_messages', false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="scheduled_messages">Scheduled Messages</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="analytics_dashboard" id="analytics_dashboard" 
                                           {{ config('whatsapp.features.analytics_dashboard', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="analytics_dashboard">Analytics Dashboard</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="chatbot_integration" id="chatbot_integration" 
                                           {{ config('whatsapp.features.chatbot_integration', false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="chatbot_integration">Chatbot Integration</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success mt-3">
                            <i class="bi bi-save me-1"></i> Save Feature Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Instance Status & Info -->
        <div class="col-lg-4">
            <!-- Instance Status -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Instance Status</h6>
                </div>
                <div class="card-body">
                    <div id="instanceStatus" class="text-center py-3">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Checking status...</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-info" onclick="getQRCode()">
                            <i class="bi bi-qr-code me-1"></i> Get QR Code
                        </button>
                        <button class="btn btn-warning" onclick="restartInstance()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Restart Instance
                        </button>
                        <button class="btn btn-primary" onclick="testConnection()">
                            <i class="bi bi-wifi me-1"></i> Test Connection
                        </button>
                        <a href="{{ route('whatsapp.analytics') }}" class="btn btn-success">
                            <i class="bi bi-graph-up me-1"></i> View Analytics
                        </a>
                    </div>
                </div>
            </div>

            <!-- Webhook Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Webhook URLs</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small">Message Webhook</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" 
                                   value="{{ route('whatsapp.webhook.message') }}" readonly>
                            <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard(this)">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Status Webhook</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" 
                                   value="{{ route('whatsapp.webhook.status') }}" readonly>
                            <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard(this)">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Instance Webhook</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" 
                                   value="{{ route('whatsapp.webhook.instance') }}" readonly>
                            <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard(this)">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted">Configure these URLs in your Ultramsg dashboard</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrCodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scan QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="qrCodeContent">
                <!-- QR code will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Check instance status on load
    checkInstanceStatus();
    
    // Auto refresh every 30 seconds
    setInterval(checkInstanceStatus, 30000);
    
    // Settings form submission
    $('#settingsForm').on('submit', function(e) {
        e.preventDefault();
        saveSettings($(this), 'API configuration');
    });
    
    $('#messageSettingsForm').on('submit', function(e) {
        e.preventDefault();
        saveSettings($(this), 'Message settings');
    });
    
    $('#featureSettingsForm').on('submit', function(e) {
        e.preventDefault();
        saveSettings($(this), 'Feature settings');
    });
});

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
                            <p class="small">Instance is active and ready</p>
                        </div>
                    `;
                } else {
                    statusHtml = `
                        <div class="text-warning">
                            <i class="bi bi-exclamation-triangle fa-2x mb-2"></i>
                            <h5>Not Connected</h5>
                            <p class="small">Please scan QR code to authenticate</p>
                        </div>
                    `;
                }
                
                $('#instanceStatus').html(statusHtml);
            } else {
                $('#instanceStatus').html(`
                    <div class="text-danger">
                        <i class="bi bi-x-circle fa-2x mb-2"></i>
                        <h5>Connection Failed</h5>
                        <p class="small">${response.message}</p>
                    </div>
                `);
            }
        },
        error: function() {
            $('#instanceStatus').html(`
                <div class="text-danger">
                    <i class="bi bi-wifi-off fa-2x mb-2"></i>
                    <h5>Status Unknown</h5>
                    <p class="small">Unable to check instance status</p>
                </div>
            `);
        }
    });
}

function saveSettings(form, settingsName) {
    const formData = new FormData(form[0]);
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.text();
    
    submitBtn.prop('disabled', true).text('Saving...');
    
    $.ajax({
        url: '{{ route("whatsapp.settings.update") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert('success', settingsName + ' saved successfully!');
            } else {
                showAlert('error', response.message || 'Failed to save ' + settingsName);
            }
        },
        error: function(xhr) {
            let message = 'Failed to save ' + settingsName;
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert('error', message);
        },
        complete: function() {
            submitBtn.prop('disabled', false).text(originalText);
        }
    });
}

function testConnection() {
    $.ajax({
        url: '{{ route("whatsapp.test.connection") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            if (response.success) {
                showAlert('success', 'Connection test successful!');
            } else {
                showAlert('error', 'Connection test failed: ' + (response.message || 'Unknown error'));
            }
        },
        error: function() {
            showAlert('error', 'Connection test failed. Please check your settings.');
        }
    });
}

function getQRCode() {
    $('#qrCodeModal').modal('show');
    $('#qrCodeContent').html('<div class="spinner-border"></div><p class="mt-2">Loading QR code...</p>');
    
    $.ajax({
        url: '{{ route("whatsapp.qr.code") }}',
        method: 'GET',
        success: function(response) {
            if (response.success && response.data.qr) {
                $('#qrCodeContent').html(`
                    <img src="${response.data.qr}" class="img-fluid mb-3" alt="QR Code">
                    <p>Open WhatsApp on your phone and scan this QR code</p>
                `);
            } else {
                $('#qrCodeContent').html('<p class="text-danger">Failed to get QR code: ' + (response.message || 'Unknown error') + '</p>');
            }
        },
        error: function() {
            $('#qrCodeContent').html('<p class="text-danger">Failed to get QR code. Please try again.</p>');
        }
    });
}

function restartInstance() {
    if (confirm('Are you sure you want to restart the WhatsApp instance? This may disconnect your current session.')) {
        $.ajax({
            url: '{{ route("whatsapp.instance.restart") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Instance restart initiated. Please wait a moment and check the status.');
                    setTimeout(checkInstanceStatus, 5000);
                } else {
                    showAlert('error', 'Failed to restart instance: ' + (response.message || 'Unknown error'));
                }
            },
            error: function() {
                showAlert('error', 'Failed to restart instance. Please try again.');
            }
        });
    }
}

function copyToClipboard(button) {
    const input = button.parentNode.querySelector('input');
    input.select();
    document.execCommand('copy');
    
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check"></i>';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        button.innerHTML = originalHtml;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('.alert').remove();
    $('.container-fluid').prepend(alertHtml);
    
    setTimeout(() => $('.alert').fadeOut(), 5000);
}
</script>
@endpush
@endsection