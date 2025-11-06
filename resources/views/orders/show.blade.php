@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="container">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Redirecting to the new order details page...
        </div>
    </div>
</div>

<script>
    // Redirect to new order details route
    window.location.href = '{{ route("order.details", request()->route("order")) }}';
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .alert {
        background: var(--sidebar-hover);
        border-color: var(--border-color);
        color: var(--text-primary);
    }
    
    .alert-info {
        background: rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.3);
        color: var(--text-primary);
    }
</style>
@endpush
