@extends('admin.layout')

@section('title', 'Import Preview')
@section('page-title', 'Import Preview')
@section('page-description', 'Review validation results before importing')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <!-- Summary Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clipboard-check me-2"></i>
                    Summary — {{ $filename }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="mb-1">{{ $totalRows }}</h3>
                            <small class="text-muted">Total Rows</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 bg-success-subtle">
                            <h3 class="mb-1 text-success">{{ $validRows }}</h3>
                            <small class="text-muted">Valid Rows</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 {{ $invalidRows > 0 ? 'bg-danger-subtle' : '' }}">
                            <h3 class="mb-1 {{ $invalidRows > 0 ? 'text-danger' : '' }}">{{ $invalidRows }}</h3>
                            <small class="text-muted">Invalid Rows</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="mb-1">{{ count($headers) }}</h3>
                            <small class="text-muted">Columns Detected</small>
                        </div>
                    </div>
                </div>

                @if($invalidRows > 0)
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>{{ $invalidRows }} row(s)</strong> have validation errors and will be <strong>skipped</strong>.
                        Valid rows are still imported. You can download the full error report after the import runs.
                    </div>
                @else
                    <div class="alert alert-success mt-3 mb-0">
                        <i class="bi bi-check-circle me-2"></i>
                        All rows passed validation. Ready to import.
                    </div>
                @endif

                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <form action="{{ route('admin.products.import.execute') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="storage_path" value="{{ $storagePath }}">
                        <input type="hidden" name="filename" value="{{ $filename }}">
                        <button type="submit" class="btn btn-success"
                                onclick="return confirm('Start importing {{ $validRows }} valid product row(s)? Invalid rows will be skipped.');">
                            <i class="bi bi-rocket-takeoff me-1"></i>
                            Start Import ({{ $validRows }} valid)
                        </button>
                    </form>
                    <a href="{{ route('admin.products.import.form') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Upload
                    </a>
                </div>
            </div>
        </div>

        <!-- Preview Rows -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-table me-2"></i>
                    Row Preview
                    <small class="text-muted">(showing first {{ count($previewRows) }} of {{ $totalRows }} rows)</small>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-bordered table-striped mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Row</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Weight</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Variations</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewRows as $row)
                                <tr class="{{ !empty($row['errors']) ? 'table-danger' : '' }}">
                                    <td>{{ $row['number'] }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($row['name'], 50) }}</td>
                                    <td>{{ $row['price'] }}</td>
                                    <td>{{ $row['weight'] }}</td>
                                    <td>{{ $row['category'] ?: '-' }}</td>
                                    <td>{{ $row['brand'] ?: '-' }}</td>
                                    <td>
                                        @if(!empty($row['variations']))
                                            <span class="badge bg-info text-dark">{{ \Illuminate\Support\Str::limit($row['variations'], 40) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(empty($row['errors']))
                                            <span class="badge bg-success">Valid</span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                    title="{{ implode(' | ', $row['errors']) }}">
                                                <i class="bi bi-x-circle"></i> {{ count($row['errors']) }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
