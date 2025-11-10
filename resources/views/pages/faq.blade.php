<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Frequently Asked Questions</title>
    
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
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 100px 0 80px;
        }
        
        .faq-category {
            border-radius: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .faq-category:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
        }
        
        .faq-category.active {
            border-color: var(--primary-color);
            background-color: rgba(0, 123, 255, 0.1);
        }
        
        .accordion-button:not(.collapsed) {
            background-color: var(--primary-color);
            color: white;
        }
        
        .accordion-button:not(.collapsed)::after {
            filter: brightness(0) invert(1);
        }
        
        .search-box {
            background: white;
            border-radius: 50px;
            padding: 15px 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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
        
        .contact-card {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
        }
        
        .badge-custom {
            background: var(--accent-color);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
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
                        <a class="nav-link active" href="{{ route('pages.faq') }}">FAQ</a>
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
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-4">Frequently Asked Questions</h1>
                    <p class="lead mb-4">
                        Find quick answers to common questions about our products, services, and policies.
                    </p>
                    
                    <!-- Search Box -->
                    <div class="search-box d-flex align-items-center mb-4">
                        <i class="bi bi-search text-muted me-3"></i>
                        <input type="text" id="faqSearch" class="form-control border-0 bg-transparent" 
                               placeholder="Search for answers...">
                    </div>
                    
                    <div class="text-muted">
                        <small>Can't find what you're looking for? <a href="#contact" class="text-white">Contact us</a></small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Categories -->
    <section class="py-5">
        <div class="container">
            <div class="row g-3 mb-5">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="faq-category card h-100 text-center p-3" data-category="all">
                        <i class="bi bi-grid-3x3-gap-fill text-primary mb-2" style="font-size: 2rem;"></i>
                        <h6 class="mb-0">All Questions</h6>
                        <small class="text-muted">View All</small>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="faq-category card h-100 text-center p-3" data-category="orders">
                        <i class="bi bi-bag-check text-primary mb-2" style="font-size: 2rem;"></i>
                        <h6 class="mb-0">Orders</h6>
                        <small class="text-muted">Placing & Tracking</small>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="faq-category card h-100 text-center p-3" data-category="shipping">
                        <i class="bi bi-truck text-primary mb-2" style="font-size: 2rem;"></i>
                        <h6 class="mb-0">Shipping</h6>
                        <small class="text-muted">Delivery Info</small>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="faq-category card h-100 text-center p-3" data-category="returns">
                        <i class="bi bi-arrow-left-circle text-primary mb-2" style="font-size: 2rem;"></i>
                        <h6 class="mb-0">Returns</h6>
                        <small class="text-muted">Exchange Policy</small>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="faq-category card h-100 text-center p-3" data-category="sizing">
                        <i class="bi bi-rulers text-primary mb-2" style="font-size: 2rem;"></i>
                        <h6 class="mb-0">Sizing</h6>
                        <small class="text-muted">Size Guide</small>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="faq-category card h-100 text-center p-3" data-category="account">
                        <i class="bi bi-person-circle text-primary mb-2" style="font-size: 2rem;"></i>
                        <h6 class="mb-0">Account</h6>
                        <small class="text-muted">Profile & Login</small>
                    </div>
                </div>
            </div>

            <!-- FAQ Accordion -->
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        
                        <!-- Orders FAQs -->
                        <div class="accordion-item mb-3 faq-item" data-category="orders">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    <span class="badge-custom me-3">Orders</span>
                                    How do I place an order?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <strong>Placing an order is simple:</strong>
                                    <ol class="mt-2">
                                        <li>Browse our products and select the items you want</li>
                                        <li>Choose your size, color, and quantity</li>
                                        <li>Add items to your cart</li>
                                        <li>Review your cart and proceed to checkout</li>
                                        <li>Enter your shipping and payment information</li>
                                        <li>Confirm your order</li>
                                    </ol>
                                    <p class="mt-3">You'll receive an order confirmation email once your order is successfully placed.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 faq-item" data-category="orders">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    <span class="badge-custom me-3">Orders</span>
                                    How can I track my order?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can track your order in several ways:
                                    <ul class="mt-2">
                                        <li><strong>Order Tracking Page:</strong> Visit our order tracking page and enter your order number</li>
                                        <li><strong>Email Updates:</strong> Check your email for shipping notifications with tracking links</li>
                                        <li><strong>Account Dashboard:</strong> Log into your account and view order status in your dashboard</li>
                                    </ul>
                                    <div class="alert alert-info mt-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Tracking information is usually available within 24-48 hours after your order is shipped.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 faq-item" data-category="orders">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    <span class="badge-custom me-3">Orders</span>
                                    Can I cancel or modify my order?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <strong>Order modifications:</strong>
                                    <p>You can cancel or modify your order within 1 hour of placing it, provided it hasn't been processed yet.</p>
                                    
                                    <strong>To cancel or modify:</strong>
                                    <ul class="mt-2">
                                        <li>Contact our customer service immediately</li>
                                        <li>Provide your order number</li>
                                        <li>Specify the changes you'd like to make</li>
                                    </ul>
                                    
                                    <div class="alert alert-warning mt-3">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        Once an order is shipped, it cannot be modified. You'll need to process a return instead.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping FAQs -->
                        <div class="accordion-item mb-3 faq-item" data-category="shipping">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    <span class="badge-custom me-3">Shipping</span>
                                    What are your shipping options and costs?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <strong>We offer several shipping options:</strong>
                                    <div class="table-responsive mt-3">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Shipping Method</th>
                                                    <th>Delivery Time</th>
                                                    <th>Cost</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Standard Shipping</td>
                                                    <td>5-7 business days</td>
                                                    <td>₹50 (Free on orders over ₹999)</td>
                                                </tr>
                                                <tr>
                                                    <td>Express Shipping</td>
                                                    <td>2-3 business days</td>
                                                    <td>₹150</td>
                                                </tr>
                                                <tr>
                                                    <td>Same Day Delivery*</td>
                                                    <td>Same day</td>
                                                    <td>₹300</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted">*Same day delivery available in select cities only</small>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 faq-item" data-category="shipping">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    <span class="badge-custom me-3">Shipping</span>
                                    Do you ship internationally?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Currently, we only ship within India. We're working on expanding our international shipping options.
                                    
                                    <div class="mt-3">
                                        <strong>Domestic shipping covers:</strong>
                                        <ul class="mt-2">
                                            <li>All major cities and towns</li>
                                            <li>Rural areas (may take additional 1-2 days)</li>
                                            <li>Pin codes served by our logistics partners</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="alert alert-info mt-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Subscribe to our newsletter to be notified when international shipping becomes available!
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Returns FAQs -->
                        <div class="accordion-item mb-3 faq-item" data-category="returns">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    <span class="badge-custom me-3">Returns</span>
                                    What is your return policy?
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <strong>Our return policy:</strong>
                                    <ul class="mt-2">
                                        <li><strong>Return Window:</strong> 30 days from delivery date</li>
                                        <li><strong>Condition:</strong> Items must be unused, with tags attached</li>
                                        <li><strong>Original Packaging:</strong> Return in original packaging when possible</li>
                                        <li><strong>Refund Processing:</strong> 5-7 business days after we receive the item</li>
                                    </ul>
                                    
                                    <div class="alert alert-success mt-3">
                                        <i class="bi bi-check-circle me-2"></i>
                                        <strong>Free Returns:</strong> We provide free return shipping labels for most returns.
                                    </div>
                                    
                                    <strong>Non-returnable items:</strong>
                                    <ul class="mt-2">
                                        <li>Underwear and intimate apparel</li>
                                        <li>Personalized or customized items</li>
                                        <li>Items marked as "Final Sale"</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 faq-item" data-category="returns">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                    <span class="badge-custom me-3">Returns</span>
                                    How do I initiate a return?
                                </button>
                            </h2>
                            <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <strong>Easy return process:</strong>
                                    <ol class="mt-2">
                                        <li><strong>Login to Your Account:</strong> Go to your order history</li>
                                        <li><strong>Select Return:</strong> Choose the item(s) you want to return</li>
                                        <li><strong>Specify Reason:</strong> Let us know why you're returning the item</li>
                                        <li><strong>Print Label:</strong> We'll email you a prepaid return shipping label</li>
                                        <li><strong>Package Item:</strong> Securely package the item with the return form</li>
                                        <li><strong>Ship Back:</strong> Drop off at any of our partner locations</li>
                                    </ol>
                                    
                                    <div class="alert alert-info mt-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        You can also initiate returns by contacting our customer service team.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sizing FAQs -->
                        <div class="accordion-item mb-3 faq-item" data-category="sizing">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                    <span class="badge-custom me-3">Sizing</span>
                                    How do I find the right size?
                                </button>
                            </h2>
                            <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <strong>Finding your perfect fit:</strong>
                                    <ul class="mt-2">
                                        <li><strong>Size Chart:</strong> Check our detailed size chart on each product page</li>
                                        <li><strong>Measurements:</strong> Use a measuring tape for accurate body measurements</li>
                                        <li><strong>Product Reviews:</strong> Read customer reviews for sizing feedback</li>
                                        <li><strong>Size Recommendations:</strong> Use our size recommendation tool</li>
                                    </ul>
                                    
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <strong>How to Measure:</strong>
                                                </div>
                                                <div class="card-body">
                                                    <small>
                                                        <strong>Chest:</strong> Measure around the fullest part<br>
                                                        <strong>Waist:</strong> Measure around your natural waistline<br>
                                                        <strong>Hips:</strong> Measure around the widest part
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <strong>Still Unsure?</strong>
                                                </div>
                                                <div class="card-body">
                                                    <small>
                                                        Order multiple sizes and return the ones that don't fit. 
                                                        We offer free returns within 30 days!
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account FAQs -->
                        <div class="accordion-item mb-3 faq-item" data-category="account">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                                    <span class="badge-custom me-3">Account</span>
                                    How do I create an account?
                                </button>
                            </h2>
                            <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <strong>Creating an account is easy and free:</strong>
                                    <ol class="mt-2">
                                        <li>Click "Sign Up" in the top right corner</li>
                                        <li>Enter your email address and create a password</li>
                                        <li>Verify your email address</li>
                                        <li>Complete your profile information</li>
                                    </ol>
                                    
                                    <div class="alert alert-success mt-3">
                                        <i class="bi bi-gift me-2"></i>
                                        <strong>Welcome Bonus:</strong> Get 10% off your first order when you create an account!
                                    </div>
                                    
                                    <strong>Account Benefits:</strong>
                                    <ul class="mt-2">
                                        <li>Faster checkout process</li>
                                        <li>Order history and tracking</li>
                                        <li>Wishlist and saved items</li>
                                        <li>Exclusive member offers</li>
                                        <li>Early access to sales</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 faq-item" data-category="account">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq10">
                                    <span class="badge-custom me-3">Account</span>
                                    I forgot my password. How can I reset it?
                                </button>
                            </h2>
                            <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <strong>Password reset is simple:</strong>
                                    <ol class="mt-2">
                                        <li>Go to the login page</li>
                                        <li>Click "Forgot Password?"</li>
                                        <li>Enter your email address</li>
                                        <li>Check your email for a reset link</li>
                                        <li>Click the link and create a new password</li>
                                    </ol>
                                    
                                    <div class="alert alert-warning mt-3">
                                        <i class="bi bi-clock me-2"></i>
                                        Password reset links expire after 1 hour for security reasons.
                                    </div>
                                    
                                    <strong>Tips for a strong password:</strong>
                                    <ul class="mt-2">
                                        <li>Use at least 8 characters</li>
                                        <li>Include uppercase and lowercase letters</li>
                                        <li>Add numbers and special characters</li>
                                        <li>Avoid common words or personal information</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title text-center">Still Need Help?</h2>
                    <p class="text-center text-muted mb-5">
                        Can't find the answer you're looking for? Our friendly customer support team is here to help!
                    </p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="contact-card">
                        <i class="bi bi-chat-dots mb-3" style="font-size: 3rem;"></i>
                        <h5>Live Chat</h5>
                        <p class="mb-3">Get instant help from our support team</p>
                        <button class="btn btn-light btn-sm">Start Chat</button>
                        <div class="mt-2">
                            <small>Available 24/7</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="contact-card">
                        <i class="bi bi-envelope mb-3" style="font-size: 3rem;"></i>
                        <h5>Email Support</h5>
                        <p class="mb-3">Send us your questions anytime</p>
                        <a href="mailto:support@yourcompany.com" class="btn btn-light btn-sm">Send Email</a>
                        <div class="mt-2">
                            <small>Response within 24 hours</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="contact-card">
                        <i class="bi bi-telephone mb-3" style="font-size: 3rem;"></i>
                        <h5>Phone Support</h5>
                        <p class="mb-3">Speak directly with our team</p>
                        <a href="tel:+15551234567" class="btn btn-light btn-sm">Call Now</a>
                        <div class="mt-2">
                            <small>Mon-Fri: 9AM-6PM</small>
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
                        We're committed to providing exceptional products and services 
                        that exceed our customers' expectations.
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
                        <li><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Contact Us</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Returns</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Shipping Info</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3">Contact Info</h6>
                    <div class="text-muted">
                        <p><i class="bi bi-geo-alt me-2"></i>123 Business Street, City, State 12345</p>
                        <p><i class="bi bi-telephone me-2"></i>+1 (555) 123-4567</p>
                        <p><i class="bi bi-envelope me-2"></i>info@yourcompany.com</p>
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
        // FAQ Search Functionality
        const searchInput = document.getElementById('faqSearch');
        const faqItems = document.querySelectorAll('.faq-item');
        const categories = document.querySelectorAll('.faq-category');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let hasResults = false;

            faqItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = 'block';
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                }
            });

            // Show "no results" message if needed
            if (!hasResults && searchTerm) {
                showNoResults(true);
            } else {
                showNoResults(false);
            }
        });

        // Category Filtering
        categories.forEach(category => {
            category.addEventListener('click', function() {
                const selectedCategory = this.dataset.category;

                // Update active state
                categories.forEach(cat => cat.classList.remove('active'));
                this.classList.add('active');

                // Filter FAQ items
                faqItems.forEach(item => {
                    if (selectedCategory === 'all' || item.dataset.category === selectedCategory) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Clear search
                searchInput.value = '';
                showNoResults(false);
            });
        });

        // Set initial active category
        document.querySelector('[data-category="all"]').classList.add('active');

        // Show/Hide no results message
        function showNoResults(show) {
            let noResultsMsg = document.getElementById('noResultsMessage');
            
            if (show && !noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.id = 'noResultsMessage';
                noResultsMsg.className = 'text-center py-5';
                noResultsMsg.innerHTML = `
                    <div class="text-muted">
                        <i class="bi bi-search mb-3" style="font-size: 3rem;"></i>
                        <h5>No results found</h5>
                        <p>Try different keywords or <a href="#contact">contact us</a> for help.</p>
                    </div>
                `;
                document.getElementById('faqAccordion').appendChild(noResultsMsg);
            } else if (!show && noResultsMsg) {
                noResultsMsg.remove();
            }
        }

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

        // Auto-expand FAQ if URL has hash
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
    </script>
</body>
</html>