<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Your Company</title>
    
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
        
        .table-of-contents ul li a:hover {
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
        
        .highlight-box {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            border-left: 5px solid var(--primary-color);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 8px;
        }
        
        .data-types {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
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
                        <a class="nav-link active" href="{{ route('pages.privacy') }}">Privacy Policy</a>
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
                        <i class="bi bi-shield-check me-3"></i>Privacy Policy
                    </h1>
                    <p class="lead mb-4">
                        We are committed to protecting your privacy and ensuring the security of your personal information. 
                        This policy explains how we collect, use, and safeguard your data.
                    </p>
                    <div class="last-updated text-start">
                        <i class="bi bi-calendar-check text-success me-2"></i>
                        <strong>Last Updated:</strong> {{ date('F d, Y') }}
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
                            <li><a href="#overview">1. Overview</a></li>
                            <li><a href="#information-collection">2. Information We Collect</a></li>
                            <li><a href="#information-use">3. How We Use Information</a></li>
                            <li><a href="#information-sharing">4. Information Sharing</a></li>
                            <li><a href="#cookies">5. Cookies & Tracking</a></li>
                            <li><a href="#data-security">6. Data Security</a></li>
                            <li><a href="#user-rights">7. Your Rights</a></li>
                            <li><a href="#third-party">8. Third-Party Services</a></li>
                            <li><a href="#children">9. Children's Privacy</a></li>
                            <li><a href="#international">10. International Transfers</a></li>
                            <li><a href="#changes">11. Policy Changes</a></li>
                            <li><a href="#contact">12. Contact Information</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Content -->
                <div class="col-lg-8">
                    <!-- Overview -->
                    <div id="overview" class="content-section">
                        <h3><i class="bi bi-info-circle me-2"></i>1. Overview</h3>
                        <p>
                            Welcome to Your Company ("we," "our," or "us"). This Privacy Policy explains how we collect, 
                            use, disclose, and safeguard your information when you visit our website and make purchases 
                            from our online store.
                        </p>
                        <div class="highlight-box">
                            <h5><i class="bi bi-shield-fill-check text-success me-2"></i>Our Commitment</h5>
                            <p class="mb-0">
                                We are committed to transparency and will never sell, rent, or share your personal 
                                information with third parties for marketing purposes without your explicit consent.
                            </p>
                        </div>
                        <p>
                            Please read this privacy policy carefully. If you do not agree with the terms of this 
                            privacy policy, please do not access the site or make any purchases.
                        </p>
                    </div>

                    <!-- Information Collection -->
                    <div id="information-collection" class="content-section">
                        <h3><i class="bi bi-collection me-2"></i>2. Information We Collect</h3>
                        
                        <h5>Personal Information</h5>
                        <div class="data-types">
                            <p><strong>We may collect the following types of personal information:</strong></p>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul>
                                        <li><strong>Identity Data:</strong> Name, username, title</li>
                                        <li><strong>Contact Data:</strong> Email, phone number</li>
                                        <li><strong>Address Data:</strong> Billing/delivery addresses</li>
                                        <li><strong>Financial Data:</strong> Payment card details</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul>
                                        <li><strong>Transaction Data:</strong> Purchase history, preferences</li>
                                        <li><strong>Technical Data:</strong> IP address, browser type</li>
                                        <li><strong>Usage Data:</strong> How you use our website</li>
                                        <li><strong>Marketing Data:</strong> Communication preferences</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4">Automatic Information Collection</h5>
                        <p>
                            We automatically collect certain information when you visit our website, including:
                        </p>
                        <ul>
                            <li>Log information (IP address, browser type, referring URLs)</li>
                            <li>Device information (device type, operating system)</li>
                            <li>Cookies and similar technologies</li>
                            <li>Web beacons and pixel tags</li>
                        </ul>
                    </div>

                    <!-- Information Use -->
                    <div id="information-use" class="content-section">
                        <h3><i class="bi bi-gear me-2"></i>3. How We Use Your Information</h3>
                        
                        <p>We use the information we collect for various purposes, including:</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Order Processing</h5>
                                <ul>
                                    <li>Process and fulfill your orders</li>
                                    <li>Send order confirmations and updates</li>
                                    <li>Handle returns and exchanges</li>
                                    <li>Provide customer support</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Account Management</h5>
                                <ul>
                                    <li>Create and manage your account</li>
                                    <li>Personalize your experience</li>
                                    <li>Remember your preferences</li>
                                    <li>Provide recommendations</li>
                                </ul>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h5>Communication</h5>
                                <ul>
                                    <li>Send promotional emails (with consent)</li>
                                    <li>Respond to inquiries</li>
                                    <li>Provide important updates</li>
                                    <li>Send security alerts</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Website Improvement</h5>
                                <ul>
                                    <li>Analyze website usage</li>
                                    <li>Improve our services</li>
                                    <li>Prevent fraud and abuse</li>
                                    <li>Ensure website security</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Information Sharing -->
                    <div id="information-sharing" class="content-section">
                        <h3><i class="bi bi-share me-2"></i>4. Information Sharing</h3>
                        
                        <div class="highlight-box">
                            <h5><i class="bi bi-exclamation-triangle text-warning me-2"></i>Important</h5>
                            <p class="mb-0">
                                We do not sell, trade, or otherwise transfer your personal information to third parties 
                                for marketing purposes without your consent.
                            </p>
                        </div>

                        <p>We may share your information in the following circumstances:</p>

                        <h5>Service Providers</h5>
                        <p>
                            We may share information with trusted third-party service providers who assist us in 
                            operating our website and conducting our business, including:
                        </p>
                        <ul>
                            <li>Payment processors (Stripe, PayPal, Razorpay)</li>
                            <li>Shipping companies (FedEx, UPS, local couriers)</li>
                            <li>Email service providers (Mailchimp, SendGrid)</li>
                            <li>Analytics providers (Google Analytics)</li>
                            <li>Customer service platforms</li>
                        </ul>

                        <h5>Legal Requirements</h5>
                        <p>We may disclose your information if required by law or in good faith belief that such disclosure is necessary to:</p>
                        <ul>
                            <li>Comply with legal obligations</li>
                            <li>Protect our rights and property</li>
                            <li>Prevent fraud or illegal activities</li>
                            <li>Protect the safety of our users</li>
                        </ul>
                    </div>

                    <!-- Cookies -->
                    <div id="cookies" class="content-section">
                        <h3><i class="bi bi-cookie me-2"></i>5. Cookies & Tracking Technologies</h3>
                        
                        <p>
                            We use cookies and similar tracking technologies to enhance your browsing experience, 
                            analyze site traffic, and personalize content.
                        </p>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Cookie Type</th>
                                        <th>Purpose</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Essential Cookies</strong></td>
                                        <td>Required for basic website functionality</td>
                                        <td>Session/1 year</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Analytics Cookies</strong></td>
                                        <td>Help us understand website usage</td>
                                        <td>2 years</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Marketing Cookies</strong></td>
                                        <td>Used for personalized advertising</td>
                                        <td>1 year</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Preference Cookies</strong></td>
                                        <td>Remember your settings and preferences</td>
                                        <td>1 year</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Cookie Control:</strong> You can control cookies through your browser settings. 
                            However, disabling certain cookies may affect website functionality.
                        </div>
                    </div>

                    <!-- Data Security -->
                    <div id="data-security" class="content-section">
                        <h3><i class="bi bi-shield-lock me-2"></i>6. Data Security</h3>
                        
                        <p>
                            We implement appropriate security measures to protect your personal information against 
                            unauthorized access, alteration, disclosure, or destruction.
                        </p>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>Technical Safeguards</h5>
                                <ul>
                                    <li>SSL/TLS encryption</li>
                                    <li>Secure data centers</li>
                                    <li>Regular security audits</li>
                                    <li>Firewalls and intrusion detection</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Administrative Safeguards</h5>
                                <ul>
                                    <li>Limited employee access</li>
                                    <li>Background checks</li>
                                    <li>Security training programs</li>
                                    <li>Data breach response plan</li>
                                </ul>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> While we strive to protect your information, no method of 
                            transmission over the Internet is 100% secure. Please use caution when sharing sensitive information.
                        </div>
                    </div>

                    <!-- User Rights -->
                    <div id="user-rights" class="content-section">
                        <h3><i class="bi bi-person-check me-2"></i>7. Your Privacy Rights</h3>
                        
                        <p>You have several rights regarding your personal information:</p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <strong>Access Rights</strong>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <li><i class="bi bi-check text-success me-2"></i>View your personal data</li>
                                            <li><i class="bi bi-check text-success me-2"></i>Download your data</li>
                                            <li><i class="bi bi-check text-success me-2"></i>Update inaccurate information</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <strong>Control Rights</strong>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <li><i class="bi bi-check text-success me-2"></i>Opt-out of marketing emails</li>
                                            <li><i class="bi bi-check text-success me-2"></i>Delete your account</li>
                                            <li><i class="bi bi-check text-success me-2"></i>Restrict data processing</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h5>How to Exercise Your Rights</h5>
                            <p>To exercise any of these rights, please:</p>
                            <ul>
                                <li>Log into your account settings</li>
                                <li>Contact our privacy team at privacy@yourcompany.com</li>
                                <li>Use our data request form</li>
                                <li>Call our customer service at +1 (555) 123-4567</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Third Party Services -->
                    <div id="third-party" class="content-section">
                        <h3><i class="bi bi-link me-2"></i>8. Third-Party Services</h3>
                        
                        <p>Our website may contain links to third-party websites or integrate with third-party services:</p>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Purpose</th>
                                        <th>Privacy Policy</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Google Analytics</td>
                                        <td>Website analytics</td>
                                        <td><a href="https://policies.google.com/privacy" target="_blank">View Policy</a></td>
                                    </tr>
                                    <tr>
                                        <td>Facebook Pixel</td>
                                        <td>Marketing and advertising</td>
                                        <td><a href="https://www.facebook.com/policy.php" target="_blank">View Policy</a></td>
                                    </tr>
                                    <tr>
                                        <td>PayPal</td>
                                        <td>Payment processing</td>
                                        <td><a href="https://www.paypal.com/privacy" target="_blank">View Policy</a></td>
                                    </tr>
                                    <tr>
                                        <td>Mailchimp</td>
                                        <td>Email marketing</td>
                                        <td><a href="https://mailchimp.com/legal/privacy/" target="_blank">View Policy</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            We are not responsible for the privacy practices of third-party websites. 
                            Please review their privacy policies before providing any information.
                        </div>
                    </div>

                    <!-- Children's Privacy -->
                    <div id="children" class="content-section">
                        <h3><i class="bi bi-person-heart me-2"></i>9. Children's Privacy</h3>
                        
                        <div class="highlight-box">
                            <h5><i class="bi bi-shield-fill-exclamation text-danger me-2"></i>Age Restriction</h5>
                            <p class="mb-0">
                                Our services are not intended for children under 13 years of age. We do not knowingly 
                                collect personal information from children under 13.
                            </p>
                        </div>

                        <p>If we learn that we have collected personal information from a child under 13:</p>
                        <ul>
                            <li>We will delete the information immediately</li>
                            <li>We will not use the information for any purpose</li>
                            <li>We will not share the information with third parties</li>
                            <li>Parents will be notified if applicable</li>
                        </ul>

                        <p>
                            If you believe we have collected information from a child under 13, 
                            please contact us immediately at privacy@yourcompany.com.
                        </p>
                    </div>

                    <!-- International Transfers -->
                    <div id="international" class="content-section">
                        <h3><i class="bi bi-globe me-2"></i>10. International Data Transfers</h3>
                        
                        <p>
                            Your information may be transferred to and maintained on computers located outside of your 
                            state, province, country, or other governmental jurisdiction where data protection laws 
                            may differ.
                        </p>

                        <h5>Safeguards for International Transfers</h5>
                        <ul>
                            <li>Standard contractual clauses approved by regulatory authorities</li>
                            <li>Adequacy decisions by relevant data protection authorities</li>
                            <li>Privacy Shield certification (where applicable)</li>
                            <li>Binding corporate rules</li>
                        </ul>

                        <div class="alert alert-primary">
                            <i class="bi bi-info-circle me-2"></i>
                            By using our services, you consent to the transfer of your information to countries 
                            that may have different data protection standards.
                        </div>
                    </div>

                    <!-- Policy Changes -->
                    <div id="changes" class="content-section">
                        <h3><i class="bi bi-arrow-clockwise me-2"></i>11. Changes to This Privacy Policy</h3>
                        
                        <p>
                            We may update this Privacy Policy from time to time to reflect changes in our practices 
                            or for other operational, legal, or regulatory reasons.
                        </p>

                        <h5>How We Notify You</h5>
                        <ul>
                            <li>Email notification to registered users</li>
                            <li>Prominent notice on our website</li>
                            <li>In-app notifications (if applicable)</li>
                            <li>Updated "Last Modified" date</li>
                        </ul>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Continued use of our services after changes constitutes acceptance of the updated policy.
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div id="contact" class="content-section">
                        <h3><i class="bi bi-envelope me-2"></i>12. Contact Us</h3>
                        
                        <p>
                            If you have any questions about this Privacy Policy or our data practices, 
                            please contact us:
                        </p>

                        <div class="contact-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5><i class="bi bi-person-badge me-2"></i>Privacy Officer</h5>
                                    <p class="mb-0">John Smith</p>
                                    <p class="mb-0">Chief Privacy Officer</p>
                                    <p class="mb-3">Your Company, Inc.</p>
                                    
                                    <h6><i class="bi bi-geo-alt me-2"></i>Address:</h6>
                                    <p class="mb-0">123 Business Street</p>
                                    <p class="mb-0">Suite 456</p>
                                    <p>City, State 12345</p>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="bi bi-telephone me-2"></i>Phone:</h6>
                                    <p>+1 (555) 123-4567</p>
                                    
                                    <h6><i class="bi bi-envelope me-2"></i>Email:</h6>
                                    <p>privacy@yourcompany.com</p>
                                    
                                    <h6><i class="bi bi-clock me-2"></i>Response Time:</h6>
                                    <p>We aim to respond within 30 days</p>
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
                        Committed to protecting your privacy and providing transparent information 
                        about how we handle your personal data.
                    </p>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Legal</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.privacy') }}" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li><a href="{{ route('pages.terms') }}" class="text-muted text-decoration-none">Terms & Conditions</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Cookie Policy</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">GDPR Compliance</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.help') }}" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="{{ route('pages.faq') }}" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3">Privacy Rights</h6>
                    <div class="text-muted">
                        <p>Exercise your privacy rights:</p>
                        <a href="mailto:privacy@yourcompany.com" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-envelope me-2"></i>Contact Privacy Team
                        </a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center text-muted">
                <p>&copy; {{ date('Y') }} Your Company. All rights reserved. | 
                   <a href="{{ route('pages.privacy') }}" class="text-decoration-none">Privacy Policy</a> | 
                   <a href="{{ route('pages.terms') }}" class="text-decoration-none">Terms & Conditions</a>
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
    </script>
</body>
</html>