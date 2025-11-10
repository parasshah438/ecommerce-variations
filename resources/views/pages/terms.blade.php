<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - Your Company</title>
    
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
            padding: 100px 0 60px;
        }
        
        .table-of-contents {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: sticky;
            top: 100px;
        }
        
        .table-of-contents ul {
            list-style: none;
            padding-left: 0;
        }
        
        .table-of-contents ul li {
            margin-bottom: 0.5rem;
        }
        
        .table-of-contents ul li a {
            text-decoration: none;
            color: var(--primary-color);
            transition: all 0.3s ease;
            display: block;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }
        
        .table-of-contents ul li a:hover,
        .table-of-contents ul li a.active {
            background: var(--primary-color);
            color: white;
            transform: translateX(10px);
        }
        
        .content-section {
            margin-bottom: 3rem;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .content-section h3 {
            color: var(--primary-color);
            border-bottom: 3px solid var(--accent-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .important-notice {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border-left: 5px solid #ffc107;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 8px;
        }
        
        .terms-highlight {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            border-left: 5px solid var(--primary-color);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 8px;
        }
        
        .restriction-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 1.5rem;
        }
        
        .contact-info {
            background: var(--primary-color);
            color: white;
            border-radius: 15px;
            padding: 2rem;
        }
        
        .last-updated {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .policy-card {
            border-left: 4px solid var(--accent-color);
            padding: 1.5rem;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            margin-bottom: 1.5rem;
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
                        <a class="nav-link" href="{{ route('pages.help') }}">Help & Support</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pages.privacy') }}">Privacy Policy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('pages.terms') }}">Terms & Conditions</a>
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
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="bi bi-file-earmark-text me-3"></i>Terms & Conditions
                    </h1>
                    <p class="lead mb-4">
                        These terms and conditions govern your use of our website and services. 
                        Please read them carefully before making any purchases or using our services.
                    </p>
                    <div class="last-updated text-start">
                        <i class="bi bi-calendar-check text-success me-2"></i>
                        <strong>Last Updated:</strong> {{ date('F d, Y') }}
                        <span class="badge bg-success ms-2">Version 2.1</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Table of Contents -->
                <div class="col-lg-4">
                    <div class="table-of-contents">
                        <h5 class="mb-3">
                            <i class="bi bi-list-ul me-2"></i>Table of Contents
                        </h5>
                        <ul>
                            <li><a href="#acceptance">1. Acceptance of Terms</a></li>
                            <li><a href="#definitions">2. Definitions</a></li>
                            <li><a href="#account">3. User Accounts</a></li>
                            <li><a href="#orders">4. Orders & Payment</a></li>
                            <li><a href="#shipping">5. Shipping & Delivery</a></li>
                            <li><a href="#returns">6. Returns & Refunds</a></li>
                            <li><a href="#prohibited">7. Prohibited Uses</a></li>
                            <li><a href="#intellectual">8. Intellectual Property</a></li>
                            <li><a href="#limitation">9. Limitation of Liability</a></li>
                            <li><a href="#indemnification">10. Indemnification</a></li>
                            <li><a href="#privacy">11. Privacy Policy</a></li>
                            <li><a href="#modifications">12. Modifications</a></li>
                            <li><a href="#governing">13. Governing Law</a></li>
                            <li><a href="#contact">14. Contact Information</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Content -->
                <div class="col-lg-8">
                    <!-- Acceptance of Terms -->
                    <div id="acceptance" class="content-section">
                        <h3><i class="bi bi-check-circle me-2"></i>1. Acceptance of Terms</h3>
                        
                        <div class="important-notice">
                            <h5><i class="bi bi-exclamation-triangle text-warning me-2"></i>Important Notice</h5>
                            <p class="mb-0">
                                By accessing and using this website, you accept and agree to be bound by the terms 
                                and provision of this agreement.
                            </p>
                        </div>
                        
                        <p>
                            Welcome to Your Company ("Company," "we," "our," or "us"). These Terms and Conditions 
                            ("Terms") govern your use of our website located at yourcompany.com (the "Service") 
                            operated by Your Company.
                        </p>
                        
                        <p>
                            Our Privacy Policy also governs your use of the Service and explains how we collect, 
                            safeguard and disclose information that results from your use of our web pages.
                        </p>
                        
                        <p>
                            If you do not agree with these Terms, please do not access or use our Service.
                        </p>
                    </div>

                    <!-- Definitions -->
                    <div id="definitions" class="content-section">
                        <h3><i class="bi bi-book me-2"></i>2. Definitions</h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="policy-card">
                                    <h5>"Service"</h5>
                                    <p class="mb-0">Refers to the website and online store operated by Your Company.</p>
                                </div>
                                
                                <div class="policy-card">
                                    <h5>"User" or "You"</h5>
                                    <p class="mb-0">Refers to the individual accessing or using the Service.</p>
                                </div>
                                
                                <div class="policy-card">
                                    <h5>"Company"</h5>
                                    <p class="mb-0">Refers to Your Company, the owner and operator of the Service.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="policy-card">
                                    <h5>"Products"</h5>
                                    <p class="mb-0">Refers to the goods offered for sale through our Service.</p>
                                </div>
                                
                                <div class="policy-card">
                                    <h5>"Order"</h5>
                                    <p class="mb-0">Refers to a request to purchase Products from our Service.</p>
                                </div>
                                
                                <div class="policy-card">
                                    <h5>"Account"</h5>
                                    <p class="mb-0">Refers to the user profile created to access our Service.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Accounts -->
                    <div id="account" class="content-section">
                        <h3><i class="bi bi-person-circle me-2"></i>3. User Accounts</h3>
                        
                        <h5>Account Creation</h5>
                        <p>
                            When you create an account with us, you must provide information that is accurate, 
                            complete, and current at all times. You are responsible for safeguarding the password 
                            and for all activities under your account.
                        </p>
                        
                        <h5>Account Responsibilities</h5>
                        <ul>
                            <li>Maintain the confidentiality of your account and password</li>
                            <li>Notify us immediately of any unauthorized use of your account</li>
                            <li>Ensure all information provided is accurate and up-to-date</li>
                            <li>You are responsible for all activities under your account</li>
                        </ul>
                        
                        <div class="terms-highlight">
                            <h5><i class="bi bi-shield-check text-primary me-2"></i>Account Security</h5>
                            <p class="mb-0">
                                We reserve the right to terminate accounts that violate these Terms or engage 
                                in fraudulent activities.
                            </p>
                        </div>
                    </div>

                    <!-- Orders & Payment -->
                    <div id="orders" class="content-section">
                        <h3><i class="bi bi-credit-card me-2"></i>4. Orders & Payment</h3>
                        
                        <h5>Order Process</h5>
                        <ol>
                            <li><strong>Product Selection:</strong> Choose your desired products and add to cart</li>
                            <li><strong>Checkout:</strong> Provide shipping and billing information</li>
                            <li><strong>Payment:</strong> Complete payment using accepted methods</li>
                            <li><strong>Confirmation:</strong> Receive order confirmation email</li>
                        </ol>
                        
                        <h5>Accepted Payment Methods</h5>
                        <div class="row text-center">
                            <div class="col-md-3">
                                <i class="bi bi-credit-card text-primary" style="font-size: 2rem;"></i>
                                <p class="mt-2"><strong>Credit/Debit Cards</strong></p>
                            </div>
                            <div class="col-md-3">
                                <i class="bi bi-paypal text-primary" style="font-size: 2rem;"></i>
                                <p class="mt-2"><strong>PayPal</strong></p>
                            </div>
                            <div class="col-md-3">
                                <i class="bi bi-phone text-primary" style="font-size: 2rem;"></i>
                                <p class="mt-2"><strong>UPI/Wallets</strong></p>
                            </div>
                            <div class="col-md-3">
                                <i class="bi bi-cash text-primary" style="font-size: 2rem;"></i>
                                <p class="mt-2"><strong>Cash on Delivery</strong></p>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Pricing:</strong> All prices are listed in Indian Rupees (INR) and include applicable taxes unless otherwise stated.
                        </div>
                        
                        <h5>Order Acceptance</h5>
                        <p>
                            Your receipt of an order confirmation does not signify our acceptance of your order, 
                            nor does it constitute confirmation of our offer to sell. We reserve the right to 
                            accept or decline your order for any reason.
                        </p>
                    </div>

                    <!-- Shipping & Delivery -->
                    <div id="shipping" class="content-section">
                        <h3><i class="bi bi-truck me-2"></i>5. Shipping & Delivery</h3>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Shipping Method</th>
                                        <th>Delivery Time</th>
                                        <th>Cost</th>
                                        <th>Tracking</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Standard Shipping</td>
                                        <td>5-7 business days</td>
                                        <td>₹50 (Free on orders ₹999+)</td>
                                        <td>Yes</td>
                                    </tr>
                                    <tr>
                                        <td>Express Shipping</td>
                                        <td>2-3 business days</td>
                                        <td>₹150</td>
                                        <td>Yes</td>
                                    </tr>
                                    <tr>
                                        <td>Same Day Delivery</td>
                                        <td>Same day (select cities)</td>
                                        <td>₹300</td>
                                        <td>Yes</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5>Delivery Terms</h5>
                        <ul>
                            <li>Delivery times are estimates and may vary due to unforeseen circumstances</li>
                            <li>Risk of loss transfers to you upon delivery to the carrier</li>
                            <li>Someone must be available to receive the delivery</li>
                            <li>We are not responsible for delays caused by shipping carriers</li>
                            <li>International shipping is not currently available</li>
                        </ul>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Delivery Delays:</strong> During peak seasons or special circumstances, 
                            deliveries may take longer than usual. We will notify you of any significant delays.
                        </div>
                    </div>

                    <!-- Returns & Refunds -->
                    <div id="returns" class="content-section">
                        <h3><i class="bi bi-arrow-left-circle me-2"></i>6. Returns & Refunds</h3>
                        
                        <h5>Return Policy</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <strong>Returnable Items</strong>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <li><i class="bi bi-check text-success me-2"></i>Unused items with tags</li>
                                            <li><i class="bi bi-check text-success me-2"></i>Items in original packaging</li>
                                            <li><i class="bi bi-check text-success me-2"></i>Within 30 days of delivery</li>
                                            <li><i class="bi bi-check text-success me-2"></i>Defective or damaged items</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-danger text-white">
                                        <strong>Non-Returnable Items</strong>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <li><i class="bi bi-x text-danger me-2"></i>Intimate apparel</li>
                                            <li><i class="bi bi-x text-danger me-2"></i>Personalized items</li>
                                            <li><i class="bi bi-x text-danger me-2"></i>Items marked "Final Sale"</li>
                                            <li><i class="bi bi-x text-danger me-2"></i>Used or damaged items</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h5>Refund Process</h5>
                        <ol>
                            <li>Initiate return request through your account or customer service</li>
                            <li>Receive return authorization and shipping label</li>
                            <li>Package items securely and ship back to us</li>
                            <li>Inspection of returned items (2-3 business days)</li>
                            <li>Refund processed to original payment method (5-7 business days)</li>
                        </ol>
                        
                        <div class="terms-highlight">
                            <h5><i class="bi bi-cash text-success me-2"></i>Refund Timeline</h5>
                            <p class="mb-0">
                                Refunds are processed within 5-7 business days after we receive and inspect 
                                the returned items. Credit card refunds may take additional time to appear on your statement.
                            </p>
                        </div>
                    </div>

                    <!-- Prohibited Uses -->
                    <div id="prohibited" class="content-section">
                        <h3><i class="bi bi-x-circle me-2"></i>7. Prohibited Uses</h3>
                        
                        <div class="restriction-box">
                            <h5><i class="bi bi-exclamation-triangle text-danger me-2"></i>You may not use our Service:</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul>
                                        <li>For any unlawful purpose</li>
                                        <li>To violate any international, federal, provincial, or state regulations or laws</li>
                                        <li>To transmit or procure spam, phishing, or worms</li>
                                        <li>To interfere with or disrupt the Service</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul>
                                        <li>To submit false or misleading information</li>
                                        <li>To upload viruses or malicious code</li>
                                        <li>To collect user information without permission</li>
                                        <li>To impersonate any person or entity</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <p>
                            We reserve the right to terminate your use of the Service for violating any 
                            of the prohibited uses.
                        </p>
                    </div>

                    <!-- Intellectual Property -->
                    <div id="intellectual" class="content-section">
                        <h3><i class="bi bi-c-circle me-2"></i>8. Intellectual Property Rights</h3>
                        
                        <p>
                            The Service and its original content, features, and functionality are and will remain 
                            the exclusive property of Your Company and its licensors.
                        </p>
                        
                        <h5>Protected Content Includes:</h5>
                        <ul>
                            <li>Website design and layout</li>
                            <li>Product images and descriptions</li>
                            <li>Company logos and trademarks</li>
                            <li>Software and code</li>
                            <li>Written content and copy</li>
                        </ul>
                        
                        <div class="alert alert-danger">
                            <i class="bi bi-shield-exclamation me-2"></i>
                            <strong>Copyright Notice:</strong> Unauthorized use of our intellectual property 
                            may result in legal action and monetary damages.
                        </div>
                    </div>

                    <!-- Limitation of Liability -->
                    <div id="limitation" class="content-section">
                        <h3><i class="bi bi-shield-exclamation me-2"></i>9. Limitation of Liability</h3>
                        
                        <div class="important-notice">
                            <h5><i class="bi bi-exclamation-triangle text-warning me-2"></i>Disclaimer</h5>
                            <p class="mb-0">
                                Our Service is provided on an "AS IS" and "AS AVAILABLE" basis. Your Company 
                                makes no warranties, expressed or implied.
                            </p>
                        </div>
                        
                        <p>
                            In no event shall Your Company, nor its directors, employees, partners, agents, 
                            suppliers, or affiliates, be liable for any indirect, incidental, special, 
                            consequential, or punitive damages.
                        </p>
                        
                        <h5>Limitation Scope:</h5>
                        <ul>
                            <li>Loss of profits or data</li>
                            <li>Business interruption</li>
                            <li>Personal injury or property damage</li>
                            <li>Service outages or errors</li>
                        </ul>
                    </div>

                    <!-- Indemnification -->
                    <div id="indemnification" class="content-section">
                        <h3><i class="bi bi-person-check me-2"></i>10. Indemnification</h3>
                        
                        <p>
                            You agree to defend, indemnify, and hold harmless Your Company and its licensee 
                            and licensors from and against any and all claims, damages, obligations, losses, 
                            liabilities, costs, or debt, and expenses (including but not limited to attorney's fees).
                        </p>
                        
                        <h5>Indemnification applies to claims arising from:</h5>
                        <ul>
                            <li>Your use of and access to the Service</li>
                            <li>Your violation of any term of these Terms</li>
                            <li>Your violation of any third-party right</li>
                            <li>Any claim that your use caused damage to a third party</li>
                        </ul>
                    </div>

                    <!-- Privacy Policy -->
                    <div id="privacy" class="content-section">
                        <h3><i class="bi bi-shield-check me-2"></i>11. Privacy Policy</h3>
                        
                        <p>
                            Your privacy is important to us. Our Privacy Policy explains how we collect, use, 
                            and protect your information when you use our Service.
                        </p>
                        
                        <div class="terms-highlight">
                            <h5><i class="bi bi-info-circle text-primary me-2"></i>Data Protection</h5>
                            <p class="mb-2">
                                By using our Service, you agree to the collection and use of information 
                                in accordance with our Privacy Policy.
                            </p>
                            <a href="{{ route('pages.privacy') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-right me-2"></i>Read Full Privacy Policy
                            </a>
                        </div>
                    </div>

                    <!-- Modifications -->
                    <div id="modifications" class="content-section">
                        <h3><i class="bi bi-arrow-clockwise me-2"></i>12. Changes to Terms</h3>
                        
                        <p>
                            We reserve the right to modify or replace these Terms at any time. If a revision 
                            is material, we will try to provide at least 30 days notice prior to any new terms taking effect.
                        </p>
                        
                        <h5>How We Notify You:</h5>
                        <ul>
                            <li>Email notification to registered users</li>
                            <li>Prominent notice on our website</li>
                            <li>In-app notifications (where applicable)</li>
                            <li>Updated "Last Modified" date on this page</li>
                        </ul>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Continued Use:</strong> Your continued use of the Service after changes 
                            become effective constitutes acceptance of the new Terms.
                        </div>
                    </div>

                    <!-- Governing Law -->
                    <div id="governing" class="content-section">
                        <h3><i class="bi bi-building me-2"></i>13. Governing Law & Jurisdiction</h3>
                        
                        <p>
                            These Terms shall be interpreted and governed by the laws of [Your State/Country], 
                            without regard to its conflict of law provisions.
                        </p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Dispute Resolution</h5>
                                <ol>
                                    <li><strong>Informal Resolution:</strong> Contact us directly</li>
                                    <li><strong>Mediation:</strong> Attempt mediation if needed</li>
                                    <li><strong>Arbitration:</strong> Binding arbitration as last resort</li>
                                    <li><strong>Legal Action:</strong> In courts of competent jurisdiction</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h5>Jurisdiction</h5>
                                <p>
                                    Our failure to enforce any right or provision of these Terms will not be 
                                    considered a waiver of those rights.
                                </p>
                                <p>
                                    If any provision of these Terms is held to be invalid or unenforceable, 
                                    the remaining provisions will remain in full force and effect.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div id="contact" class="content-section">
                        <h3><i class="bi bi-envelope me-2"></i>14. Contact Information</h3>
                        
                        <p>
                            If you have any questions about these Terms and Conditions, please contact us:
                        </p>

                        <div class="contact-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5><i class="bi bi-building me-2"></i>Company Information</h5>
                                    <p class="mb-0"><strong>Your Company, Inc.</strong></p>
                                    <p class="mb-0">Legal Department</p>
                                    <p class="mb-3">123 Business Street, Suite 456</p>
                                    <p class="mb-3">City, State 12345, Country</p>
                                    
                                    <h6><i class="bi bi-telephone me-2"></i>Phone:</h6>
                                    <p>+1 (555) 123-4567</p>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="bi bi-envelope me-2"></i>Email Contacts:</h6>
                                    <p><strong>General Inquiries:</strong> info@yourcompany.com</p>
                                    <p><strong>Legal Matters:</strong> legal@yourcompany.com</p>
                                    <p><strong>Customer Service:</strong> support@yourcompany.com</p>
                                    
                                    <h6><i class="bi bi-clock me-2"></i>Business Hours:</h6>
                                    <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                                    <p>Saturday: 10:00 AM - 4:00 PM</p>
                                    <p>Sunday: Closed</p>
                                </div>
                            </div>
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
                        Operating with transparency and integrity. These terms ensure a fair and 
                        secure shopping experience for all our customers.
                    </p>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Legal</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.terms') }}" class="text-muted text-decoration-none">Terms & Conditions</a></li>
                        <li><a href="{{ route('pages.privacy') }}" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Cookie Policy</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Disclaimer</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.help') }}" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="{{ route('pages.faq') }}" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Contact Us</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Shipping Info</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3">Questions About Terms?</h6>
                    <div class="text-muted">
                        <p>Need clarification on any terms?</p>
                        <a href="mailto:legal@yourcompany.com" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-envelope me-2"></i>Contact Legal Team
                        </a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center text-muted">
                <p>&copy; {{ date('Y') }} Your Company. All rights reserved. | 
                   <a href="{{ route('pages.terms') }}" class="text-decoration-none">Terms & Conditions</a> | 
                   <a href="{{ route('pages.privacy') }}" class="text-decoration-none">Privacy Policy</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Smooth scrolling for table of contents
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

        // Highlight active section in table of contents
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('.content-section');
            const tocLinks = document.querySelectorAll('.table-of-contents a');
            
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.getBoundingClientRect().top;
                if (sectionTop <= 150) {
                    current = section.getAttribute('id');
                }
            });
            
            tocLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').substring(1) === current) {
                    link.classList.add('active');
                }
            });
        });

        // Add print functionality
        function printTerms() {
            window.print();
        }

        // Add floating print button
        document.addEventListener('DOMContentLoaded', function() {
            const floatingPrint = document.createElement('div');
            floatingPrint.innerHTML = `
                <button class="btn btn-secondary rounded-circle shadow-lg" 
                        style="position: fixed; bottom: 80px; right: 20px; z-index: 1000; width: 60px; height: 60px;"
                        onclick="printTerms()" title="Print Terms">
                    <i class="bi bi-printer"></i>
                </button>
            `;
            document.body.appendChild(floatingPrint);
        });
    </script>
</body>
</html>