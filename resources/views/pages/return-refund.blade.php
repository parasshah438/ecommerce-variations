<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return & Refund Policy - Your Company</title>
    
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
            --danger-color: #dc3545;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 100px 0 60px;
        }
        
        .policy-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        
        .policy-card:hover {
            transform: translateY(-5px);
        }
        
        .return-step {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            height: 100%;
            position: relative;
        }
        
        .return-step::after {
            content: attr(data-step);
            position: absolute;
            top: -15px;
            left: 20px;
            background: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .eligibility-card {
            border-left: 5px solid var(--success-color);
            background: #d4edda;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .restriction-card {
            border-left: 5px solid var(--danger-color);
            background: #f8d7da;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .refund-timeline {
            position: relative;
            padding: 2rem 0;
        }
        
        .timeline-item {
            position: relative;
            padding-left: 4rem;
            margin-bottom: 2rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 1.5rem;
            top: 0;
            bottom: -2rem;
            width: 2px;
            background: var(--primary-color);
        }
        
        .timeline-item::after {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0.5rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: var(--accent-color);
        }
        
        .timeline-item:last-child::before {
            display: none;
        }
        
        .icon-xl {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .highlight-box {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            border-left: 5px solid var(--primary-color);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 8px;
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
                            <li><a class="dropdown-item" href="{{ route('pages.shipping') }}">Shipping Policy</a></li>
                            <li><a class="dropdown-item active" href="{{ route('pages.return.refund') }}">Return & Refund</a></li>
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
                        <i class="bi bi-arrow-left-circle me-3"></i>Return & Refund Policy
                    </h1>
                    <p class="lead mb-4">
                        Shop with confidence! We offer hassle-free returns and quick refunds to ensure 
                        your complete satisfaction with every purchase.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#return-process" class="btn btn-light btn-lg">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Return Process
                        </a>
                        <a href="#refund-policy" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-cash me-2"></i>Refund Policy
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <img src="https://via.placeholder.com/500x400/ffffff/007bff?text=Easy+Returns" 
                             alt="Easy Returns" class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Overview Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center mb-5">
                        <i class="bi bi-clock-history me-2 text-primary"></i>
                        Return Policy Overview
                    </h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <i class="bi bi-calendar-check text-success icon-xl"></i>
                        <h5>30-Day Window</h5>
                        <p class="text-muted">Return items within 30 days of delivery for a full refund</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <i class="bi bi-truck text-primary icon-xl"></i>
                        <h5>Free Return Shipping</h5>
                        <p class="text-muted">We provide prepaid return labels for most items</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <i class="bi bi-cash-coin text-warning icon-xl"></i>
                        <h5>Quick Refunds</h5>
                        <p class="text-muted">Refunds processed within 3-5 business days</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <i class="bi bi-shield-check text-info icon-xl"></i>
                        <h5>Quality Guarantee</h5>
                        <p class="text-muted">100% satisfaction guarantee on all purchases</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Return Eligibility Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center mb-5">
                        <i class="bi bi-check-circle me-2 text-primary"></i>
                        What Can Be Returned?
                    </h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="eligibility-card">
                        <h5><i class="bi bi-check-circle text-success me-2"></i>Returnable Items</h5>
                        <ul class="list-unstyled mb-0">
                            <li><i class="bi bi-check text-success me-2"></i>Items with original tags and packaging</li>
                            <li><i class="bi bi-check text-success me-2"></i>Unused and unworn products</li>
                            <li><i class="bi bi-check text-success me-2"></i>Items returned within 30 days</li>
                            <li><i class="bi bi-check text-success me-2"></i>Products in original condition</li>
                            <li><i class="bi bi-check text-success me-2"></i>Items with proof of purchase</li>
                            <li><i class="bi bi-check text-success me-2"></i>Defective or damaged items</li>
                            <li><i class="bi bi-check text-success me-2"></i>Wrong size or color received</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="restriction-card">
                        <h5><i class="bi bi-x-circle text-danger me-2"></i>Non-Returnable Items</h5>
                        <ul class="list-unstyled mb-0">
                            <li><i class="bi bi-x text-danger me-2"></i>Intimate apparel and underwear</li>
                            <li><i class="bi bi-x text-danger me-2"></i>Personalized or customized items</li>
                            <li><i class="bi bi-x text-danger me-2"></i>Items marked as "Final Sale"</li>
                            <li><i class="bi bi-x text-danger me-2"></i>Used or worn items</li>
                            <li><i class="bi bi-x text-danger me-2"></i>Items without tags or packaging</li>
                            <li><i class="bi bi-x text-danger me-2"></i>Cosmetics and beauty products</li>
                            <li><i class="bi bi-x text-danger me-2"></i>Gift cards and digital products</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Return Process Section -->
    <section id="return-process" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center mb-5">
                        <i class="bi bi-arrow-repeat me-2 text-primary"></i>
                        How to Return Your Items
                    </h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="return-step" data-step="1">
                        <i class="bi bi-person-circle text-primary icon-xl"></i>
                        <h5>Initiate Return</h5>
                        <p class="text-muted">
                            Log into your account and go to "My Orders" to start the return process. 
                            Select the items you want to return and specify the reason.
                        </p>
                        <div class="mt-3">
                            <a href="#" class="btn btn-outline-primary btn-sm">Start Return</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="return-step" data-step="2">
                        <i class="bi bi-printer text-success icon-xl"></i>
                        <h5>Print Return Label</h5>
                        <p class="text-muted">
                            We'll email you a prepaid return shipping label. Print it out and attach 
                            it to your package. No need to pay for return shipping!
                        </p>
                        <div class="mt-3">
                            <small class="text-success">Free return shipping included</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="return-step" data-step="3">
                        <i class="bi bi-box text-warning icon-xl"></i>
                        <h5>Pack Your Items</h5>
                        <p class="text-muted">
                            Pack items securely in the original packaging if possible. Include the 
                            return form and any accessories that came with the product.
                        </p>
                        <div class="mt-3">
                            <small class="text-warning">Keep original packaging when possible</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="return-step" data-step="4">
                        <i class="bi bi-truck text-info icon-xl"></i>
                        <h5>Ship Your Return</h5>
                        <p class="text-muted">
                            Drop off your package at any authorized shipping location or schedule 
                            a pickup. You'll receive a tracking number to monitor your return.
                        </p>
                        <div class="mt-3">
                            <small class="text-info">Tracking included for peace of mind</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="return-step" data-step="5">
                        <i class="bi bi-search text-secondary icon-xl"></i>
                        <h5>Inspection Process</h5>
                        <p class="text-muted">
                            Once we receive your return, our quality team will inspect the items 
                            within 2-3 business days to ensure they meet return criteria.
                        </p>
                        <div class="mt-3">
                            <small class="text-secondary">Quick 2-3 day inspection</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="return-step" data-step="6">
                        <i class="bi bi-cash-coin text-success icon-xl"></i>
                        <h5>Refund Processed</h5>
                        <p class="text-muted">
                            After approval, your refund will be processed back to your original 
                            payment method within 3-5 business days. You'll receive confirmation.
                        </p>
                        <div class="mt-3">
                            <small class="text-success">Quick refund processing</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Refund Policy Section -->
    <section id="refund-policy" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center mb-5">
                        <i class="bi bi-cash me-2 text-primary"></i>
                        Refund Policy & Timeline
                    </h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    <div class="policy-card">
                        <h4 class="mb-4">Refund Processing Timeline</h4>
                        
                        <div class="refund-timeline">
                            <div class="timeline-item">
                                <h6>Return Initiated</h6>
                                <p class="text-muted mb-1">Customer starts return process online</p>
                                <small class="text-primary">Day 1</small>
                            </div>
                            
                            <div class="timeline-item">
                                <h6>Item Shipped Back</h6>
                                <p class="text-muted mb-1">Package shipped using our prepaid label</p>
                                <small class="text-primary">Day 2-3</small>
                            </div>
                            
                            <div class="timeline-item">
                                <h6>Package Received</h6>
                                <p class="text-muted mb-1">Return arrives at our processing center</p>
                                <small class="text-primary">Day 5-7</small>
                            </div>
                            
                            <div class="timeline-item">
                                <h6>Quality Inspection</h6>
                                <p class="text-muted mb-1">Items inspected for return eligibility</p>
                                <small class="text-primary">Day 8-10</small>
                            </div>
                            
                            <div class="timeline-item">
                                <h6>Refund Approved</h6>
                                <p class="text-muted mb-1">Refund processed to original payment method</p>
                                <small class="text-success">Day 10-12</small>
                            </div>
                            
                            <div class="timeline-item">
                                <h6>Money in Account</h6>
                                <p class="text-muted mb-1">Refund appears in customer's account</p>
                                <small class="text-success">Day 12-15</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="policy-card">
                        <h5><i class="bi bi-credit-card text-primary me-2"></i>Payment Methods</h5>
                        
                        <div class="mb-4">
                            <h6>Credit/Debit Cards</h6>
                            <p class="text-muted small">3-5 business days to appear on statement</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6>UPI/Digital Wallets</h6>
                            <p class="text-muted small">1-3 business days to reflect balance</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6>Net Banking</h6>
                            <p class="text-muted small">3-7 business days depending on bank</p>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>COD orders are refunded via bank transfer</small>
                        </div>
                    </div>

                    <div class="policy-card">
                        <h5><i class="bi bi-arrow-left-right text-success me-2"></i>Exchange Option</h5>
                        <p class="text-muted">
                            Want a different size or color? Choose exchange instead of return 
                            for faster processing.
                        </p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>Same product, different variant</li>
                            <li><i class="bi bi-check text-success me-2"></i>No additional shipping cost</li>
                            <li><i class="bi bi-check text-success me-2"></i>Faster than return + new order</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Special Circumstances Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center mb-5">
                        <i class="bi bi-exclamation-triangle me-2 text-primary"></i>
                        Special Circumstances
                    </h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="policy-card">
                        <h4><i class="bi bi-exclamation-diamond text-warning me-2"></i>Damaged or Defective Items</h4>
                        <p>Received a damaged or defective product? We'll make it right immediately.</p>
                        
                        <h6>What to do:</h6>
                        <ol>
                            <li>Contact us within 48 hours of delivery</li>
                            <li>Provide photos of the damaged item</li>
                            <li>We'll arrange immediate replacement or refund</li>
                            <li>No need to return damaged items in many cases</li>
                        </ol>
                        
                        <div class="highlight-box">
                            <h6><i class="bi bi-shield-check text-success me-2"></i>Our Promise</h6>
                            <p class="mb-0">
                                Damaged or defective items are eligible for immediate refund or replacement, 
                                even beyond our standard return window.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="policy-card">
                        <h4><i class="bi bi-x-octagon text-danger me-2"></i>Wrong Item Received</h4>
                        <p>We occasionally make mistakes. If you received the wrong item, we'll fix it fast.</p>
                        
                        <h6>Our process:</h6>
                        <ol>
                            <li>Contact customer service immediately</li>
                            <li>We'll send the correct item right away</li>
                            <li>Prepaid return label for wrong item</li>
                            <li>No charges for our mistake</li>
                        </ol>
                        
                        <div class="highlight-box">
                            <h6><i class="bi bi-lightning-charge text-warning me-2"></i>Priority Handling</h6>
                            <p class="mb-0">
                                Wrong item shipments are processed with highest priority. 
                                Correct items typically ship within 24 hours.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="policy-card">
                        <h4><i class="bi bi-gift text-info me-2"></i>Gift Returns</h4>
                        <p>Returning a gift? No problem! We make gift returns simple and discrete.</p>
                        
                        <h6>Gift return options:</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>Store credit (no receipt needed)</li>
                            <li><i class="bi bi-check text-success me-2"></i>Exchange for different item</li>
                            <li><i class="bi bi-check text-success me-2"></i>Refund to gift-giver (with receipt)</li>
                            <li><i class="bi bi-check text-success me-2"></i>Gift receipt helps track original order</li>
                        </ul>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Gift returns follow the same 30-day window from delivery date.
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="policy-card">
                        <h4><i class="bi bi-percent text-success me-2"></i>Sale Item Returns</h4>
                        <p>Sale items follow the same return policy as regular-priced items.</p>
                        
                        <h6>Sale return policy:</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>Same 30-day return window</li>
                            <li><i class="bi bi-check text-success me-2"></i>Full refund of amount paid</li>
                            <li><i class="bi bi-check text-success me-2"></i>Free return shipping included</li>
                            <li><i class="bi bi-x text-danger me-2"></i>"Final Sale" items are excluded</li>
                        </ul>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Items marked "Final Sale" cannot be returned or exchanged.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact & FAQ Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="policy-card">
                        <h4><i class="bi bi-question-circle text-primary me-2"></i>Frequently Asked Questions</h4>
                        
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        Can I return items after 30 days?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Our standard return window is 30 days. However, we may make exceptions for 
                                        defective items, wrong items shipped, or other special circumstances. 
                                        Contact customer service to discuss your specific situation.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Do I have to pay for return shipping?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        No! We provide free return shipping labels for most returns. The only exception 
                                        is if you're returning an item simply because you changed your mind and the 
                                        item was under ₹999. In that case, return shipping costs ₹50.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        How long does the refund take?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Once we receive and process your return (2-3 business days), refunds are 
                                        issued within 3-5 business days. The time for the money to appear in your 
                                        account depends on your bank/payment provider, typically 3-7 business days.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="policy-card">
                        <h4><i class="bi bi-headset text-success me-2"></i>Need Help?</h4>
                        <p>Our customer service team is here to help with any return questions.</p>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('pages.help') }}" class="btn btn-primary">
                                <i class="bi bi-chat-dots me-2"></i>Live Chat
                            </a>
                            <a href="mailto:returns@yourcompany.com" class="btn btn-outline-primary">
                                <i class="bi bi-envelope me-2"></i>Email Support
                            </a>
                            <a href="tel:+15551234567" class="btn btn-outline-success">
                                <i class="bi bi-telephone me-2"></i>Call Us
                            </a>
                        </div>
                        
                        <div class="mt-4">
                            <h6><i class="bi bi-clock text-info me-2"></i>Support Hours</h6>
                            <p class="text-muted small mb-1">Monday - Friday: 9 AM - 9 PM</p>
                            <p class="text-muted small mb-1">Saturday: 10 AM - 6 PM</p>
                            <p class="text-muted small mb-0">Sunday: 10 AM - 4 PM</p>
                        </div>
                        
                        <div class="alert alert-success mt-3">
                            <i class="bi bi-telephone-fill me-2"></i>
                            <strong>Returns Hotline:</strong><br>
                            <a href="tel:+15551234999" class="text-decoration-none">+1 (555) 123-4999</a>
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
                        Customer satisfaction is our priority. Easy returns, quick refunds, 
                        and excellent service make shopping with us risk-free.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Returns</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.return.refund') }}" class="text-muted text-decoration-none">Return Policy</a></li>
                        <li><a href="#return-process" class="text-muted text-decoration-none">Return Process</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Start Return</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Exchange Items</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.help') }}" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="{{ route('pages.faq') }}" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Contact Us</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Order Status</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3">Return Support</h6>
                    <div class="text-muted">
                        <p><i class="bi bi-telephone me-2"></i>Returns: +1 (555) 123-4999</p>
                        <p><i class="bi bi-envelope me-2"></i>returns@yourcompany.com</p>
                        <p><i class="bi bi-clock me-2"></i>Mon-Fri: 9AM-9PM, Weekends: 10AM-6PM</p>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center text-muted">
                <p>&copy; {{ date('Y') }} Your Company. All rights reserved. | 30-Day Return Guarantee</p>
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

        // Return process simulation
        document.querySelector('.btn-outline-primary[href="#"]').addEventListener('click', function(e) {
            e.preventDefault();
            alert('Return process will redirect to your account dashboard. Please log in to start a return.');
        });

        // Add floating return button
        document.addEventListener('DOMContentLoaded', function() {
            const floatingReturn = document.createElement('div');
            floatingReturn.innerHTML = `
                <button class="btn btn-success rounded-circle shadow-lg" 
                        style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; width: 60px; height: 60px;"
                        onclick="scrollToSection('#return-process')" title="Start Return">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            `;
            document.body.appendChild(floatingReturn);
        });

        function scrollToSection(selector) {
            const target = document.querySelector(selector);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    </script>
</body>
</html>