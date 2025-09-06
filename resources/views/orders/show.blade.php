@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Order #{{ $order->id ?? '' }}</h2>
  @if(isset($order))
    <p>Status: {{ ucfirst($order->status) }}</p>
    <p>Total: ₹{{ number_format($order->total,2) }}</p>
    <h4>Items</h4>
    <ul class="list-group">
      @foreach($order->items as $it)
        <li class="list-group-item">SKU: {{ $it->variation->sku ?? $it->product_variation_id }} — Qty: {{ $it->quantity }} — ₹{{ number_format($it->price,2) }}</li>
      @endforeach
    </ul>
  @else
    <p>Order not found.</p>
  @endif
</div>
@endsection
