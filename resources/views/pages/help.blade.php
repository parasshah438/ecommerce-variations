<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - Customer Service Center</title>
    
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
            padding: 100px 0;
        }
        
        .help-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
            height: 100%;
        }
        
        .help-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .step-item {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1rem;
            border-left: 5px solid var(--primary-color);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .step-item:hover {
            transform: translateX(10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .contact-method {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .contact-method:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .troubleshoot-section {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
        }
        
        .section-title {
            position: relative;
            margin-bottom: 3rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--accent-color);
        }
        
        .icon-xl {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .status-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--success-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
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
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pages.about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pages.faq') }}">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('pages.help') }}">Help & Support</a>
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
                        <i class="bi bi-headset me-3"></i>Help & Support
                    </h1>
                    <p class="lead mb-4">
                        We're here to help you every step of the way. Find answers, get support, 
                        and learn how to make the most of your shopping experience.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#quick-help" class="btn btn-light btn-lg">
                            <i class="bi bi-lightning-fill me-2"></i>Quick Help
                        </a>
                        <a href="#contact-support" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-chat-dots me-2"></i>Contact Support
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <img src="https://via.placeholder.com/500x400/ffffff/007bff?text=Customer+Support" 
                             alt="Customer Support" class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Help Section -->
    <section id="quick-help" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center">Quick Help Topics</h2>
                    <p class="text-center text-muted mb-5">
                        Find instant solutions to the most common questions
                    </p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card help-card shadow">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-person-circle text-primary icon-xl"></i>
                            <h5 class="card-title">Account Management</h5>
                            <p class="card-text text-muted">
                                Learn how to create, manage, and update your account settings
                            </p>
                            <a href="#account-help" class="btn btn-primary">Get Help</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card help-card shadow">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-credit-card text-success icon-xl"></i>
                            <h5 class="card-title">Checkout Process</h5>
                            <p class="card-text text-muted">
                                Step-by-step guide to completing your purchase safely
                            </p>
                            <a href="#checkout-help" class="btn btn-success">Get Help</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card help-card shadow">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-tools text-warning icon-xl"></i>
                            <h5 class="card-title">Troubleshooting</h5>
                            <p class="card-text text-muted">
                                Fix common issues with orders, payments, and website problems
                            </p>
                            <a href="#troubleshooting" class="btn btn-warning">Get Help</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Account Management Help -->
    <section id="account-help" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <h3 class="mb-4">
                        <i class="bi bi-person-circle text-primary me-2"></i>
                        Account Management Guide
                    </h3>
                    
                    <div class="step-item">
                        <div class="d-flex align-items-start">
                            <div class="step-number">1</div>
                            <div>
                                <h5>Creating Your Account</h5>
                                <p class="mb-3">Sign up for a free account to unlock exclusive benefits:</p>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Faster checkout</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Order tracking</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Wishlist and favorites</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Exclusive offers</li>
                                </ul>
                                <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm">Create Account</a>
                            </div>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="d-flex align-items-start">
                            <div class="step-number">2</div>
                            <div>
                                <h5>Managing Personal Information</h5>
                                <p class="mb-3">Keep your account information up to date:</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Profile Settings:</strong>
                                        <ul class="mt-2">
                                            <li>Update name and email</li>
                                            <li>Change password</li>
                                            <li>Set communication preferences</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Address Book:</strong>
                                        <ul class="mt-2">
                                            <li>Add multiple addresses</li>
                                            <li>Set default shipping address</li>
                                            <li>Manage billing addresses</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="d-flex align-items-start">
                            <div class="step-number">3</div>
                            <div>
                                <h5>Account Security</h5>
                                <p class="mb-3">Protect your account with these security features:</p>
                                <div class="alert alert-info">
                                    <i class="bi bi-shield-check me-2"></i>
                                    <strong>Security Tips:</strong> Use a strong password, enable two-factor authentication, and never share your login credentials.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Checkout Process Help -->
    <section id="checkout-help" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <h3 class="mb-4">
                        <i class="bi bi-credit-card text-success me-2"></i>
                        Checkout Process Guide
                    </h3>
                    
                    <div class="step-item">
                        <div class="d-flex align-items-start">
                            <div class="step-number">1</div>
                            <div>
                                <h5>Adding Items to Cart</h5>
                                <p>Select your desired products and add them to your shopping cart:</p>
                                <ul>
                                    <li>Choose size, color, and quantity</li>
                                    <li>Review product details and prices</li>
                                    <li>Save items for later or add to wishlist</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="d-flex align-items-start">
                            <div class="step-number">2</div>
                            <div>
                                <h5>Shipping Information</h5>
                                <p>Provide accurate shipping details:</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Required Information:</strong>
                                        <ul>
                                            <li>Full name</li>
                                            <li>Complete address</li>
                                            <li>Phone number</li>
                                            <li>Email address</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Shipping Options:</strong>
                                        <ul>
                                            <li>Standard delivery (5-7 days)</li>
                                            <li>Express shipping (2-3 days)</li>
                                            <li>Same-day delivery (select areas)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="d-flex align-items-start">
                            <div class="step-number">3</div>
                            <div>
                                <h5>Payment Methods</h5>
                                <p>We accept various secure payment options:</p>
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <i class="bi bi-credit-card text-primary" style="font-size: 2rem;"></i>
                                            <p class="mt-2 mb-0"><strong>Credit Cards</strong></p>
                                            <small class="text-muted">Visa, Mastercard, Amex</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <i class="bi bi-phone text-success" style="font-size: 2rem;"></i>
                                            <p class="mt-2 mb-0"><strong>UPI/Wallets</strong></p>
                                            <small class="text-muted">PhonePe, GPay, Paytm</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <i class="bi bi-bank text-info" style="font-size: 2rem;"></i>
                                            <p class="mt-2 mb-0"><strong>Net Banking</strong></p>
                                            <small class="text-muted">All major banks</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <i class="bi bi-cash text-warning" style="font-size: 2rem;"></i>
                                            <p class="mt-2 mb-0"><strong>COD</strong></p>
                                            <small class="text-muted">Cash on Delivery</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Troubleshooting Section -->
    <section id="troubleshooting" class="py-5 troubleshoot-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="section-title text-center">
                        <i class="bi bi-tools text-warning me-2"></i>
                        Common Issues & Solutions
                    </h3>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Payment Issues
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="paymentAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payment1">
                                            Payment Failed or Declined
                                        </button>
                                    </h2>
                                    <div id="payment1" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                                        <div class="accordion-body">
                                            <strong>Solutions:</strong>
                                            <ul>
                                                <li>Check your card details and expiry date</li>
                                                <li>Ensure sufficient balance/credit limit</li>
                                                <li>Try a different payment method</li>
                                                <li>Contact your bank if the issue persists</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payment2">
                                            Double Charged for Order
                                        </button>
                                    </h2>
                                    <div id="payment2" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                                        <div class="accordion-body">
                                            Don't worry! Double charges are usually temporary holds that get automatically released within 3-5 business days. If the charge doesn't reverse, contact our support team with your transaction details.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="bi bi-bag-x me-2"></i>
                                Order Issues
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="orderAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#order1">
                                            Can't Find My Order
                                        </button>
                                    </h2>
                                    <div id="order1" class="accordion-collapse collapse" data-bs-parent="#orderAccordion">
                                        <div class="accordion-body">
                                            <strong>Check these places:</strong>
                                            <ul>
                                                <li>Your email for order confirmation</li>
                                                <li>Spam/junk folder</li>
                                                <li>Your account's order history</li>
                                                <li>Different email address if you have multiple</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#order2">
                                            Order Status Not Updating
                                        </button>
                                    </h2>
                                    <div id="order2" class="accordion-collapse collapse" data-bs-parent="#orderAccordion">
                                        <div class="accordion-body">
                                            Order status updates may take 24-48 hours. If your order status hasn't updated for more than 48 hours, please contact our support team with your order number.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-globe me-2"></i>
                                Website Issues
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="websiteAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#website1">
                                            Page Not Loading
                                        </button>
                                    </h2>
                                    <div id="website1" class="accordion-collapse collapse" data-bs-parent="#websiteAccordion">
                                        <div class="accordion-body">
                                            <strong>Try these steps:</strong>
                                            <ol>
                                                <li>Refresh the page (Ctrl+F5)</li>
                                                <li>Clear your browser cache and cookies</li>
                                                <li>Try a different browser</li>
                                                <li>Check your internet connection</li>
                                                <li>Disable browser extensions temporarily</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-person-lock me-2"></i>
                                Login Problems
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="loginAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#login1">
                                            Forgot Password
                                        </button>
                                    </h2>
                                    <div id="login1" class="accordion-collapse collapse" data-bs-parent="#loginAccordion">
                                        <div class="accordion-body">
                                            <ol>
                                                <li>Go to the login page</li>
                                                <li>Click "Forgot Password?"</li>
                                                <li>Enter your registered email</li>
                                                <li>Check your email for reset link</li>
                                                <li>Create a new password</li>
                                            </ol>
                                            <a href="{{ route('password.request') }}" class="btn btn-outline-success btn-sm">Reset Password</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Support Section -->
    <section id="contact-support" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center">Contact Our Support Team</h2>
                    <p class="text-center text-muted mb-5">
                        Still need help? Our friendly support team is available 24/7
                    </p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="contact-method">
                        <div style="position: relative;">
                            <i class="bi bi-chat-dots text-primary icon-xl"></i>
                            <div class="status-badge">
                                <i class="bi bi-circle-fill"></i>
                            </div>
                        </div>
                        <h5>Live Chat</h5>
                        <p class="text-muted mb-3">Get instant help from our agents</p>
                        <button class="btn btn-primary btn-sm" onclick="startLiveChat()">Start Chat</button>
                        <div class="mt-2">
                            <small class="text-success">
                                <i class="bi bi-circle-fill me-1"></i>Online Now
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="contact-method">
                        <i class="bi bi-envelope text-success icon-xl"></i>
                        <h5>Email Support</h5>
                        <p class="text-muted mb-3">Send detailed queries anytime</p>
                        <a href="mailto:support@yourcompany.com" class="btn btn-success btn-sm">Send Email</a>
                        <div class="mt-2">
                            <small class="text-muted">Response within 2 hours</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="contact-method">
                        <i class="bi bi-telephone text-warning icon-xl"></i>
                        <h5>Phone Support</h5>
                        <p class="text-muted mb-3">Speak directly with our team</p>
                        <a href="tel:+15551234567" class="btn btn-warning btn-sm">Call Now</a>
                        <div class="mt-2">
                            <small class="text-muted">24/7 Available</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="contact-method">
                        <i class="bi bi-question-circle text-info icon-xl"></i>
                        <h5>Help Center</h5>
                        <p class="text-muted mb-3">Browse our knowledge base</p>
                        <a href="{{ route('pages.faq') }}" class="btn btn-info btn-sm">Visit FAQ</a>
                        <div class="mt-2">
                            <small class="text-muted">Self-service options</small>
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
                        We're committed to providing exceptional customer service and support 
                        to ensure your shopping experience is seamless.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('welcome') }}" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="{{ route('pages.about') }}" class="text-muted text-decoration-none">About</a></li>
                        <li><a href="{{ route('products.index') }}" class="text-muted text-decoration-none">Products</a></li>
                        <li><a href="{{ route('pages.faq') }}" class="text-muted text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.help') }}" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="{{ route('pages.privacy') }}" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li><a href="{{ route('pages.terms') }}" class="text-muted text-decoration-none">Terms & Conditions</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3">Contact Info</h6>
                    <div class="text-muted">
                        <p><i class="bi bi-geo-alt me-2"></i>123 Business Street, City, State 12345</p>
                        <p><i class="bi bi-telephone me-2"></i>+1 (555) 123-4567</p>
                        <p><i class="bi bi-envelope me-2"></i>support@yourcompany.com</p>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center text-muted">
                <p>&copy; {{ date('Y') }} Your Company. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Smooth scrolling for anchor links
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

        // Live chat simulation
        function startLiveChat() {
            alert('Live chat feature will be implemented soon! For now, please email us at support@yourcompany.com');
        }

        // Auto-expand accordion if URL has hash
        window.addEventListener('load', function() {
            if (window.location.hash) {
                const target = document.querySelector(window.location.hash);
                if (target && target.classList.contains('accordion-collapse')) {
                    const button = document.querySelector(`[data-bs-target="${window.location.hash}"]`);
                    if (button) {
                        button.click();
                        setTimeout(() => {
                            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }, 300);
                    }
                }
            }
        });

        // Add floating help button
        document.addEventListener('DOMContentLoaded', function() {
            const floatingHelp = document.createElement('div');
            floatingHelp.innerHTML = `
                <button class="btn btn-primary rounded-circle shadow-lg" 
                        style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; width: 60px; height: 60px;"
                        onclick="scrollToTop()" title="Back to Top">
                    <i class="bi bi-arrow-up"></i>
                </button>
            `;
            document.body.appendChild(floatingHelp);
        });

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
</body>
</html>