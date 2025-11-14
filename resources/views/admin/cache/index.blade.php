@extends('layouts.admin')

@section('title', 'Cache Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-memory me-2"></i>Cache Management
                    </h3>
                    <div class="badge bg-info">{{ $cacheStats['driver'] ?? 'Unknown' }} Driver</div>
                </div>
                
                <div class="card-body">
                    <!-- Cache Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1">{{ number_format($cacheStats['total_entries']) }}</h4>
                                    <small>Total Entries</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1">{{ number_format($cacheStats['active_entries']) }}</h4>
                                    <small>Active Entries</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1">{{ number_format($cacheStats['expired_entries']) }}</h4>
                                    <small>Expired Entries</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1">{{ $cacheStats['size_estimate'] }}</h4>
                                    <small>Estimated Size</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cache Clear Actions -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-trash me-2"></i>Clear Cache
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        <strong>Warning:</strong> Clearing cache will temporarily affect site performance until cache rebuilds.
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-primary" onclick="clearCache('application')">
                                            <i class="bi bi-layers-half me-2"></i>Clear Application Cache
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="clearCache('config')">
                                            <i class="bi bi-gear me-2"></i>Clear & Rebuild Config Cache
                                        </button>
                                        <button class="btn btn-outline-info" onclick="clearCache('route')">
                                            <i class="bi bi-signpost-2 me-2"></i>Clear & Rebuild Route Cache
                                        </button>
                                        <button class="btn btn-outline-success" onclick="clearCache('view')">
                                            <i class="bi bi-eye me-2"></i>Clear & Rebuild View Cache
                                        </button>
                                        <hr>
                                        <button class="btn btn-danger" onclick="confirmClearAll()">
                                            <i class="bi bi-trash3 me-2"></i>Clear All Caches
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-key me-2"></i>Clear Specific Keys
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="specificKeys" class="form-label">Cache Keys (one per line)</label>
                                        <textarea class="form-control" id="specificKeys" rows="8" 
                                                  placeholder="similar_products_1_electronics_samsung&#10;categories_with_products&#10;brands_with_products&#10;product_price_range"></textarea>
                                        <div class="form-text">
                                            Common keys:
                                            <br>• <code>categories_with_products</code>
                                            <br>• <code>brands_with_products</code>
                                            <br>• <code>product_price_range</code>
                                            <br>• <code>similar_products_*</code>
                                        </div>
                                    </div>
                                    <button class="btn btn-warning w-100" onclick="clearSpecificKeys()">
                                        <i class="bi bi-key me-2"></i>Clear Specific Keys
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="bi bi-clock-history me-2"></i>Recent Cache Activity
                                    </h5>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="loadLogs()">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="activityLogs">
                                        <div class="text-center text-muted py-3">
                                            <i class="bi bi-hourglass-split fs-3"></i>
                                            <p class="mb-0">Click Refresh to load recent activity</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Are you sure?</strong>
                </div>
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmButton">Confirm</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-load logs on page load
    loadLogs();
});

function clearCache(type) {
    const button = event.target;
    const originalContent = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    button.disabled = true;
    
    fetch('{{ route("admin.cache.clear") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            cache_type: type
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Cache cleared successfully: ${type}`);
            
            // Show detailed results
            if (data.results) {
                let details = '<ul class="mb-0">';
                Object.entries(data.results).forEach(([key, status]) => {
                    const icon = status === 'success' ? 'check-circle text-success' : 'x-circle text-danger';
                    details += `<li><i class="bi bi-${icon} me-1"></i>${key}: ${status}</li>`;
                });
                details += '</ul>';
                
                showAlert('info', 'Detailed Results:', details);
            }
            
            // Refresh page after 2 seconds to update statistics
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showAlert('danger', 'Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Network error occurred. Please try again.');
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function confirmClearAll() {
    document.getElementById('confirmMessage').innerHTML = 
        'This will clear ALL cache types (application, config, routes, views). ' +
        'Your site may experience temporary performance degradation until caches rebuild.';
    
    document.getElementById('confirmButton').onclick = function() {
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
        clearCache('all');
    };
    
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

function clearSpecificKeys() {
    const keysText = document.getElementById('specificKeys').value.trim();
    if (!keysText) {
        showAlert('warning', 'Please enter at least one cache key.');
        return;
    }
    
    const keys = keysText.split('\n').map(key => key.trim()).filter(key => key);
    
    fetch('{{ route("admin.cache.clear-specific") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            cache_keys: keys
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            
            if (data.cleared.length > 0) {
                showAlert('info', 'Cleared Keys:', data.cleared.join(', '));
            }
            
            if (data.failed.length > 0) {
                showAlert('warning', 'Failed Keys:', data.failed.join(', '));
            }
            
            // Clear the textarea
            document.getElementById('specificKeys').value = '';
        } else {
            showAlert('danger', 'Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Network error occurred. Please try again.');
    });
}

function loadLogs() {
    const container = document.getElementById('activityLogs');
    container.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    
    fetch('{{ route("admin.cache.logs") }}?per_page=10')
    .then(response => response.json())
    .then(data => {
        if (data.success && data.logs.length > 0) {
            let html = '<div class="list-group list-group-flush">';
            data.logs.forEach(log => {
                // Simple log parsing - in production you might use a more sophisticated approach
                const logInfo = parseLogLine(log);
                html += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <i class="bi bi-activity me-2 text-primary"></i>
                                ${logInfo.message}
                            </div>
                            <small class="text-muted">${logInfo.time}</small>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center text-muted py-3">No recent cache activity found.</div>';
        }
    })
    .catch(error => {
        console.error('Error loading logs:', error);
        container.innerHTML = '<div class="text-center text-danger py-3">Failed to load activity logs.</div>';
    });
}

function parseLogLine(logLine) {
    // Simple log parsing - extract timestamp and message
    const timestampMatch = logLine.match(/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/);
    const timestamp = timestampMatch ? timestampMatch[0] : 'Unknown time';
    
    let message = 'Cache activity';
    if (logLine.includes('Cache cleared by admin')) {
        message = 'Cache cleared by admin';
    } else if (logLine.includes('Specific cache keys cleared')) {
        message = 'Specific cache keys cleared';
    }
    
    return {
        time: timestamp,
        message: message
    };
}

function showAlert(type, title, content = '') {
    const alertsContainer = document.querySelector('.card-body');
    const alertId = 'alert-' + Date.now();
    
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            <strong>${title}</strong>
            ${content ? '<div class="mt-2">' + content + '</div>' : ''}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertsContainer.insertAdjacentHTML('afterbegin', alertHTML);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alertElement = document.getElementById(alertId);
        if (alertElement) {
            new bootstrap.Alert(alertElement).close();
        }
    }, 5000);
}
</script>
@endpush