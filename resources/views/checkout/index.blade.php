@extends('layouts.frontend')

@section('title', 'Checkout - ' . config('app.name'))

@section('styles')
<style>
    .checkout-container {
        min-height: 80vh;
        padding: 2rem 0;
    }
    
    .step-indicator {
        margin-bottom: 2rem;
    }
    
    .step {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        font-weight: 600;
        margin: 0 auto;
        position: relative;
    }
    
    .step.active {
        background: var(--primary-color);
        color: white;
    }
    
    .step.completed {
        background: var(--success-color);
        color: white;
    }
    
    .step-line {
        height: 2px;
        background: #e9ecef;
        position: relative;
        top: -21px;
        z-index: -1;
    }
    
    .step-line.active {
        background: var(--primary-color);
    }
    
    .order-summary {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        position: sticky;
        top: 20px;
    }
    
    .product-item {
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 0;
    }
    
    .product-item:last-child {
        border-bottom: none;
    }
    
    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .address-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .address-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    }
    
    .address-card.selected {
        border-color: var(--primary-color);
        background: rgba(13, 110, 253, 0.05);
    }
    
    .payment-method {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }
    
    .payment-method:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    }
    
    .payment-method.selected {
        border-color: var(--primary-color);
        background: rgba(13, 110, 253, 0.05);
    }
    
    .form-floating label {
        color: #6c757d;
    }
    
    .btn-place-order {
        background: linear-gradient(135deg, var(--primary-color), #0a58ca);
        border: none;
        padding: 0.875rem 2rem;
        font-weight: 600;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-place-order:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
    }
    
    .secure-checkout {
        background: rgba(25, 135, 84, 0.1);
        border: 1px solid rgba(25, 135, 84, 0.2);
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .quantity-badge {
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        top: -8px;
        right: -8px;
    }
</style>
@endsection

@section('content')
<div class="checkout-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('welcome') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cart.index') }}" class="text-decoration-none">Cart</a></li>
                <li class="breadcrumb-item active" aria-current="page">Checkout</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold mb-3">Secure Checkout</h1>
            <p class="lead text-muted">Review your order and complete your purchase</p>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="step completed">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <div class="mt-2 fw-semibold text-success">Cart</div>
                </div>
                <div class="col-md-4">
                    <div class="step-line active"></div>
                    <div class="step active">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <div class="mt-2 fw-semibold text-primary">Checkout</div>
                </div>
                <div class="col-md-4">
                    <div class="step-line"></div>
                    <div class="step">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="mt-2 fw-semibold text-muted">Complete</div>
                </div>
            </div>
        </div>

<!-- Razorpay SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Main Checkout Content -->
        <form action="{{ route('checkout.place_order') }}" method="POST" id="checkoutForm">
            @csrf
            <div class="row">
                <!-- Left Column - Checkout Forms -->
                <div class="col-lg-8">
                    <!-- Delivery Address Section -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-geo-alt text-primary me-2"></i>
                                Delivery Address
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(auth()->user()->addresses && auth()->user()->addresses->count() > 0)
                                <!-- Saved Addresses -->
                                <h6 class="fw-semibold mb-3">Choose from saved addresses:</h6>
                                <div class="row g-3 mb-4">
                                    @foreach(auth()->user()->addresses as $address)
                                        <div class="col-md-6">
                                            <div class="address-card position-relative {{ $address->is_default ? 'selected' : '' }}" onclick="selectAddress({{ $address->id }})">
                                                <input type="radio" name="address_id" value="{{ $address->id }}" 
                                                       class="form-check-input" id="address_{{ $address->id }}" 
                                                       {{ $address->is_default ? 'checked' : '' }} hidden>
                                                
                                                <!-- Address Header -->
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-{{ $address->type === 'home' ? 'primary' : ($address->type === 'work' ? 'success' : 'secondary') }}">
                                                            <i class="{{ $address->typeIcon }} me-1"></i>{{ $address->typeLabel }}
                                                        </span>
                                                        @if($address->is_default)
                                                            <span class="badge bg-warning text-dark">
                                                                <i class="bi bi-star-fill me-1"></i>Default
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <i class="bi bi-check-circle-fill text-primary address-check" style="display: none;"></i>
                                                        <!-- Action Buttons -->
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                    onclick="event.stopPropagation(); editAddress({{ $address->id }})" 
                                                                    title="Edit Address">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </button>
                                                            @if(!$address->is_default)
                                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                        onclick="event.stopPropagation(); setDefaultAddress({{ $address->id }})" 
                                                                        title="Set as Default">
                                                                    <i class="bi bi-star"></i>
                                                                </button>
                                                            @endif
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="event.stopPropagation(); deleteAddress({{ $address->id }})" 
                                                                    title="Delete Address">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Address Details -->
                                                <h6 class="fw-semibold mb-1">{{ $address->name }}</h6>
                                                <div class="small text-muted mb-2">
                                                    <i class="bi bi-telephone me-1"></i>{{ $address->phone }}
                                                    @if($address->alternate_phone)
                                                        <br><i class="bi bi-telephone-plus me-1"></i>{{ $address->alternate_phone }}
                                                    @endif
                                                </div>
                                                
                                                <p class="text-muted mb-1 small">
                                                    {{ $address->address_line }}
                                                    @if($address->landmark)
                                                        <br><i class="bi bi-geo-alt me-1"></i>Near {{ $address->landmark }}
                                                    @endif
                                                    <br>{{ $address->city }}, {{ $address->state }} {{ $address->zip }}
                                                </p>
                                                
                                                @if($address->delivery_instructions)
                                                    <div class="small text-info">
                                                        <i class="bi bi-info-circle me-1"></i>{{ Str::limit($address->delivery_instructions, 50) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="text-center mb-3">
                                    <button type="button" class="btn btn-outline-primary" onclick="toggleNewAddress()">
                                        <i class="bi bi-plus-circle me-1"></i>Add New Address
                                    </button>
                                </div>
                            @endif

                            <!-- New Address Form -->
                            <div id="newAddressForm" class="{{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'd-none' : '' }}">
                                <h6 class="fw-semibold mb-3">{{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'Add new address:' : 'Enter delivery address:' }}</h6>
                                <div class="row g-3">
                                    <!-- Basic Information -->
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="{{ old('name', auth()->user()->name) }}" 
                                                   {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : 'required' }}>
                                            <label for="name">Full Name *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" id="type" name="type" 
                                                    {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : 'required' }}>
                                                <option value="home" {{ old('type') == 'home' ? 'selected' : '' }}>Home</option>
                                                <option value="work" {{ old('type') == 'work' ? 'selected' : '' }}>Work</option>
                                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                            <label for="type">Address Type *</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Phone Numbers -->
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="{{ old('phone', auth()->user()->mobile) }}" 
                                                   {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : 'required' }}>
                                            <label for="phone">Phone Number *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="alternate_phone" name="alternate_phone" 
                                                   value="{{ old('alternate_phone') }}" 
                                                   {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : '' }}>
                                            <label for="alternate_phone">Alternate Phone</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Address Details -->
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="address_line" name="address_line" 
                                                      style="height: 100px" {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : 'required' }}>{{ old('address_line') }}</textarea>
                                            <label for="address_line">Address Line *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="landmark" name="landmark" 
                                                   value="{{ old('landmark') }}" 
                                                   {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : '' }}>
                                            <label for="landmark">Landmark</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="city" name="city" 
                                                   value="{{ old('city') }}" {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : 'required' }}>
                                            <label for="city">City *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="state" name="state" 
                                                   value="{{ old('state') }}" {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : 'required' }}>
                                            <label for="state">State *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="zip" name="zip" 
                                                   value="{{ old('zip') }}" {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : 'required' }}>
                                            <label for="zip">PIN Code *</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Additional Information -->
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="delivery_instructions" name="delivery_instructions" 
                                                      style="height: 80px" {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : '' }}>{{ old('delivery_instructions') }}</textarea>
                                            <label for="delivery_instructions">Delivery Instructions</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Default Address Checkbox -->
                                    @if(auth()->user()->addresses && auth()->user()->addresses->count() > 0)
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" 
                                                   {{ old('is_default') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_default">
                                                Set as default address
                                            </label>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Section -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-credit-card text-primary me-2"></i>
                                Payment Method
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="payment-method selected" onclick="selectPaymentMethod('cod')">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="payment_method" value="cod" class="form-check-input me-3" checked>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-cash-coin text-success fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-1 fw-semibold">Cash on Delivery</h6>
                                                <p class="text-muted mb-0 small">Pay when your order is delivered</p>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="badge bg-success">Recommended</span>
                                </div>
                            </div>

                            <div class="payment-method" onclick="selectPaymentMethod('online')">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="payment_method" value="online" class="form-check-input me-3">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-credit-card text-primary fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-1 fw-semibold">Online Payment</h6>
                                                <p class="text-muted mb-0 small">UPI, Card, Net Banking via Razorpay</p>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="badge bg-success">Secure</span>
                                </div>
                            </div>

                            <div class="secure-checkout">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-shield-check text-success me-2"></i>
                                    <small class="text-success fw-semibold">Your payment information is secure and encrypted</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-receipt text-primary me-2"></i>
                            Order Summary
                        </h5>

                        <!-- Cart Items -->
                        <div id="cart-items">
                            @php
                                $cart = auth()->user()->cart;
                                $total = 0;
                            @endphp

                            @if($cart && $cart->items->count() > 0)
                                @foreach($cart->items as $item)
                                    @php $total += $item->price * $item->quantity; @endphp
                                    <div class="product-item">
                                        <div class="d-flex align-items-center">
                                            <div class="position-relative me-3">
                                                @if($item->variation->images->first())
                                                    <img src="{{ asset('storage/' . $item->variation->images->first()->image_path) }}" 
                                                         alt="{{ $item->variation->product->name }}" class="product-image">
                                                @else
                                                    <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                                        <i class="bi bi-image text-muted"></i>
                                                    </div>
                                                @endif
                                                <span class="quantity-badge">{{ $item->quantity }}</span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-semibold">{{ $item->variation->product->name }}</h6>
                                                <p class="text-muted mb-1 small">SKU: {{ $item->variation->sku }}</p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-semibold text-primary">₹{{ number_format($item->price * $item->quantity, 2) }}</span>
                                                    <small class="text-muted">₹{{ number_format($item->price, 2) }} × {{ $item->quantity }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-cart-x fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">Your cart is empty</p>
                                    <a href="{{ route('products.index') }}" class="btn btn-primary">Continue Shopping</a>
                                </div>
                            @endif
                        </div>

                        @if($cart && $cart->items->count() > 0)
                            <!-- Order Total -->
                            <div class="border-top pt-3 mt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal ({{ $cartSummary['items'] ?? 0 }} items)</span>
                                    <span>₹{{ number_format($cartSummary['subtotal'] ?? 0, 2) }}</span>
                                </div>
                                @if(isset($cartSummary['coupon']) && $cartSummary['coupon'])
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Coupon Discount ({{ $cartSummary['coupon']['code'] }})</span>
                                    <span>-₹{{ number_format($cartSummary['discount_amount'] ?? 0, 2) }}</span>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Shipping</span>
                                    <span class="text-success">{{ ($cartSummary['shipping_cost'] ?? 0) > 0 ? '₹' . number_format($cartSummary['shipping_cost'], 2) : 'Free' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax (GST)</span>
                                    <span>₹{{ number_format($cartSummary['tax_amount'] ?? 0, 2) }}</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="fw-bold">Total</h6>
                                    <h6 class="fw-bold text-primary">₹{{ number_format($cartSummary['total'] ?? 0, 2) }}</h6>
                                </div>

                                <!-- Place Order Button -->
                                <button type="submit" class="btn btn-place-order w-100 text-white">
                                    <i class="bi bi-lock me-2"></i>
                                    Place Order
                                </button>

                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="bi bi-shield-check me-1"></i>
                                        Secure 256-bit SSL encryption
                                    </small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Address Modal -->
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAddressModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Address
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAddressForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_address_id" name="address_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                                <label for="edit_name">Full Name *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="edit_type" name="type" required>
                                    <option value="home">Home</option>
                                    <option value="work">Work</option>
                                    <option value="other">Other</option>
                                </select>
                                <label for="edit_type">Address Type *</label>
                            </div>
                        </div>
                        
                        <!-- Phone Numbers -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                                <label for="edit_phone">Phone Number *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="edit_alternate_phone" name="alternate_phone">
                                <label for="edit_alternate_phone">Alternate Phone</label>
                            </div>
                        </div>
                        
                        <!-- Address Details -->
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" id="edit_address_line" name="address_line" style="height: 100px" required></textarea>
                                <label for="edit_address_line">Address Line *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="edit_landmark" name="landmark">
                                <label for="edit_landmark">Landmark</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="edit_city" name="city" required>
                                <label for="edit_city">City *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="edit_state" name="state" required>
                                <label for="edit_state">State *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="edit_zip" name="zip" required>
                                <label for="edit_zip">PIN Code *</label>
                            </div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" id="edit_delivery_instructions" name="delivery_instructions" style="height: 80px"></textarea>
                                <label for="edit_delivery_instructions">Delivery Instructions</label>
                            </div>
                        </div>
                        
                        <!-- Default Address Checkbox -->
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_default" name="is_default" value="1">
                                <label class="form-check-label" for="edit_is_default">
                                    Set as default address
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateAddress()">
                        <i class="bi bi-check-lg me-1"></i>Update Address
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Address Selection
function selectAddress(addressId) {
    // Remove selected class from all address cards
    document.querySelectorAll('.address-card').forEach(card => {
        card.classList.remove('selected');
        card.querySelector('.address-check').style.display = 'none';
    });
    
    // Add selected class to clicked card
    event.currentTarget.classList.add('selected');
    event.currentTarget.querySelector('.address-check').style.display = 'block';
    
    // Check the radio button
    document.getElementById(`address_${addressId}`).checked = true;
    
    // Hide new address form
    document.getElementById('newAddressForm').classList.add('d-none');
    
    // Clear required attributes from new address form
    clearNewAddressRequirements();
}

function toggleNewAddress() {
    const form = document.getElementById('newAddressForm');
    const isHidden = form.classList.contains('d-none');
    
    if (isHidden) {
        form.classList.remove('d-none');
        // Remove selection from saved addresses
        document.querySelectorAll('.address-card').forEach(card => {
            card.classList.remove('selected');
            card.querySelector('.address-check').style.display = 'none';
        });
        document.querySelectorAll('input[name="address_id"]').forEach(radio => {
            radio.checked = false;
        });
        // Add required attributes to new address form
        addNewAddressRequirements();
    } else {
        form.classList.add('d-none');
        clearNewAddressRequirements();
        // Clear form values when hiding
        clearNewAddressForm();
    }
}

function addNewAddressRequirements() {
    document.querySelectorAll('#newAddressForm input, #newAddressForm textarea').forEach(input => {
        input.required = true;
        input.disabled = false; // Enable fields when showing new address form
    });
}

function clearNewAddressRequirements() {
    document.querySelectorAll('#newAddressForm input, #newAddressForm textarea').forEach(input => {
        input.required = false;
        input.disabled = true; // Disable fields so they won't be submitted
    });
}

function clearNewAddressForm() {
    document.querySelectorAll('#newAddressForm input, #newAddressForm textarea').forEach(input => {
        input.value = '';
        input.disabled = true;
    });
}

function selectAddress(addressId) {
    // Hide new address form and disable its fields
    const newAddressForm = document.getElementById('newAddressForm');
    newAddressForm.classList.add('d-none');
    clearNewAddressRequirements();
    
    // Continue with existing address selection logic
    document.querySelectorAll('.address-card').forEach(card => {
        card.classList.remove('selected');
        card.querySelector('.address-check').style.display = 'none';
    });
    
    // Select the clicked address
    const selectedCard = event.currentTarget;
    selectedCard.classList.add('selected');
    selectedCard.querySelector('.address-check').style.display = 'block';
    
    // Check the radio button
    document.querySelector(`input[value="${addressId}"]`).checked = true;
}

// Payment Method Selection
function selectPaymentMethod(method) {
    // Remove selected class from all payment methods
    document.querySelectorAll('.payment-method').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selected class to clicked method
    event.currentTarget.classList.add('selected');
    
    // Check the radio button
    document.querySelector(`input[value="${method}"]`).checked = true;
}

// Form Validation
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Always prevent default form submission
    
    const addressSelected = document.querySelector('input[name="address_id"]:checked');
    const newAddressVisible = !document.getElementById('newAddressForm').classList.contains('d-none');
    
    console.log('Form submit - Address selected:', addressSelected);
    console.log('Form submit - New address visible:', newAddressVisible);
    
    if (!addressSelected && !newAddressVisible) {
        alert('Please select a delivery address or add a new one.');
        return false;
    }
    
    if (newAddressVisible) {
        const requiredFields = document.querySelectorAll('#newAddressForm input[required], #newAddressForm textarea[required]');
        let allFilled = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                allFilled = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!allFilled) {
            alert('Please fill all required address fields.');
            return false;
        }
    }
    
    // Check payment method selection
    const paymentMethodSelected = document.querySelector('input[name="payment_method"]:checked');
    console.log('Form submit - Payment method selected:', paymentMethodSelected);
    
    if (!paymentMethodSelected) {
        alert('Please select a payment method.');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    
    // Handle payment based on selected method
    if (paymentMethodSelected.value === 'cod') {
        // COD - Submit form normally
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing COD Order...';
        
        // Create FormData and submit via fetch
        const formData = new FormData(this);
        
        fetch('{{ route("checkout.place_order") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                return response.text();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Place Order';
        });
    } else if (paymentMethodSelected.value === 'online') {
        // Online payment via Razorpay
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Preparing Payment...';
        
        // Create Razorpay order first
        const formData = new FormData(this);
        
        fetch('{{ route("checkout.razorpay.create_order") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Initialize Razorpay payment
                initiateRazorpayPayment(data);
            } else {
                throw new Error(data.error || 'Failed to create payment order');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to initialize payment: ' + error.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Place Order';
        });
    }
});

// Function to initiate Razorpay payment
function initiateRazorpayPayment(orderData) {
    const options = {
        key: orderData.razorpay_config.key,
        amount: orderData.amount,
        currency: orderData.currency,
        name: orderData.razorpay_config.name,
        description: orderData.razorpay_config.description,
        image: orderData.razorpay_config.image,
        order_id: orderData.razorpay_order_id,
        handler: function (response) {
            // Payment successful - verify on server
            verifyRazorpayPayment(response, orderData.order_id);
        },
        prefill: {
            name: '{{ auth()->user()->name ?? "" }}',
            email: '{{ auth()->user()->email ?? "" }}',
            contact: '{{ auth()->user()->phone ?? "" }}'
        },
        theme: {
            color: orderData.razorpay_config.theme.color
        },
        modal: {
            ondismiss: function() {
                // Payment cancelled
                handlePaymentCancellation(orderData.order_id);
            }
        }
    };
    
    const rzp = new Razorpay(options);
    rzp.on('payment.failed', function (response) {
        // Payment failed
        handlePaymentFailure(response.error, orderData.order_id);
    });
    
    rzp.open();
}

// Function to verify Razorpay payment
function verifyRazorpayPayment(paymentResponse, orderId) {
    const formData = new FormData();
    formData.append('razorpay_order_id', paymentResponse.razorpay_order_id);
    formData.append('razorpay_payment_id', paymentResponse.razorpay_payment_id);
    formData.append('razorpay_signature', paymentResponse.razorpay_signature);
    formData.append('order_id', orderId);
    
    fetch('{{ route("checkout.razorpay.verify_payment") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to success page
            window.location.href = data.redirect_url;
        } else {
            throw new Error(data.error || 'Payment verification failed');
        }
    })
    .catch(error => {
        console.error('Payment verification error:', error);
        alert('Payment verification failed: ' + error.message);
        // Optionally redirect to checkout page
        window.location.reload();
    });
}

