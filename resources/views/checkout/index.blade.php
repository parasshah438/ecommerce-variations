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
                                            <div class="address-card" onclick="selectAddress({{ $address->id }})">
                                                <input type="radio" name="address_id" value="{{ $address->id }}" 
                                                       class="form-check-input" id="address_{{ $address->id }}" hidden>
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="badge bg-primary">{{ $address->label }}</span>
                                                    <i class="bi bi-check-circle-fill text-primary address-check" style="display: none;"></i>
                                                </div>
                                                <h6 class="fw-semibold">{{ $address->name }}</h6>
                                                <p class="text-muted mb-1">{{ $address->phone }}</p>
                                                <p class="text-muted mb-0">
                                                    {{ $address->address_line }}<br>
                                                    {{ $address->city }}, {{ $address->state }} {{ $address->zip }}
                                                </p>
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
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="{{ old('phone', auth()->user()->mobile) }}" 
                                                   {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : 'required' }}>
                                            <label for="phone">Phone Number *</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="address_line" name="address_line" 
                                                      style="height: 100px" {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : 'required' }}>{{ old('address_line') }}</textarea>
                                            <label for="address_line">Address Line *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="city" name="city" 
                                                   value="{{ old('city') }}" {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : 'required' }}>
                                            <label for="city">City *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="state" name="state" 
                                                   value="{{ old('state') }}" {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : 'required' }}>
                                            <label for="state">State *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="zip" name="zip" 
                                                   value="{{ old('zip') }}" {{ auth()->user()->addresses && auth()->user()->addresses->count() > 0 ? 'disabled' : 'required' }}>
                                            <label for="zip">PIN Code *</label>
                                        </div>
                                    </div>
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
                                                <p class="text-muted mb-0 small">UPI, Card, Net Banking</p>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="badge bg-info">Coming Soon</span>
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
                                    <span>Subtotal ({{ $cart->items->sum('quantity') }} items)</span>
                                    <span>₹{{ number_format($total, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Shipping</span>
                                    <span class="text-success">Free</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax</span>
                                    <span>₹0.00</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <h6 class="fw-bold">Total</h6>
                                    <h6 class="fw-bold text-primary">₹{{ number_format($total, 2) }}</h6>
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
    const addressSelected = document.querySelector('input[name="address_id"]:checked');
    const newAddressVisible = !document.getElementById('newAddressForm').classList.contains('d-none');
    
    console.log('Form submit - Address selected:', addressSelected);
    console.log('Form submit - New address visible:', newAddressVisible);
    
    if (!addressSelected && !newAddressVisible) {
        e.preventDefault();
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
            e.preventDefault();
            alert('Please fill all required address fields.');
            return false;
        }
    }
    
    // Check payment method selection
    const paymentMethodSelected = document.querySelector('input[name="payment_method"]:checked');
    console.log('Form submit - Payment method selected:', paymentMethodSelected);
    
    if (!paymentMethodSelected) {
        e.preventDefault();
        alert('Please select a payment method.');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // If no saved addresses, show new address form
    const savedAddresses = document.querySelectorAll('.address-card').length;
    if (savedAddresses === 0) {
        addNewAddressRequirements();
    }
});
</script>
@endsection