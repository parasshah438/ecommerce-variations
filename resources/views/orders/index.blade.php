@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Your Orders</h2>
  <div class="list-group">
    @foreach(App\Models\Order::where('user_id', auth()->id())->latest()->get() as $order)
      <a href="{{ route('orders.show', $order->id) }}" class="list-group-item list-group-item-action">
        Order #{{ $order->id }} — ₹{{ number_format($order->total,2) }} — {{ ucfirst($order->status) }}
      </a>
    @endforeach
  </div>
</div>
@endsection