// Function to handle payment failure
function handlePaymentFailure(error, orderId) {
    console.error('Payment failed:', error);
    
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('error', JSON.stringify(error));
    
    fetch('{{ route("checkout.razorpay.payment_failed") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message || 'Payment failed. Please try again.');
        if (data.redirect_url) {
            window.location.href = data.redirect_url;
        } else {
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error handling payment failure:', error);
        alert('Payment failed. Please try again.');
        window.location.reload();
    });
}

// Function to handle payment cancellation
function handlePaymentCancellation(orderId) {
    console.log('Payment cancelled by user');
    
    // Re-enable the submit button
    const submitBtn = document.querySelector('button[type="submit"]');
    submitBtn.disabled = false;
    submitBtn.innerHTML = 'Place Order';
    
    alert('Payment cancelled. You can try again.');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // If no saved addresses, show new address form
    const savedAddresses = document.querySelectorAll('.address-card').length;
    if (savedAddresses === 0) {
        addNewAddressRequirements();
    }
    
    // Show check icon for default address on page load
    const defaultAddressCard = document.querySelector('.address-card.selected');
    if (defaultAddressCard) {
        const checkIcon = defaultAddressCard.querySelector('.address-check');
        if (checkIcon) {
            checkIcon.style.display = 'block';
        }
    }
});

