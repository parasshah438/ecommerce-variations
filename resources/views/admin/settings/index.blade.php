@extends('admin.layout')

@section('title', 'Website Settings')
@section('page-title', 'Website / System Settings')
@section('page-description', 'Manage .env configuration tab-wise — changes are saved to your environment file')
@section('breadcrumb-section', 'Admin')
@section('breadcrumb-page', 'Settings')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="alert alert-warning">
    <i class="fas fa-shield-alt me-2"></i>
    <strong>Admin only.</strong> Settings are written directly to <code>.env</code>. A backup is created automatically before each save in <code>storage/app/env-backups/</code>.
</div>

<div class="card">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs card-header-tabs px-3 pt-2 flex-nowrap overflow-auto" role="tablist">
            @foreach($tabs as $tabKey => $tabMeta)
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $tab === $tabKey ? 'active' : '' }}"
                       href="{{ route('admin.settings.index', ['tab' => $tabKey]) }}">
                        <i class="fas {{ $tabMeta['icon'] ?? 'fa-cog' }} me-1"></i>
                        {{ $tabMeta['label'] }}
                    </a>
                </li>
            @endforeach
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab === 'cache' ? 'active' : '' }}"
                   href="{{ route('admin.settings.index', ['tab' => 'cache']) }}">
                    <i class="fas fa-bolt me-1"></i> Cache
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab === 'backup' ? 'active' : '' }}"
                   href="{{ route('admin.settings.index', ['tab' => 'backup']) }}">
                    <i class="fas fa-cloud-download-alt me-1"></i> Backup
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">
        @if($tab === 'cache')
            @include('admin.settings.partials.cache', ['cacheStats' => $cacheStats])
        @elseif($tab === 'backup')
            @include('admin.settings.partials.backup', ['envBackups' => $envBackups])
        @else
            @php $tabMeta = $tabs[$tab]; @endphp
            <div class="mb-4">
                <h5 class="mb-1">{{ $tabMeta['label'] }}</h5>
                <p class="text-muted mb-0">{{ $tabMeta['description'] }}</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update', $tab) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    @foreach($tabMeta['keys'] as $key => $field)
                        <div class="col-md-6">
                            <label for="{{ $key }}" class="form-label">
                                {{ $field['label'] }}
                                @if(!empty($field['sensitive']))
                                    <span class="badge bg-secondary ms-1">secret</span>
                                @endif
                            </label>

                            @if(($field['type'] ?? 'text') === 'select')
                                <select name="{{ $key }}" id="{{ $key }}" class="form-select @error($key) is-invalid @enderror">
                                    @foreach($field['options'] as $optValue => $optLabel)
                                        <option value="{{ $optValue }}" {{ old($key, $settings[$key] ?? '') == $optValue ? 'selected' : '' }}>
                                            {{ $optLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            @elseif(($field['type'] ?? '') === 'boolean')
                                @php $boolVal = old($key, $settings[$key] ?? 'false'); @endphp
                                <select name="{{ $key }}" id="{{ $key }}" class="form-select @error($key) is-invalid @enderror">
                                    <option value="1" {{ filter_var($boolVal, FILTER_VALIDATE_BOOLEAN) ? 'selected' : '' }}>Yes / True</option>
                                    <option value="0" {{ !filter_var($boolVal, FILTER_VALIDATE_BOOLEAN) ? 'selected' : '' }}>No / False</option>
                                </select>
                            @elseif(($field['type'] ?? '') === 'password')
                                <input type="password" name="{{ $key }}" id="{{ $key }}"
                                       class="form-control @error($key) is-invalid @enderror"
                                       placeholder="{{ !empty($settings[$key . '_masked']) ? 'Current: ' . $settings[$key . '_masked'] . ' (leave blank to keep)' : 'Enter value' }}"
                                       autocomplete="new-password">
                                @if(!empty($settings[$key . '_masked']))
                                    <div class="form-text">Leave empty to keep the current value.</div>
                                @endif
                            @else
                                <input type="{{ $field['type'] ?? 'text' }}" name="{{ $key }}" id="{{ $key }}"
                                       value="{{ old($key, $settings[$key] ?? '') }}"
                                       class="form-control @error($key) is-invalid @enderror">
                            @endif

                            @error($key)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>

                @if($tab === 'general' && !empty($readonly['APP_KEY']))
                    <div class="row g-3 mt-2">
                        <div class="col-md-12">
                            <label class="form-label">APP_KEY <span class="badge bg-secondary">read-only</span></label>
                            <input type="text" class="form-control" value="{{ \Illuminate\Support\Str::limit($readonly['APP_KEY'], 20) }}••••••••" readonly disabled>
                            <div class="form-text">Application key cannot be changed from this panel (security).</div>
                        </div>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save to .env
                    </button>

                    @if($tab === 'mail')
                        <button type="button" class="btn btn-outline-info" id="testMailBtn">
                            <i class="fas fa-paper-plane me-1"></i> Send Test Email
                        </button>
                    @endif

                    @if($tab === 'database')
                        <button type="button" class="btn btn-outline-secondary" id="testDbBtn">
                            <i class="fas fa-plug me-1"></i> Test Connection
                        </button>
                    @endif
                </div>
            </form>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

function showToast(type, message) {
    alert((type === 'success' ? '✓ ' : '✗ ') + message);
}

@if($tab === 'mail')
document.getElementById('testMailBtn')?.addEventListener('click', async function () {
    const email = prompt('Send test email to:', '{{ auth()->user()->email }}');
    if (!email) return;

    this.disabled = true;
    try {
        const res = await fetch('{{ route('admin.settings.test-mail') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ test_email: email })
        });
        const data = await res.json();
        showToast(data.success ? 'success' : 'error', data.message);
    } catch (e) {
        showToast('error', e.message);
    } finally {
        this.disabled = false;
    }
});
@endif

@if($tab === 'database')
document.getElementById('testDbBtn')?.addEventListener('click', async function () {
    const form = this.closest('form');
    const payload = {};
    ['DB_CONNECTION','DB_HOST','DB_PORT','DB_DATABASE','DB_USERNAME','DB_PASSWORD'].forEach(name => {
        const el = form.querySelector(`[name="${name}"]`);
        if (el) payload[name] = el.value;
    });

    this.disabled = true;
    try {
        const res = await fetch('{{ route('admin.settings.test-database') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        showToast(data.success ? 'success' : 'error', data.message);
    } catch (e) {
        showToast('error', e.message);
    } finally {
        this.disabled = false;
    }
});
@endif
</script>
@endpush
