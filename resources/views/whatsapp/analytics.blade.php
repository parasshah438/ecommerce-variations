@extends('layouts.app')

@section('title', 'WhatsApp Analytics')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">WhatsApp Analytics</h1>
                    <p class="text-muted">Track your messaging performance and statistics</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-primary" onclick="refreshData()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </button>
                        <a href="{{ route('whatsapp.reports.download') }}" class="btn btn-success">
                            <i class="bi bi-download me-1"></i> Export Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Messages (This Month)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalMessages">
                                0
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
                                Delivery Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="deliveryRate">
                                0%
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Contacts
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="activeContacts">
                                0
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fa-2x text-gray-300"></i>
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
                                Templates Used
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="templatesUsed">
                                0
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-file-text fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Messages Over Time Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Messages Over Time (Last 30 Days)</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="#" onclick="changeTimeframe('7days')">Last 7 Days</a>
                            <a class="dropdown-item" href="#" onclick="changeTimeframe('30days')">Last 30 Days</a>
                            <a class="dropdown-item" href="#" onclick="changeTimeframe('90days')">Last 90 Days</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="messagesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Status Pie Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Message Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2"><i class="bi bi-circle-fill text-success"></i> Delivered</span>
                        <span class="mr-2"><i class="bi bi-circle-fill text-primary"></i> Sent</span>
                        <span class="mr-2"><i class="bi bi-circle-fill text-danger"></i> Failed</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Message Types -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Message Types</h6>
                </div>
                <div class="card-body">
                    @if(isset($stats['messages_by_type']) && $stats['messages_by_type']->count() > 0)
                        @foreach($stats['messages_by_type'] as $type)
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-capitalize">{{ $type->message_type }}</span>
                                </div>
                                <div>
                                    <span class="badge bg-primary">{{ number_format($type->count) }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No message type data available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Performing Templates -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Performing Templates</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Template Name</th>
                                    <th>Category</th>
                                    <th>Usage Count</th>
                                    <th>Success Rate</th>
                                    <th>Last Used</th>
                                </tr>
                            </thead>
                            <tbody id="topTemplatesTable">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="recentActivityTable">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Loading recent activity...</td>
                                </tr>
                            </tbody>
                        </table>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let messagesChart;
let statusChart;

$(document).ready(function() {
    loadAnalyticsData();
    initializeCharts();
});

function loadAnalyticsData() {
    // Load summary statistics
    $.ajax({
        url: '{{ route("api.whatsapp.message.stats") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#totalMessages').text(data.total_messages || 0);
                $('#deliveryRate').text((data.delivery_rate || 0) + '%');
                $('#activeContacts').text(data.active_contacts || 0);
                $('#templatesUsed').text(data.templates_used || 0);
                
                updateCharts(data);
                updateTopTemplates(data.top_templates || []);
                updateRecentActivity(data.recent_activity || []);
            }
        },
        error: function() {
            console.error('Failed to load analytics data');
        }
    });
}

function initializeCharts() {
    // Messages Over Time Chart
    const ctx = document.getElementById('messagesChart');
    messagesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Messages Sent',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Status Distribution Chart
    const ctx2 = document.getElementById('statusChart');
    statusChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Delivered', 'Sent', 'Failed'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['#1cc88a', '#4e73df', '#e74a3b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function updateCharts(data) {
    // Update messages chart
    if (data.messages_by_day && messagesChart) {
        const labels = data.messages_by_day.map(item => item.date);
        const counts = data.messages_by_day.map(item => item.count);
        
        messagesChart.data.labels = labels;
        messagesChart.data.datasets[0].data = counts;
        messagesChart.update();
    }

    // Update status chart
    if (data.messages_by_status && statusChart) {
        const statusData = {
            delivered: 0,
            sent: 0,
            failed: 0
        };

        data.messages_by_status.forEach(item => {
            statusData[item.status] = item.count;
        });

        statusChart.data.datasets[0].data = [
            statusData.delivered,
            statusData.sent,
            statusData.failed
        ];
        statusChart.update();
    }
}

function updateTopTemplates(templates) {
    const tbody = $('#topTemplatesTable');
    if (templates.length > 0) {
        let html = '';
        templates.forEach(template => {
            html += `
                <tr>
                    <td>${template.name}</td>
                    <td><span class="badge bg-secondary">${template.category}</span></td>
                    <td>${template.usage_count}</td>
                    <td>${template.success_rate || 'N/A'}%</td>
                    <td>${template.last_used || 'Never'}</td>
                </tr>
            `;
        });
        tbody.html(html);
    } else {
        tbody.html('<tr><td colspan="5" class="text-center text-muted">No template data available</td></tr>');
    }
}

function updateRecentActivity(activities) {
    const tbody = $('#recentActivityTable');
    if (activities.length > 0) {
        let html = '';
        activities.forEach(activity => {
            html += `
                <tr>
                    <td>${activity.time}</td>
                    <td>${activity.action}</td>
                    <td>${activity.details}</td>
                    <td><span class="badge bg-${activity.status === 'success' ? 'success' : 'danger'}">${activity.status}</span></td>
                </tr>
            `;
        });
        tbody.html(html);
    } else {
        tbody.html('<tr><td colspan="4" class="text-center text-muted">No recent activity</td></tr>');
    }
}

function refreshData() {
    loadAnalyticsData();
}

function changeTimeframe(timeframe) {
    // This would reload data for the selected timeframe
    console.log('Changing timeframe to:', timeframe);
    loadAnalyticsData();
}
</script>
@endpush
@endsection