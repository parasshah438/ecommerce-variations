@extends('layouts.app')

@section('title', 'WhatsApp Setup Guide')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">WhatsApp Integration Setup Guide</h1>
                    <p class="text-muted">Follow these steps to configure your WhatsApp integration</p>
                </div>
                <div>
                    <a href="{{ route('whatsapp.settings') }}" class="btn btn-primary">
                        <i class="bi bi-gear me-1"></i> Go to Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Step 1: Get Ultramsg Account -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><span class="badge bg-primary me-2">1</span> Create Ultramsg Account</h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Go to <a href="https://ultramsg.com" target="_blank">https://ultramsg.com</a></li>
                        <li>Create a new account or login to existing account</li>
                        <li>Create a new WhatsApp instance</li>
                        <li>Note down your <strong>Instance ID</strong> and <strong>Token</strong></li>
                    </ol>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        You'll need these credentials to configure the integration.
                    </div>
                </div>
            </div>

            <!-- Step 2: Environment Configuration -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><span class="badge bg-primary me-2">2</span> Configure Environment Variables</h5>
                </div>
                <div class="card-body">
                    <p>Add these variables to your <code>.env</code> file:</p>
                    <pre class="bg-dark text-white p-3 rounded"><code># WhatsApp Ultramsg Configuration
ULTRAMSG_INSTANCE_ID=your_instance_id_here
ULTRAMSG_TOKEN=your_token_here
ULTRAMSG_BASE_URL=https://api.ultramsg.com
ULTRAMSG_TIMEOUT=30
ULTRAMSG_WEBHOOK_SECRET=your_webhook_secret

