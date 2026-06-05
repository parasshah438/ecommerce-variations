<div class="mb-4">
    <h5 class="mb-1">Cache Management</h5>
    <p class="text-muted mb-0">Clear Laravel caches after updating .env settings. Driver: <strong>{{ $cacheStats['driver'] ?? 'unknown' }}</strong></p>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-icon primary"><i class="fas fa-database"></i></div>
            <div class="stats-value">{{ number_format($cacheStats['total_entries'] ?? 0) }}</div>
            <div class="stats-label">Cache Entries</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-icon success"><i class="fas fa-check"></i></div>
            <div class="stats-value">{{ number_format($cacheStats['active_entries'] ?? 0) }}</div>
            <div class="stats-label">Active</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-icon warning"><i class="fas fa-hourglass-half"></i></div>
            <div class="stats-value">{{ number_format($cacheStats['expired_entries'] ?? 0) }}</div>
            <div class="stats-label">Expired</div>
        </div>
    </div>
</div>

<div class="alert alert-info">
  <i class="fas fa-info-circle me-2"></i>After saving SMTP, payment, or database settings, run <strong>Clear Config Cache</strong> or <strong>Clear All</strong>.
  <span class="d-block small mt-1">Clears only — safe for local dev. Rebuilding route/config cache is not run from the browser (use CLI on production if needed).</span>
</div>

<div class="d-grid gap-2 col-md-6">
    @foreach([
        'application' => ['Clear Application Cache', 'btn-outline-primary'],
        'config' => ['Clear Config Cache', 'btn-outline-secondary'],
        'route' => ['Clear Route Cache', 'btn-outline-info'],
        'view' => ['Clear View Cache', 'btn-outline-success'],
        'all' => ['Clear All Caches', 'btn-danger'],
    ] as $type => [$label, $class])
        <button type="button" class="btn {{ $class }} cache-clear-btn" data-type="{{ $type }}">
            <i class="fas fa-broom me-1"></i> {{ $label }}
        </button>
    @endforeach
</div>

<div id="cacheResult" class="mt-3 small text-muted"></div>

@push('scripts')
<script>
document.querySelectorAll('.cache-clear-btn').forEach(btn => {
    btn.addEventListener('click', async function () {
        const type = this.dataset.type;
        if (type === 'all' && !confirm('Clear ALL caches?')) return;

        this.disabled = true;
        const resultEl = document.getElementById('cacheResult');
        resultEl.textContent = 'Working...';

        try {
            const res = await fetch('{{ route('admin.settings.clear-cache') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cache_type: type })
            });

            const raw = await res.text();
            let data;
            try {
                data = JSON.parse(raw);
            } catch (parseError) {
                throw new Error('Server returned an invalid response. Check storage/logs/laravel.log for details.');
            }

            if (!res.ok && !data.message) {
                throw new Error('Request failed with status ' + res.status);
            }

            resultEl.textContent = data.message || (data.success ? 'Done' : 'Failed');
            resultEl.className = 'mt-3 small ' + (data.success ? 'text-success' : 'text-danger');
        } catch (e) {
            resultEl.textContent = e.message;
            resultEl.className = 'mt-3 small text-danger';
        } finally {
            this.disabled = false;
        }
    });
});
</script>
@endpush
