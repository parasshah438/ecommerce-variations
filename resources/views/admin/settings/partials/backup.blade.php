<div class="mb-4">
    <h5 class="mb-1">Backup & Restore Helpers</h5>
    <p class="text-muted mb-0">Backup your database and environment file before major changes.</p>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100 border">
            <div class="card-body">
                <h6><i class="fas fa-database text-primary me-2"></i>Database Backup</h6>
                <p class="text-muted small">Runs <code>php artisan backup:run --only-db</code> using Spatie Laravel Backup.</p>
                <button type="button" class="btn btn-primary" id="runDbBackupBtn">
                    <i class="fas fa-play me-1"></i> Run Database Backup
                </button>
                <div id="dbBackupResult" class="small mt-2 text-muted"></div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100 border">
            <div class="card-body">
                <h6><i class="fas fa-file-code text-warning me-2"></i>.env Backup</h6>
                <p class="text-muted small">Creates a timestamped copy in <code>storage/app/env-backups/</code> (also auto-created on every settings save).</p>
                <button type="button" class="btn btn-warning" id="backupEnvBtn">
                    <i class="fas fa-copy me-1"></i> Backup .env Now
                </button>
                <div id="envBackupResult" class="small mt-2 text-muted"></div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4 border">
    <div class="card-header">
        <h6 class="mb-0">Recent .env Backups</h6>
    </div>
    <div class="card-body p-0">
        @if(count($envBackups) > 0)
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>File</th>
                            <th>Size</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($envBackups as $backup)
                            <tr>
                                <td><code>{{ $backup['name'] }}</code></td>
                                <td>{{ $backup['size'] }}</td>
                                <td>{{ $backup['modified'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4 text-center text-muted">No .env backups yet. They are created automatically when you save settings.</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

document.getElementById('runDbBackupBtn')?.addEventListener('click', async function () {
    if (!confirm('Run database backup now?')) return;
    this.disabled = true;
    const el = document.getElementById('dbBackupResult');
    el.textContent = 'Running backup...';
    try {
        const res = await fetch('{{ route('admin.settings.database-backup') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
        const data = await res.json();
        el.textContent = data.message;
        el.className = 'small mt-2 ' + (data.success ? 'text-success' : 'text-danger');
    } catch (e) {
        el.textContent = e.message;
        el.className = 'small mt-2 text-danger';
    } finally {
        this.disabled = false;
    }
});

document.getElementById('backupEnvBtn')?.addEventListener('click', async function () {
    this.disabled = true;
    const el = document.getElementById('envBackupResult');
    el.textContent = 'Backing up...';
    try {
        const res = await fetch('{{ route('admin.settings.backup-env') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
        const data = await res.json();
        el.textContent = data.message + (data.file ? ' (' + data.file + ')' : '');
        el.className = 'small mt-2 ' + (data.success ? 'text-success' : 'text-danger');
        if (data.success) setTimeout(() => location.reload(), 1200);
    } catch (e) {
        el.textContent = e.message;
        el.className = 'small mt-2 text-danger';
    } finally {
        this.disabled = false;
    }
});
</script>
@endpush
