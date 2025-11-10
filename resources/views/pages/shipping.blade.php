<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Policy - Your Company</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --accent-color: #fd7e14;
            --success-color: #28a745;
            --info-color: #17a2b8;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 100px 0 60px;
        }
        
        .shipping-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        
        .shipping-card:hover {
            transform: translateY(-5px);
        }
        
        .shipping-option {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .shipping-option:hover {
            border-color: var(--primary-color);
            background: rgba(0, 123, 255, 0.05);
        }
        
        .zone-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            height: 100%;
        }
        
        .icon-xl {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .policy-highlight {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            border-left: 5px solid var(--primary-color);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 8px;
        }
        
        .cost-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .timeline-item {
            position: relative;
            padding-left: 3rem;
            margin-bottom: 2rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--primary-color);
        }
        
        .timeline-item::after {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0.5rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: var(--accent-color);
        }
        
        .tracking-step {
            text-align: center;
            padding: 1.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        
        .tracking-step:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('welcome') }}">
                <i class="bi bi-shop"></i> Your Company
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('welcome') }}">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Policies
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="{{ route('pages.shipping') }}">Shipping Policy</a></li>
                            <li><a class="dropdown-item" href="{{ route('pages.return.refund') }}">Return & Refund</a></li>
                            <li><a class="dropdown-item" href="{{ route('pages.privacy') }}">Privacy Policy</a></li>
                            <li><a class="dropdown-item" href="{{ route('pages.terms') }}">Terms & Conditions</a></li>
                            <li><a class="dropdown-item" href="{{ route('pages.cookie.policy') }}">Cookie Policy</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pages.help') }}">Help & Support</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('products.index') }}">Products</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="bi bi-truck me-3"></i>Shipping Policy
                    </h1>
                    <p class="lead mb-4">
                        Fast, reliable, and secure delivery to your doorstep. Learn about our shipping 
                        options, delivery times, and policies to ensure a smooth shopping experience.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#shipping-options" class="btn btn-light btn-lg">
                            <i class="bi bi-box-seam me-2"></i>Shipping Options
                        </a>
                        <a href="#track-order" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-geo-alt me-2"></i>Track Your Order
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <img src="https://via.placeholder.com/500x400/ffffff/007bff?text=Fast+Delivery" 
                             alt="Fast Delivery" class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Shipping Options Section -->
    <section id="shipping-options" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center mb-5">
                        <i class="bi bi-speedometer2 me-2 text-primary"></i>
                        Choose Your Shipping Speed
                    </h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="shipping-option">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-truck text-success me-3" style="font-size: 2.5rem;"></i>
                            <div>
                                <h5 class="mb-1">Standard Shipping</h5>
                                <span class="badge bg-success">Most Popular</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Delivery Time:</h6>
                                <p class="text-muted">5-7 Business Days</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Cost:</h6>
                                <p class="text-success fw-bold">₹50</p>
                                <small class="text-muted">Free on orders ₹999+</small>
                            </div>
                        </div>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>Tracking included</li>
                            <li><i class="bi bi-check text-success me-2"></i>Insurance up to ₹5,000</li>
                            <li><i class="bi bi-check text-success me-2"></i>Delivery to doorstep</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="shipping-option">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-lightning text-warning me-3" style="font-size: 2.5rem;"></i>
                            <div>
                                <h5 class="mb-1">Express Shipping</h5>
                                <span class="badge bg-warning">Fast Delivery</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Delivery Time:</h6>
                                <p class="text-muted">2-3 Business Days</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Cost:</h6>
                                <p class="text-warning fw-bold">₹150</p>
                                <small class="text-muted">All locations</small>
                            </div>
                        </div>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>Priority handling</li>
                            <li><i class="bi bi-check text-success me-2"></i>Real-time tracking</li>
                            <li><i class="bi bi-check text-success me-2"></i>Insurance up to ₹10,000</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="shipping-option">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-rocket text-danger me-3" style="font-size: 2.5rem;"></i>
                            <div>
                                <h5 class="mb-1">Same Day Delivery</h5>
                                <span class="badge bg-danger">Ultra Fast</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Delivery Time:</h6>
                                <p class="text-muted">Same Day</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Cost:</h6>
                                <p class="text-danger fw-bold">₹300</p>
                                <small class="text-muted">Select cities only</small>
                            </div>
                        </div>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>Order by 2 PM</li>
                            <li><i class="bi bi-check text-success me-2"></i>Delivery by 8 PM</li>
                            <li><i class="bi bi-check text-success me-2"></i>SMS notifications</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Shipping Zones Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center mb-5">
                        <i class="bi bi-geo-alt me-2 text-primary"></i>
                        Delivery Zones & Coverage
                    </h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="zone-card">
                        <i class="bi bi-building text-primary icon-xl"></i>
                        <h4>Metro Cities</h4>
                        <p class="text-muted">Mumbai, Delhi, Bangalore, Chennai, Kolkata, Hyderabad</p>
                        <div class="row text-center">
                            <div class="col-6">
                                <h6>Standard</h6>
                                <p class="text-success fw-bold">3-5 days</p>
                            </div>
                            <div class="col-6">
                                <h6>Express</h6>
                                <p class="text-warning fw-bold">1-2 days</p>
                            </div>
                        </div>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>Same day delivery available
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="zone-card">
                        <i class="bi bi-house text-info icon-xl"></i>
                        <h4>Tier 2 Cities</h4>
                        <p class="text-muted">Pune, Ahmedabad, Jaipur, Lucknow, Kanpur, Nagpur</p>
                        <div class="row text-center">
                            <div class="col-6">
                                <h6>Standard</h6>
                                <p class="text-success fw-bold">4-6 days</p>
                            </div>
                            <div class="col-6">
                                <h6>Express</h6>
                                <p class="text-warning fw-bold">2-3 days</p>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>Express delivery available
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="zone-card">
                        <i class="bi bi-tree text-success icon-xl"></i>
                        <h4>Other Areas</h4>
                        <p class="text-muted">Towns, rural areas, and remote locations</p>
                        <div class="row text-center">
                            <div class="col-6">
                                <h6>Standard</h6>
                                <p class="text-success fw-bold">6-8 days</p>
                            </div>
                            <div class="col-6">
                                <h6>Express</h6>
                                <p class="text-warning fw-bold">3-5 days</p>
                            </div>
                        </div>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>Standard shipping only
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Shipping Costs Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center mb-5">
                        <i class="bi bi-currency-rupee me-2 text-primary"></i>
                        Shipping Costs & Free Shipping
                    </h2>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="cost-table">
                        <table class="table table-hover mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Order Value</th>
                                    <th>Standard Shipping</th>
                                    <th>Express Shipping</th>
                                    <th>Same Day Delivery</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>₹0 - ₹498</strong></td>
                                    <td>₹50</td>
                                    <td>₹150</td>
                                    <td>₹300</td>
                                </tr>
                                <tr>
                                    <td><strong>₹499 - ₹998</strong></td>
                                    <td>₹30</td>
                                    <td>₹150</td>
                                    <td>₹300</td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>₹999 & Above</strong></td>
                                    <td><span class="badge bg-success">FREE</span></td>
                                    <td>₹150</td>
                                    <td>₹300</td>
                                </tr>
                                <tr class="table-warning">
                                    <td><strong>₹2999 & Above</strong></td>
                                    <td><span class="badge bg-success">FREE</span></td>
                                    <td><span class="badge bg-warning">FREE</span></td>
                                    <td>₹300</td>
                                </tr>
                                <tr class="table-danger">
                                    <td><strong>₹4999 & Above</strong></td>
                                    <td><span class="badge bg-success">FREE</span></td>
                                    <td><span class="badge bg-warning">FREE</span></td>
                                    <td><span class="badge bg-danger">FREE</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="policy-highlight mt-4">
                        <h5><i class="bi bi-gift text-success me-2"></i>Free Shipping Benefits</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check text-success me-2"></i>No minimum order for members</li>
                                    <li><i class="bi bi-check text-success me-2"></i>Free shipping on all returns</li>
                                    <li><i class="bi bi-check text-success me-2"></i>Special offers during sales</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check text-success me-2"></i>Birthday month free shipping</li>
                                    <li><i class="bi bi-check text-success me-2"></i>Loyalty program benefits</li>
                                    <li><i class="bi bi-check text-success me-2"></i>Bulk order discounts</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Order Processing Timeline -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center mb-5">
                        <i class="bi bi-clock-history me-2 text-primary"></i>
                        Order Processing Timeline
                    </h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="shipping-card">
                        <h4 class="mb-4">From Order to Delivery</h4>
                        
                        <div class="timeline-item">
                            <h6>Order Placed</h6>
                            <p class="text-muted">You place your order and receive confirmation email</p>
                            <small class="text-primary">Immediate</small>
                        </div>
                        
                        <div class="timeline-item">
                            <h6>Payment Processing</h6>
                            <p class="text-muted">We verify and process your payment</p>
                            <small class="text-primary">5-10 minutes</small>
                        </div>
                        
                        <div class="timeline-item">
                            <h6>Order Preparation</h6>
                            <p class="text-muted">Items are picked, packed, and quality checked</p>
                            <small class="text-primary">6-24 hours</small>
                        </div>
                        
                        <div class="timeline-item">
                            <h6>Shipped</h6>
                            <p class="text-muted">Package handed over to shipping partner</p>
                            <small class="text-primary">24-48 hours</small>
                        </div>
                        
                        <div class="timeline-item">
                            <h6>In Transit</h6>
                            <p class="text-muted">Package is on its way to you</p>
                            <small class="text-primary">1-7 days</small>
                        </div>
                        
                        <div class="timeline-item">
                            <h6>Delivered</h6>
                            <p class="text-muted">Package arrives at your doorstep</p>
                            <small class="text-success">Order Complete!</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="shipping-card">
                        <h4 class="mb-4">Processing Schedules</h4>
                        
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle me-2"></i>Business Hours</h6>
                            <p class="mb-0">Monday to Friday: 9 AM - 6 PM<br>
                            Saturday: 10 AM - 4 PM<br>
                            Sunday & Holidays: Closed</p>
                        </div>
                        
                        <h6>Order Cutoff Times</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-clock text-primary me-2"></i><strong>Same Day:</strong> Order by 12 PM</li>
                            <li><i class="bi bi-clock text-warning me-2"></i><strong>Next Day:</strong> Order by 4 PM</li>
                            <li><i class="bi bi-clock text-success me-2"></i><strong>Standard:</strong> Order anytime</li>
                        </ul>
                        
                        <h6 class="mt-4">Holiday Schedule</h6>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> Orders placed on weekends and holidays will be processed 
                            on the next business day. Delivery times may be extended during festival seasons.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Order Tracking Section -->
    <section id="track-order" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center mb-5">
                        <i class="bi bi-search me-2 text-primary"></i>
                        Track Your Order
                    </h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="tracking-step">
                        <i class="bi bi-receipt text-primary icon-xl"></i>
                        <h5>Order Confirmation</h5>
                        <p class="text-muted">Check your email for order details and tracking number</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="tracking-step">
                        <i class="bi bi-phone text-success icon-xl"></i>
                        <h5>SMS Updates</h5>
                        <p class="text-muted">Receive real-time updates via SMS notifications</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="tracking-step">
                        <i class="bi bi-globe text-info icon-xl"></i>
                        <h5>Online Tracking</h5>
                        <p class="text-muted">Track your order online using our tracking portal</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="tracking-step">
                        <i class="bi bi-headset text-warning icon-xl"></i>
                        <h5>Customer Support</h5>
                        <p class="text-muted">Contact our support team for any tracking queries</p>
                    </div>
                </div>
            </div>
            
            <div class="row justify-content-center mt-5">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white text-center">
                            <h5><i class="bi bi-search me-2"></i>Track Your Order</h5>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <label for="trackingNumber" class="form-label">Order Number or Tracking ID</label>
                                    <input type="text" class="form-control form-control-lg" id="trackingNumber" 
                                           placeholder="Enter your order number (e.g., ORD123456)">
                                </div>
                                <div class="mb-3">
                                    <label for="emailAddress" class="form-label">Email Address (Optional)</label>
                                    <input type="email" class="form-control" id="emailAddress" 
                                           placeholder="Enter your email address">
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-search me-2"></i>Track Order
                                    </button>
                                </div>
                            </form>
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    Can't find your order? <a href="{{ route('pages.help') }}">Contact Support</a>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Shipping Policies Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center mb-5">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                        Additional Shipping Information
                    </h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="shipping-card">
                        <h4><i class="bi bi-shield-check text-success me-2"></i>Delivery Guarantee</h4>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>Safe and secure packaging</li>
                            <li><i class="bi bi-check text-success me-2"></i>Insurance coverage included</li>
                            <li><i class="bi bi-check text-success me-2"></i>Damage protection guarantee</li>
                            <li><i class="bi bi-check text-success me-2"></i>Signature confirmation available</li>
                        </ul>
                        <div class="alert alert-success">
                            <i class="bi bi-info-circle me-2"></i>
                            We guarantee safe delivery or full refund of damaged items.
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="shipping-card">
                        <h4><i class="bi bi-exclamation-triangle text-warning me-2"></i>Important Notes</h4>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-dot text-warning me-2"></i>Address verification required for high-value items</li>
                            <li><i class="bi bi-dot text-warning me-2"></i>Photo ID may be required for delivery</li>
                            <li><i class="bi bi-dot text-warning me-2"></i>Delivery attempts made up to 3 times</li>
                            <li><i class="bi bi-dot text-warning me-2"></i>Undelivered packages returned after 7 days</li>
                        </ul>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Incorrect addresses may result in delivery delays or additional charges.
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="shipping-card">
                        <h4><i class="bi bi-geo-alt text-info me-2"></i>Special Locations</h4>
                        <p><strong>Remote Areas:</strong></p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-dot text-info me-2"></i>Additional 1-2 days for delivery</li>
                            <li><i class="bi bi-dot text-info me-2"></i>Extra shipping charges may apply</li>
                            <li><i class="bi bi-dot text-info me-2"></i>Limited to standard shipping only</li>
                        </ul>
                        
                        <p><strong>PO Boxes & Military Bases:</strong></p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-dot text-info me-2"></i>Standard shipping available</li>
                            <li><i class="bi bi-dot text-info me-2"></i>No COD option available</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="shipping-card">
                        <h4><i class="bi bi-question-circle text-primary me-2"></i>Need Help?</h4>
                        <p>Have questions about shipping? We're here to help!</p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('pages.help') }}" class="btn btn-primary">
                                <i class="bi bi-headset me-2"></i>Contact Support
                            </a>
                            <a href="{{ route('pages.faq') }}" class="btn btn-outline-primary">
                                <i class="bi bi-question-circle me-2"></i>View FAQ
                            </a>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                Customer Service: 9 AM - 9 PM, 7 days a week
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="mb-3">
                        <i class="bi bi-shop me-2"></i>Your Company
                    </h5>
                    <p class="text-muted">
                        Fast, reliable shipping with transparent policies and excellent customer service 
                        to ensure your satisfaction with every order.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Shipping</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.shipping') }}" class="text-muted text-decoration-none">Shipping Policy</a></li>
                        <li><a href="#track-order" class="text-muted text-decoration-none">Track Order</a></li>
                        <li><a href="{{ route('pages.return.refund') }}" class="text-muted text-decoration-none">Returns</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Delivery Info</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.help') }}" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="{{ route('pages.faq') }}" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Contact Us</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Live Chat</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3">Quick Contact</h6>
                    <div class="text-muted">
                        <p><i class="bi bi-telephone me-2"></i>Shipping Support: +1 (555) 123-4567</p>
                        <p><i class="bi bi-envelope me-2"></i>shipping@yourcompany.com</p>
                        <p><i class="bi bi-clock me-2"></i>Mon-Fri: 9AM-6PM, Sat: 10AM-4PM</p>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center text-muted">
                <p>&copy; {{ date('Y') }} Your Company. All rights reserved. | Fast, Reliable, Secure Shipping</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Order tracking simulation
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            const trackingNumber = document.getElementById('trackingNumber').value;
            if (trackingNumber) {
                alert('Tracking feature will be implemented soon! For now, please contact customer support for order updates.');
            } else {
                alert('Please enter your order number or tracking ID.');
            }
        });

        // Add floating help button
        document.addEventListener('DOMContentLoaded', function() {
            const floatingHelp = document.createElement('div');
            floatingHelp.innerHTML = `
                <button class="btn btn-primary rounded-circle shadow-lg" 
                        style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; width: 60px; height: 60px;"
                        onclick="window.location.href='{{ route('pages.help') }}'" title="Need Help?">
                    <i class="bi bi-question-lg"></i>
                </button>
            `;
            document.body.appendChild(floatingHelp);
        });
    </script>
</body>
</html>