@extends('admin.layout')

@section('title', 'Bulk Import Products')
@section('page-title', 'Bulk Import Products')
@section('page-description', 'Upload a CSV or Excel file to add multiple products at once')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Upload Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-upload me-2"></i>
                    Upload File
                </h5>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <h6 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Import Failed</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.products.import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="import_file" class="form-label">Choose File (CSV, TSV or Excel)</label>
                        <input type="file" class="form-control" id="import_file" name="import_file"
                               accept=".csv,.txt,.xlsx,.xls" required>
                        <div class="form-text">
                            <strong>Supported formats:</strong> .csv, .txt (comma/tab), .xlsx, .xls &nbsp;|&nbsp;
                            <strong>Max size:</strong> 20MB &nbsp;|&nbsp;
                            <strong>Up to 500 rows per batch</strong> — larger files are split and queued automatically.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>
                        Preview & Validate
                    </button>
                </form>
            </div>
        </div>

        <!-- Template Help Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Template Guide
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-secondary alert-permanent mb-3">
                    <i class="bi bi-download me-2"></i>
                    <strong>Prefer a ready-made file?</strong> Download a sample and fill it in:
                    <div class="d-flex gap-2 mt-2 flex-wrap">
                        <a href="{{ route('admin.products.import.sample', 'csv') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-filetype-csv me-1"></i> Sample .csv
                        </a>
                        <a href="{{ route('admin.products.import.sample', 'txt') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-filetype-txt me-1"></i> Sample .txt (tab)
                        </a>
                        <a href="{{ route('admin.products.import.sample', 'xlsx') }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-file-earmark-excel me-1"></i> Sample .xlsx
                        </a>
                        <a href="{{ route('admin.products.import.sample', 'xls') }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-file-earmark-excel me-1"></i> Sample .xls
                        </a>
                    </div>
                    <small class="text-muted d-block mt-2">
                        Each sample contains: 1 simple product, 1 product with variations, 1 minimal row,
                        and 1 intentionally invalid row (to see how errors are reported).
                    </small>
                </div>

                <div class="alert alert-info mb-3">
                    <i class="bi bi-lightbulb me-2"></i>
                    <strong>Required columns:</strong>
                    <code>name</code>, <code>description</code>, <code>price</code>, <code>weight</code>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Column</th>
                                <th>Required</th>
                                <th>Example</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><code>name</code></td><td>✅</td><td>Men's Cotton T-Shirt</td><td>Product title</td></tr>
                            <tr><td><code>description</code></td><td>✅</td><td>Premium 100% cotton...</td><td>Full description</td></tr>
                            <tr><td><code>price</code></td><td>✅</td><td>999</td><td>Selling price in ₹</td></tr>
                            <tr><td><code>weight</code></td><td>✅</td><td>200</td><td>Weight in grams</td></tr>
                            <tr><td><code>short_description</code></td><td></td><td>Soft cotton tee</td><td>Short blurb for cards (optional)</td></tr>
                            <tr><td><code>long_description</code></td><td></td><td>Detailed fabric care...</td><td>Extended detail section (optional)</td></tr>
                            <tr><td><code>meta_title</code> / <code>meta_description</code> / <code>meta_keywords</code></td><td></td><td>Cotton T-Shirt / ... / cotton,tee</td><td>SEO fields (optional)</td></tr>
                            <tr><td><code>video_url</code></td><td></td><td>https://youtu.be/...</td><td>Product video URL (optional)</td></tr>
                            <tr><td><code>country_of_origin</code></td><td></td><td>India</td><td>Defaults to existing value (optional)</td></tr>
                            <tr><td><code>manufacturer</code></td><td></td><td>NS Kurti Pvt Ltd</td><td>Manufacturer name (optional)</td></tr>
                            <tr><td><code>category</code></td><td></td><td>Fashion</td><td>Matches by name (optional)</td></tr>
                            <tr><td><code>brand</code></td><td></td><td>Nike</td><td>Matches by name (optional)</td></tr>
                            <tr><td><code>mrp</code></td><td></td><td>1299</td><td>Defaults to price</td></tr>
                            <tr><td><code>sku</code></td><td></td><td>TS-001</td><td>Auto-generated if empty</td></tr>
                            <tr><td><code>stock</code></td><td></td><td>50</td><td>Simple product stock</td></tr>
                            <tr><td><code>length</code> / <code>width</code> / <code>height</code></td><td></td><td>30 / 20 / 2</td><td>Shipping dimensions (cm)</td></tr>
                            <tr><td><code>variations</code></td><td></td><td>Color:Red,Size:M;Color:Blue,Size:L</td><td>Semicolon = variation. Comma = attribute pair. Attribute names must exist.</td></tr>
                            <tr><td><code>variation_prices</code></td><td></td><td>1049;1049</td><td>One per variation, semicolon-separated</td></tr>
                            <tr><td><code>variation_stock</code></td><td></td><td>10;15</td><td>One per variation, semicolon-separated</td></tr>
                            <tr><td><code>variation_sku</code></td><td></td><td>TS-R-M;TS-B-L</td><td>One per variation, semicolon-separated</td></tr>
                            <tr><td><code>image_urls</code></td><td></td><td>https://.../a.jpg|https://.../b.jpg</td><td>Pipe-separated URLs, downloaded on import</td></tr>
                            <tr><td><code>cover_image</code></td><td></td><td>https://.../cover.jpg</td><td>Dedicated cover image URL</td></tr>
                            <tr><td><code>hsn_code</code></td><td></td><td>61091000</td><td>GST HSN (optional)</td></tr>
                            <tr><td><code>featured</code></td><td></td><td>yes</td><td>yes/no (optional)</td></tr>
                            <tr><td><code>active</code></td><td></td><td>yes</td><td>yes/no, default yes</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Import Batches -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Imports
                </h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()" title="Refresh progress">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
            <div class="card-body p-0">
                @if($recentBatches->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">No imports yet.</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($recentBatches as $batch)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="me-2">
                                        <div class="small text-muted text-truncate" style="max-width: 180px;" title="{{ $batch->original_filename }}">
                                            {{ $batch->original_filename }}
                                        </div>
                                        <div class="fw-semibold small">
                                            {{ $batch->total_rows }} rows
                                            <span class="text-muted">·</span>
                                            {{ $batch->created_at->diffForHumans() }}
                                        </div>
                                        <span class="badge bg-{{ \Illuminate\Support\Str::startsWith($batch->status, ['validat']) ? 'info' : ($batch->status === 'completed' ? 'success' : ($batch->status === 'failed' ? 'danger' : 'warning')) }}">
                                            {{ ucfirst($batch->status) }}
                                        </span>
                                        @if($batch->invalid_rows > 0 || ($batch->import_errors && count($batch->import_errors) > 0))
                                            <a href="{{ route('admin.products.import.log', $batch) }}" class="btn btn-sm btn-link p-0 ms-1 text-danger">
                                                <i class="bi bi-download"></i> Errors
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                @if($batch->status === 'importing' || $batch->status === 'validating')
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated
                                            {{ $batch->status === 'validating' ? 'bg-info' : 'bg-primary' }}"
                                            style="width: {{ $batch->progress_percentage }}%"></div>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        {{ $batch->processed_rows }}/{{ $batch->total_rows }} processed
                                        @if($batch->failed_rows > 0)
                                            · <span class="text-danger">{{ $batch->failed_rows }} failed</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh progress every 8 seconds while imports are running
    let hasActiveImport = document.querySelector('.progress-bar-animated') !== null;
    if (hasActiveImport) {
        setInterval(() => {
            fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .catch(() => location.reload());
        }, 8000);
    }
</script>
@endpush