// Address Management Functions
function editAddress(addressId) {
    // Get address data via AJAX
    fetch(`/address/${addressId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(address => {
            console.log('Address data loaded:', address);
            // Populate edit modal
            fillEditModal(address);
            // Show modal
            const editModal = new bootstrap.Modal(document.getElementById('editAddressModal'));
            editModal.show();
        })
        .catch(error => {
            console.error('Error fetching address:', error);
            toastr.error('Failed to load address details: ' + error.message);
        });
}

function deleteAddress(addressId) {
    if (confirm('Are you sure you want to delete this address?')) {
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch(`/address/${addressId}`, {
            method: 'POST', // Use POST with method spoofing
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                // Delay reload to allow user to read the message
                setTimeout(() => {
                    location.reload();
                }, 2000); // 2 seconds delay
            } else {
                toastr.error(data.message || 'Failed to delete address');
            }
        })
        .catch(error => {
            console.error('Error deleting address:', error);
            toastr.error('Failed to delete address');
        });
    }
}

function setDefaultAddress(addressId) {
    fetch(`/address/${addressId}/set-default`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            // Delay reload to allow user to read the message
            setTimeout(() => {
                location.reload();
            }, 2000); // 2 seconds delay
        } else {
            toastr.error(data.message || 'Failed to set default address');
        }
    })
    .catch(error => {
        console.error('Error setting default address:', error);
        toastr.error('Failed to set default address');
    });
}

function fillEditModal(address) {
    document.getElementById('edit_address_id').value = address.id;
    document.getElementById('edit_name').value = address.name;
    document.getElementById('edit_phone').value = address.phone;
    document.getElementById('edit_alternate_phone').value = address.alternate_phone || '';
    document.getElementById('edit_type').value = address.type;
    document.getElementById('edit_address_line').value = address.address_line;
    document.getElementById('edit_landmark').value = address.landmark || '';
    document.getElementById('edit_city').value = address.city;
    document.getElementById('edit_state').value = address.state;
    document.getElementById('edit_zip').value = address.zip;
    document.getElementById('edit_delivery_instructions').value = address.delivery_instructions || '';
    document.getElementById('edit_is_default').checked = address.is_default;
}

function updateAddress() {
    const form = document.getElementById('editAddressForm');
    const formData = new FormData(form);
    const addressId = document.getElementById('edit_address_id').value;
    
    // Add method spoofing for PUT request
    formData.append('_method', 'PUT');
    
    console.log('Updating address:', addressId);
    
    fetch(`/address/${addressId}`, {
        method: 'POST', // Use POST with method spoofing
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers.get('content-type'));
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, get text to see what was returned
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response');
            });
        }
    })
    .then(data => {
        console.log('Update response:', data);
        if (data.success) {
            // Close modal first
            const editModal = bootstrap.Modal.getInstance(document.getElementById('editAddressModal'));
            if (editModal) {
                editModal.hide();
            }
            
            // Show success message
            toastr.success(data.message);
            
            // Delay reload to allow user to read the message
            setTimeout(() => {
                location.reload();
            }, 2000); // 2 seconds delay
        } else {
            toastr.error(data.message || 'Failed to update address');
        }
    })
    .catch(error => {
        console.error('Error updating address:', error);
        toastr.error('Failed to update address: ' + error.message);
    });
}
</script>
@endsection