# SSL Configuration (for local development)
ULTRAMSG_VERIFY_SSL=false
ULTRAMSG_USE_HTTP=false</code></pre>

                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>For Local Development:</strong> Set <code>ULTRAMSG_VERIFY_SSL=false</code> if you encounter SSL certificate issues.
                    </div>
                </div>
            </div>

            <!-- Step 3: SSL Configuration -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><span class="badge bg-primary me-2">3</span> SSL Configuration Options</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>For Local Development</h6>
                            <p>If you encounter SSL certificate errors:</p>
                            <pre class="bg-light p-2 rounded small"><code>ULTRAMSG_VERIFY_SSL=false</code></pre>
                            <p class="small text-muted">This disables SSL verification for local testing.</p>
                        </div>
                        <div class="col-md-6">
                            <h6>For Production</h6>
                            <p>Always use SSL verification in production:</p>
                            <pre class="bg-light p-2 rounded small"><code>ULTRAMSG_VERIFY_SSL=true</code></pre>
                            <p class="small text-muted">Ensures secure communication with the API.</p>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <h6><i class="bi bi-lightbulb me-2"></i>Alternative Solutions for SSL Issues:</h6>
                        <ul class="mb-0">
                            <li>Update your CA certificates bundle</li>
                            <li>Use HTTP instead of HTTPS for local testing: <code>ULTRAMSG_USE_HTTP=true</code></li>
                            <li>Configure your local PHP/cURL to use proper certificates</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Step 4: Configure Webhooks -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><span class="badge bg-primary me-2">4</span> Setup Webhooks (Optional)</h5>
                </div>
                <div class="card-body">
                    <p>Configure these webhook URLs in your Ultramsg dashboard to receive real-time updates:</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Message Webhook:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ route('whatsapp.webhook.message') }}" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard(this)">Copy</button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status Webhook:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ route('whatsapp.webhook.status') }}" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard(this)">Copy</button>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Webhooks allow you to receive real-time notifications when messages are delivered or when your instance status changes.
                    </div>
                </div>
            </div>

            <!-- Step 5: Test Connection -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><span class="badge bg-primary me-2">5</span> Test Your Configuration</h5>
                </div>
                <div class="card-body">
                    <p>Once you've configured everything, test your connection:</p>
                    <div class="d-grid gap-2 d-md-block">
                        <button class="btn btn-success" onclick="testConnection()">
                            <i class="bi bi-wifi me-1"></i> Test Connection
                        </button>
                        <button class="btn btn-info" onclick="getInstanceStatus()">
                            <i class="bi bi-info-circle me-1"></i> Check Instance Status
                        </button>
                    </div>
                    
                    <div id="testResults" class="mt-3" style="display: none;">
                        <!-- Test results will appear here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Links -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Quick Links</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="https://ultramsg.com" target="_blank" class="list-group-item list-group-item-action">
                            <i class="bi bi-box-arrow-up-right me-2"></i> Ultramsg Website
                        </a>
                        <a href="https://docs.ultramsg.com" target="_blank" class="list-group-item list-group-item-action">
                            <i class="bi bi-book me-2"></i> API Documentation
                        </a>
                        <a href="{{ route('whatsapp.settings') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-gear me-2"></i> WhatsApp Settings
                        </a>
                        <a href="{{ route('whatsapp.index') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-house me-2"></i> WhatsApp Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Common Issues -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Common Issues & Solutions</h6>
                </div>
                <div class="card-body">
                    <div class="accordion" id="issuesAccordion">
                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ssl-issue">
                                    SSL Certificate Error
                                </button>
                            </h6>
                            <div id="ssl-issue" class="accordion-collapse collapse" data-bs-parent="#issuesAccordion">
                                <div class="accordion-body small">
                                    <strong>Solution:</strong> Set <code>ULTRAMSG_VERIFY_SSL=false</code> in your .env file for local development.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#connection-timeout">
                                    Connection Timeout
                                </button>
                            </h6>
                            <div id="connection-timeout" class="accordion-collapse collapse" data-bs-parent="#issuesAccordion">
                                <div class="accordion-body small">
                                    <strong>Solution:</strong> Increase <code>ULTRAMSG_TIMEOUT</code> value or check your internet connection.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#invalid-credentials">
                                    Invalid Credentials
                                </button>
                            </h6>
                            <div id="invalid-credentials" class="accordion-collapse collapse" data-bs-parent="#issuesAccordion">
                                <div class="accordion-body small">
                                    <strong>Solution:</strong> Double-check your Instance ID and Token in the Ultramsg dashboard.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function testConnection() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing...';
    button.disabled = true;
    
    $.ajax({
        url: '{{ route("whatsapp.test.connection") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            showTestResult(response);
        },
        error: function() {
            showTestResult({
                success: false,
                message: 'Connection test failed. Please check your configuration.'
            });
        },
        complete: function() {
            button.innerHTML = originalText;
            button.disabled = false;
        }
    });
}

function getInstanceStatus() {
    $.ajax({
        url: '{{ route("whatsapp.instance.status") }}',
        method: 'GET',
        success: function(response) {
            showTestResult(response);
        },
        error: function() {
            showTestResult({
                success: false,
                message: 'Failed to get instance status.'
            });
        }
    });
}

function showTestResult(result) {
    const resultsDiv = $('#testResults');
    const alertClass = result.success ? 'alert-success' : 'alert-danger';
    const icon = result.success ? 'bi-check-circle' : 'bi-x-circle';
    
    let html = `
        <div class="alert ${alertClass}">
            <i class="bi ${icon} me-2"></i>
            <strong>${result.success ? 'Success!' : 'Error!'}</strong>
            ${result.message}
    `;
    
    if (result.warning) {
        html += `<br><small><i class="bi bi-exclamation-triangle me-1"></i>${result.warning}</small>`;
    }
    
    if (result.data) {
        html += `<br><small>Status: ${JSON.stringify(result.data)}</small>`;
    }
    
    html += '</div>';
    
    resultsDiv.html(html).show();
}

function copyToClipboard(button) {
    const input = button.parentNode.querySelector('input');
    input.select();
    document.execCommand('copy');
    
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        button.textContent = originalText;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>
@endpush
@endsection