@extends('layouts.app')

@section('content')
<div class="container">
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Redirecting to the new order details page...
    </div>
</div>

<script>
    // Redirect to new order details route
    window.location.href = '{{ route("order.details", request()->route("order")) }}';
</script>
@endsection
