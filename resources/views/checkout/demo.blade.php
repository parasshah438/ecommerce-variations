@extends('layouts.frontend')

@section('title', 'Checkout Demo - ' . config('app.name'))

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
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
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

    .demo-banner {
        background: linear-gradient(135deg, #f8d7da, #f5c2c7);
        border: 1px solid #f1aeb5;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="checkout-container">
    <div class="container">
        <!-- Demo Banner -->
        <div class="demo-banner">
            <div class="text-center">
                <i class="bi bi-info-circle text-danger fs-5 me-2"></i>
                <strong>Demo Mode:</strong> This is a preview of the checkout page design. 
                <a href="{{ route('login') }}" class="text-decoration-none">Login</a> to see the actual checkout with your cart items.
            </div>
        </div>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('welcome') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Cart</a></li>
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

        <!-- Main Checkout Content -->
        <form action="#" method="POST" id="checkoutForm">
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
                            <!-- Sample Saved Addresses -->
                            <h6 class="fw-semibold mb-3">Choose from saved addresses:</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="address-card selected" onclick="selectAddress(1)">
                                        <input type="radio" name="address_id" value="1" class="form-check-input" hidden checked>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-primary">Home</span>
                                            <i class="bi bi-check-circle-fill text-primary address-check"></i>
                                        </div>
                                        <h6 class="fw-semibold">John Doe</h6>
                                        <p class="text-muted mb-1">+91 9876543210</p>
                                        <p class="text-muted mb-0">
                                            123 Main Street, Apartment 4B<br>
                                            Mumbai, Maharashtra 400001
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="address-card" onclick="selectAddress(2)">
                                        <input type="radio" name="address_id" value="2" class="form-check-input" hidden>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-secondary">Office</span>
                                            <i class="bi bi-check-circle-fill text-primary address-check" style="display: none;"></i>
                                        </div>
                                        <h6 class="fw-semibold">John Doe</h6>
                                        <p class="text-muted mb-1">+91 9876543210</p>
                                        <p class="text-muted mb-0">
                                            456 Business Park, Floor 10<br>
                                            Mumbai, Maharashtra 400001
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mb-3">
                                <button type="button" class="btn btn-outline-primary" onclick="toggleNewAddress()">
                                    <i class="bi bi-plus-circle me-1"></i>Add New Address
                                </button>
                            </div>

                            <!-- New Address Form -->
                            <div id="newAddressForm" class="d-none">
                                <h6 class="fw-semibold mb-3">Add new address:</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="name" name="name" value="John Doe">
                                            <label for="name">Full Name *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="phone" name="phone" value="+91 9876543210">
                                            <label for="phone">Phone Number *</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="address_line" name="address_line" style="height: 100px">789 New Street, Block C</textarea>
                                            <label for="address_line">Address Line *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="city" name="city" value="Mumbai">
                                            <label for="city">City *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="state" name="state" value="Maharashtra">
                                            <label for="state">State *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="zip" name="zip" value="400002">
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

                        <!-- Sample Cart Items -->
                        <div id="cart-items">
                            <div class="product-item">
                                <div class="d-flex align-items-center">
                                    <div class="position-relative me-3">
                                        <div class="product-image">
                                            <i class="bi bi-image text-muted fs-4"></i>
                                        </div>
                                        <span class="quantity-badge">2</span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold">Premium Cotton T-Shirt</h6>
                                        <p class="text-muted mb-1 small">SKU: CT-BLU-M-001</p>
                                        <p class="text-muted mb-1 small">Color: Blue, Size: M</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold text-primary">₹1,998.00</span>
                                            <small class="text-muted">₹999.00 × 2</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="product-item">
                                <div class="d-flex align-items-center">
                                    <div class="position-relative me-3">
                                        <div class="product-image">
                                            <i class="bi bi-image text-muted fs-4"></i>
                                        </div>
                                        <span class="quantity-badge">1</span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold">Denim Jacket</h6>
                                        <p class="text-muted mb-1 small">SKU: DJ-BLK-L-002</p>
                                        <p class="text-muted mb-1 small">Color: Black, Size: L</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold text-primary">₹2,499.00</span>
                                            <small class="text-muted">₹2,499.00 × 1</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Total -->
                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal (3 items)</span>
                                <span>₹4,497.00</span>
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
                                <h6 class="fw-bold text-primary">₹4,497.00</h6>
                            </div>

                            <!-- Place Order Button -->
                            <button type="button" class="btn btn-place-order w-100 text-white" onclick="showDemoAlert()">
                                <i class="bi bi-lock me-2"></i>
                                Place Order (Demo)
                            </button>

                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Secure 256-bit SSL encryption
                                </small>
                            </div>
                        </div>
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
    
    // Hide new address form
    document.getElementById('newAddressForm').classList.add('d-none');
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
    } else {
        form.classList.add('d-none');
        // Select first address by default
        selectAddress(1);
    }
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

function showDemoAlert() {
    alert('This is a demo checkout page. To place real orders, please login to your account and add items to your cart.');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Checkout demo page loaded successfully!');
});
</script>
@endsection