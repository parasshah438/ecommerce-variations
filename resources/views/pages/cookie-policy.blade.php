<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie Policy - Your Company</title>
    
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
        
        .cookie-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        
        .cookie-card:hover {
            transform: translateY(-5px);
        }
        
        .cookie-type {
            border-left: 5px solid var(--primary-color);
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .cookie-type.essential {
            border-left-color: #28a745;
        }
        
        .cookie-type.analytics {
            border-left-color: #17a2b8;
        }
        
        .cookie-type.marketing {
            border-left-color: #ffc107;
        }
        
        .cookie-type.preference {
            border-left-color: #6f42c1;
        }
        
        .preferences-card {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            border-radius: 15px;
            padding: 2rem;
        }
        
        .toggle-switch {
            position: relative;
            width: 60px;
            height: 30px;
            background: #ccc;
            border-radius: 30px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .toggle-switch.active {
            background: var(--primary-color);
        }
        
        .toggle-switch::after {
            content: '';
            position: absolute;
            width: 26px;
            height: 26px;
            background: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: left 0.3s;
        }
        
        .toggle-switch.active::after {
            left: 32px;
        }
        
        .icon-xl {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .highlight-box {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border-left: 5px solid #ffc107;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 8px;
        }

        .table-of-contents {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
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
                            <li><a class="dropdown-item" href="{{ route('pages.return.refund') }}">Return & Refund</a></li>
                            <li><a class="dropdown-item" href="{{ route('pages.privacy') }}">Privacy Policy</a></li>
                            <li><a class="dropdown-item" href="{{ route('pages.terms') }}">Terms & Conditions</a></li>
                            <li><a class="dropdown-item active" href="{{ route('pages.cookie.policy') }}">Cookie Policy</a></li>
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
                        <i class="bi bi-cookie me-3"></i>Cookie Policy
                    </h1>
                    <p class="lead mb-4">
                        We use cookies to enhance your browsing experience, analyze site traffic, 
                        and personalize content. Learn how we use cookies and manage your preferences.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#cookie-types" class="btn btn-light btn-lg">
                            <i class="bi bi-list-ul me-2"></i>Cookie Types
                        </a>
                        <a href="#manage-cookies" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-gear me-2"></i>Manage Preferences
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <img src="https://via.placeholder.com/500x400/ffffff/007bff?text=Cookie+Settings" 
                             alt="Cookie Settings" class="img-fluid rounded-3 shadow-lg">
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
                            <i class="bi bi-list-ul me-2"></i>Contents
                        </h5>
                        <ul>
                            <li><a href="#what-are-cookies">What Are Cookies?</a></li>
                            <li><a href="#cookie-types">Types of Cookies</a></li>
                            <li><a href="#how-we-use">How We Use Cookies</a></li>
                            <li><a href="#third-party">Third-Party Cookies</a></li>
                            <li><a href="#manage-cookies">Manage Your Preferences</a></li>
                            <li><a href="#browser-settings">Browser Settings</a></li>
                            <li><a href="#updates">Policy Updates</a></li>
                        </ul>
                        
                        <div class="mt-4 p-3 bg-warning rounded">
                            <h6><i class="bi bi-gear me-2"></i>Quick Settings</h6>
                            <p class="small mb-2">Manage your cookie preferences:</p>
                            <a href="#manage-cookies" class="btn btn-sm btn-warning">Cookie Settings</a>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="col-lg-8">
                    <!-- What Are Cookies -->
                    <div id="what-are-cookies" class="cookie-card">
                        <h3><i class="bi bi-question-circle me-2 text-primary"></i>What Are Cookies?</h3>
                        
                        <p>
                            Cookies are small text files that are placed on your device (computer, smartphone, tablet) 
                            when you visit our website. They help us provide you with a better browsing experience 
                            by remembering your preferences and enabling certain functionality.
                        </p>
                        
                        <div class="highlight-box">
                            <h5><i class="bi bi-info-circle text-warning me-2"></i>Important to Know</h5>
                            <p class="mb-0">
                                Cookies do not contain personal information like your name, address, or payment details. 
                                They cannot access other files on your device or install malicious software.
                            </p>
                        </div>
                        
                        <h5>How Cookies Work:</h5>
                        <ul>
                            <li><strong>First Visit:</strong> Our website sends a cookie to your browser</li>
                            <li><strong>Storage:</strong> Your browser stores the cookie on your device</li>
                            <li><strong>Return Visits:</strong> The cookie is sent back to us when you visit again</li>
                            <li><strong>Recognition:</strong> We can recognize you and customize your experience</li>
                        </ul>
                    </div>

                    <!-- Cookie Types -->
                    <div id="cookie-types" class="cookie-card">
                        <h3><i class="bi bi-grid-3x3-gap me-2 text-primary"></i>Types of Cookies We Use</h3>
                        
                        <div class="cookie-type essential">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-shield-fill-check text-success me-3" style="font-size: 2rem;"></i>
                                <div>
                                    <h5 class="mb-1">Essential Cookies</h5>
                                    <span class="badge bg-success">Always Active</span>
                                </div>
                            </div>
                            <p class="mb-3">
                                These cookies are necessary for the website to function properly. They enable core 
                                functionality such as security, network management, and accessibility.
                            </p>
                            <strong>Examples:</strong>
                            <ul class="mb-0">
                                <li>Authentication and login status</li>
                                <li>Shopping cart contents</li>
                                <li>Website security features</li>
                                <li>Load balancing and performance</li>
                            </ul>
                        </div>

                        <div class="cookie-type analytics">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-bar-chart text-info me-3" style="font-size: 2rem;"></i>
                                <div>
                                    <h5 class="mb-1">Analytics Cookies</h5>
                                    <span class="badge bg-info">Optional</span>
                                </div>
                            </div>
                            <p class="mb-3">
                                These cookies help us understand how visitors interact with our website by 
                                collecting and reporting information anonymously.
                            </p>
                            <strong>Examples:</strong>
                            <ul class="mb-0">
                                <li>Page views and navigation patterns</li>
                                <li>Time spent on different pages</li>
                                <li>Popular products and categories</li>
                                <li>Website performance metrics</li>
                            </ul>
                        </div>

                        <div class="cookie-type marketing">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-bullseye text-warning me-3" style="font-size: 2rem;"></i>
                                <div>
                                    <h5 class="mb-1">Marketing Cookies</h5>
                                    <span class="badge bg-warning">Optional</span>
                                </div>
                            </div>
                            <p class="mb-3">
                                These cookies are used to deliver advertisements more relevant to you and your interests. 
                                They also help measure the effectiveness of advertising campaigns.
                            </p>
                            <strong>Examples:</strong>
                            <ul class="mb-0">
                                <li>Personalized product recommendations</li>
                                <li>Retargeting advertisements</li>
                                <li>Social media integration</li>
                                <li>Email marketing optimization</li>
                            </ul>
                        </div>

                        <div class="cookie-type preference">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-person-gear text-purple me-3" style="font-size: 2rem; color: #6f42c1;"></i>
                                <div>
                                    <h5 class="mb-1">Preference Cookies</h5>
                                    <span class="badge" style="background-color: #6f42c1;">Optional</span>
                                </div>
                            </div>
                            <p class="mb-3">
                                These cookies allow the website to remember choices you make and provide enhanced, 
                                more personal features.
                            </p>
                            <strong>Examples:</strong>
                            <ul class="mb-0">
                                <li>Language and region settings</li>
                                <li>Currency preferences</li>
                                <li>Theme and display settings</li>
                                <li>Recently viewed products</li>
                            </ul>
                        </div>
                    </div>

                    <!-- How We Use Cookies -->
                    <div id="how-we-use" class="cookie-card">
                        <h3><i class="bi bi-gear me-2 text-primary"></i>How We Use Cookies</h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Improve Your Experience</h5>
                                <ul>
                                    <li>Remember your shopping cart items</li>
                                    <li>Keep you logged in during your visit</li>
                                    <li>Remember your preferences and settings</li>
                                    <li>Provide personalized content</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Analyze Website Performance</h5>
                                <ul>
                                    <li>Track which pages are most popular</li>
                                    <li>Monitor website loading times</li>
                                    <li>Identify and fix technical issues</li>
                                    <li>Understand user behavior patterns</li>
                                </ul>
                            </div>
                        </div>

                        <h5 class="mt-4">Cookie Duration</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Cookie Type</th>
                                        <th>Duration</th>
                                        <th>Purpose</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Session Cookies</strong></td>
                                        <td>Until browser is closed</td>
                                        <td>Temporary functionality during your visit</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Persistent Cookies</strong></td>
                                        <td>30 days to 2 years</td>
                                        <td>Remember preferences between visits</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Analytics Cookies</strong></td>
                                        <td>Up to 2 years</td>
                                        <td>Long-term website usage analysis</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Marketing Cookies</strong></td>
                                        <td>30-90 days</td>
                                        <td>Advertising campaign effectiveness</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Third Party Cookies -->
                    <div id="third-party" class="cookie-card">
                        <h3><i class="bi bi-link-45deg me-2 text-primary"></i>Third-Party Cookies</h3>
                        
                        <p>
                            We work with trusted third-party services that may set their own cookies. 
                            These help us provide better service and functionality.
                        </p>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Service</th>
                                        <th>Purpose</th>
                                        <th>Cookie Duration</th>
                                        <th>Privacy Policy</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Google Analytics</strong></td>
                                        <td>Website traffic analysis</td>
                                        <td>2 years</td>
                                        <td><a href="https://policies.google.com/privacy" target="_blank">View Policy</a></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Facebook Pixel</strong></td>
                                        <td>Advertising and remarketing</td>
                                        <td>90 days</td>
                                        <td><a href="https://www.facebook.com/privacy" target="_blank">View Policy</a></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Google Ads</strong></td>
                                        <td>Advertising optimization</td>
                                        <td>90 days</td>
                                        <td><a href="https://policies.google.com/privacy" target="_blank">View Policy</a></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Hotjar</strong></td>
                                        <td>User experience analysis</td>
                                        <td>1 year</td>
                                        <td><a href="https://www.hotjar.com/legal/policies/privacy/" target="_blank">View Policy</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> Third-party cookies are governed by their respective privacy policies. 
                            We recommend reviewing these policies for more information.
                        </div>
                    </div>

                    <!-- Manage Cookies -->
                    <div id="manage-cookies" class="cookie-card">
                        <h3><i class="bi bi-sliders me-2 text-primary"></i>Manage Your Cookie Preferences</h3>
                        
                        <p>
                            You have control over which cookies are set on your device. Use the toggles below 
                            to customize your cookie preferences.
                        </p>

                        <div class="preferences-card">
                            <h5><i class="bi bi-gear me-2"></i>Cookie Preferences</h5>
                            
                            <div class="row align-items-center mb-4 pb-3 border-bottom">
                                <div class="col-md-9">
                                    <h6>Essential Cookies</h6>
                                    <p class="text-muted mb-0">Required for basic website functionality. Cannot be disabled.</p>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="toggle-switch active" data-required="true">
                                        <span class="visually-hidden">Essential cookies always enabled</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center mb-4 pb-3 border-bottom">
                                <div class="col-md-9">
                                    <h6>Analytics Cookies</h6>
                                    <p class="text-muted mb-0">Help us understand how you use our website to improve your experience.</p>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="toggle-switch" data-cookie-type="analytics">
                                        <span class="visually-hidden">Toggle analytics cookies</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center mb-4 pb-3 border-bottom">
                                <div class="col-md-9">
                                    <h6>Marketing Cookies</h6>
                                    <p class="text-muted mb-0">Used to show you relevant advertisements and measure their effectiveness.</p>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="toggle-switch" data-cookie-type="marketing">
                                        <span class="visually-hidden">Toggle marketing cookies</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center mb-4">
                                <div class="col-md-9">
                                    <h6>Preference Cookies</h6>
                                    <p class="text-muted mb-0">Remember your settings and provide personalized features.</p>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="toggle-switch active" data-cookie-type="preferences">
                                        <span class="visually-hidden">Toggle preference cookies</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button class="btn btn-outline-secondary" onclick="acceptOnlyEssential()">Accept Only Essential</button>
                                <button class="btn btn-success" onclick="acceptAllCookies()">Accept All Cookies</button>
                                <button class="btn btn-primary" onclick="savePreferences()">Save Preferences</button>
                            </div>
                        </div>
                    </div>

                    <!-- Browser Settings -->
                    <div id="browser-settings" class="cookie-card">
                        <h3><i class="bi bi-browser-chrome me-2 text-primary"></i>Browser Cookie Settings</h3>
                        
                        <p>
                            You can also manage cookies through your browser settings. Here's how to do it 
                            in popular browsers:
                        </p>

                        <div class="accordion" id="browserAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#chrome">
                                        <i class="bi bi-browser-chrome me-2 text-warning"></i>Google Chrome
                                    </button>
                                </h2>
                                <div id="chrome" class="accordion-collapse collapse" data-bs-parent="#browserAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Click the three-dot menu in the top-right corner</li>
                                            <li>Select "Settings" from the dropdown</li>
                                            <li>Click "Privacy and security" in the left sidebar</li>
                                            <li>Select "Cookies and other site data"</li>
                                            <li>Choose your preferred cookie settings</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#firefox">
                                        <i class="bi bi-browser-firefox me-2 text-danger"></i>Mozilla Firefox
                                    </button>
                                </h2>
                                <div id="firefox" class="accordion-collapse collapse" data-bs-parent="#browserAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Click the menu button (three lines) in the top-right</li>
                                            <li>Select "Settings" from the menu</li>
                                            <li>Click on "Privacy & Security" in the left panel</li>
                                            <li>In the "Cookies and Site Data" section, click "Manage Data"</li>
                                            <li>Adjust your cookie preferences as needed</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#safari">
                                        <i class="bi bi-browser-safari me-2 text-info"></i>Safari
                                    </button>
                                </h2>
                                <div id="safari" class="accordion-collapse collapse" data-bs-parent="#browserAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Open Safari and click "Safari" in the menu bar</li>
                                            <li>Select "Preferences" from the dropdown</li>
                                            <li>Click on the "Privacy" tab</li>
                                            <li>Choose your cookie blocking preferences</li>
                                            <li>Click "Manage Website Data" to remove specific cookies</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#edge">
                                        <i class="bi bi-browser-edge me-2 text-primary"></i>Microsoft Edge
                                    </button>
                                </h2>
                                <div id="edge" class="accordion-collapse collapse" data-bs-parent="#browserAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Click the three-dot menu in the top-right corner</li>
                                            <li>Select "Settings" from the menu</li>
                                            <li>Click on "Cookies and site permissions" in the left panel</li>
                                            <li>Select "Cookies and site data"</li>
                                            <li>Adjust your cookie settings as desired</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> Disabling certain cookies may affect website functionality 
                            and your user experience.
                        </div>
                    </div>

                    <!-- Policy Updates -->
                    <div id="updates" class="cookie-card">
                        <h3><i class="bi bi-arrow-clockwise me-2 text-primary"></i>Policy Updates</h3>
                        
                        <p>
                            We may update this Cookie Policy from time to time to reflect changes in 
                            technology, legislation, or our business practices.
                        </p>

                        <h5>How We Notify You of Changes:</h5>
                        <ul>
                            <li>Updated "Last Modified" date at the top of this policy</li>
                            <li>Prominent notice on our website for significant changes</li>
                            <li>Email notification to registered users (for major updates)</li>
                            <li>Cookie banner updates with new consent options</li>
                        </ul>

                        <div class="highlight-box">
                            <h5><i class="bi bi-calendar-check text-warning me-2"></i>Stay Informed</h5>
                            <p class="mb-0">
                                We recommend checking this policy periodically for any updates. 
                                Continued use of our website after changes indicates acceptance of the updated policy.
                            </p>
                        </div>

                        <div class="text-center mt-4">
                            <p class="text-muted">
                                <strong>Last Updated:</strong> {{ date('F d, Y') }}<br>
                                <strong>Effective Date:</strong> {{ date('F d, Y') }}
                            </p>
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
                        We respect your privacy and give you control over your data. 
                        Manage your cookie preferences anytime to customize your experience.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Privacy</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.cookie.policy') }}" class="text-muted text-decoration-none">Cookie Policy</a></li>
                        <li><a href="{{ route('pages.privacy') }}" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li><a href="#manage-cookies" class="text-muted text-decoration-none">Cookie Settings</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Data Protection</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.help') }}" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="{{ route('pages.faq') }}" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Contact Us</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Privacy Rights</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3">Cookie Preferences</h6>
                    <div class="text-muted">
                        <p>Update your cookie settings anytime:</p>
                        <button class="btn btn-outline-light btn-sm" onclick="openCookieSettings()">
                            <i class="bi bi-cookie me-2"></i>Cookie Settings
                        </button>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center text-muted">
                <p>&copy; {{ date('Y') }} Your Company. All rights reserved. | Your Privacy Matters</p>
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

        // Toggle switch functionality
        document.querySelectorAll('.toggle-switch').forEach(toggle => {
            if (toggle.dataset.required !== 'true') {
                toggle.addEventListener('click', function() {
                    this.classList.toggle('active');
                });
            }
        });

        // Cookie preference functions
        function acceptOnlyEssential() {
            document.querySelectorAll('.toggle-switch').forEach(toggle => {
                if (toggle.dataset.required !== 'true') {
                    toggle.classList.remove('active');
                }
            });
            savePreferences();
        }

        function acceptAllCookies() {
            document.querySelectorAll('.toggle-switch').forEach(toggle => {
                toggle.classList.add('active');
            });
            savePreferences();
        }

        function savePreferences() {
            const preferences = {};
            document.querySelectorAll('.toggle-switch[data-cookie-type]').forEach(toggle => {
                const type = toggle.dataset.cookieType;
                preferences[type] = toggle.classList.contains('active');
            });
            
            // Save to localStorage (in real implementation, send to server)
            localStorage.setItem('cookiePreferences', JSON.stringify(preferences));
            
            // Show confirmation
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alert.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
            alert.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>
                <strong>Settings Saved!</strong> Your cookie preferences have been updated.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 3000);
        }

        function openCookieSettings() {
            const target = document.querySelector('#manage-cookies');
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        // Load saved preferences on page load
        document.addEventListener('DOMContentLoaded', function() {
            const saved = localStorage.getItem('cookiePreferences');
            if (saved) {
                const preferences = JSON.parse(saved);
                Object.keys(preferences).forEach(type => {
                    const toggle = document.querySelector(`[data-cookie-type="${type}"]`);
                    if (toggle) {
                        if (preferences[type]) {
                            toggle.classList.add('active');
                        } else {
                            toggle.classList.remove('active');
                        }
                    }
                });
            }

            // Highlight active section in table of contents
            const observerOptions = {
                rootMargin: '-150px 0px -66%',
                threshold: 0
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const id = entry.target.getAttribute('id');
                    const link = document.querySelector(`.table-of-contents a[href="#${id}"]`);
                    if (entry.isIntersecting) {
                        document.querySelectorAll('.table-of-contents a').forEach(l => l.classList.remove('active'));
                        if (link) link.classList.add('active');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('[id]').forEach(section => {
                observer.observe(section);
            });
        });
    </script>
</body>
</html>