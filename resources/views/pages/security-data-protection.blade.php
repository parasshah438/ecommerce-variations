<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Learn about our comprehensive security measures, data protection policies, and how we safeguard your personal information and ensure secure online shopping.">
    <meta name="keywords" content="security, data protection, encryption, privacy, secure shopping, cybersecurity, GDPR, compliance">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Security & Data Protection">
    <meta property="og:description" content="Your security and data privacy are our top priorities. Learn about our comprehensive security measures and data protection practices.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <title>Security & Data Protection - {{ config('app.name') }}</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --info-color: #0dcaf0;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-security: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
        }
        
        .hero-section {
            background: var(--gradient-security);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .security-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .feature-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .feature-card:hover::before {
            left: 100%;
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover .feature-icon {
            color: var(--success-color);
            transform: scale(1.1);
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 800;
            margin-bottom: 3rem;
            position: relative;
            font-size: 2.5rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--info-color));
            border-radius: 2px;
        }
        
        .text-left .section-title::after {
            left: 0;
            transform: none;
        }
        
        .certification-badge {
            background: var(--success-color);
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: 30px;
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-block;
            margin: 0.5rem;
            box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
            transition: all 0.3s ease;
        }
        
        .certification-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 135, 84, 0.4);
        }
        
        .security-stat {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .security-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary-color);
            line-height: 1;
        }
        
        .stat-label {
            font-size: 1rem;
            color: var(--secondary-color);
            margin-top: 0.5rem;
        }
        
        .timeline-item {
            border-left: 3px solid var(--primary-color);
            padding-left: 2rem;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 0;
            width: 13px;
            height: 13px;
            background: var(--primary-color);
            border-radius: 50%;
        }
        
        .alert-security {
            background: linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(13, 110, 253, 0.1));
            border: 2px solid var(--success-color);
            border-radius: 15px;
            padding: 2rem;
        }
        
        .contact-security {
            background: var(--gradient-primary);
            color: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .contact-security::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-30px, -30px) rotate(360deg); }
        }
        
        .btn-security {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }
        
        .btn-security:hover {
            background: linear-gradient(135deg, #157347, #198754);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(25, 135, 84, 0.4);
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
            }
            
            .security-icon {
                font-size: 3.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .stat-number {
                font-size: 2.5rem;
            }
        }
        
        @media (prefers-reduced-motion: reduce) {
            .security-icon,
            .feature-card,
            .certification-badge,
            .security-stat {
                animation: none;
                transition: none;
            }
            
            .feature-card:hover,
            .certification-badge:hover,
            .security-stat:hover {
                transform: none;
            }
        }
    </style>
</head>
<body>
    <!-- Skip to main content link -->
    <a href="#main-content" class="visually-hidden-focusable btn btn-primary position-absolute top-0 start-0 m-2" style="z-index: 1000;">Skip to main content</a>
    
    <!-- Hero Section -->
    <header class="hero-section" role="banner">
        <div class="container position-relative">
            <div class="row justify-content-center text-center">
                <div class="col-lg-10">
                    <div class="security-icon" role="img" aria-label="Security shield icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h1 class="display-3 fw-bold mb-4">Security & Data Protection</h1>
                    <p class="lead mb-5 fs-4">
                        Your security and privacy are our highest priorities. We implement industry-leading security 
                        measures and follow strict data protection protocols to keep your information safe.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                        <span class="certification-badge">
                            <i class="fas fa-certificate me-2"></i>SSL Encrypted
                        </span>
                        <span class="certification-badge">
                            <i class="fas fa-shield-check me-2"></i>GDPR Compliant
                        </span>
                        <span class="certification-badge">
                            <i class="fas fa-lock me-2"></i>PCI DSS Certified
                        </span>
                        <span class="certification-badge">
                            <i class="fas fa-user-shield me-2"></i>ISO 27001
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main id="main-content" role="main">
        <!-- Security Stats Section -->
        <section class="py-5 bg-light" aria-labelledby="stats-heading">
            <div class="container">
                <h2 id="stats-heading" class="section-title text-center">Our Security at a Glance</h2>
                
                <div class="row g-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="security-stat">
                            <div class="stat-number">99.9%</div>
                            <div class="stat-label">Uptime Security</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="security-stat">
                            <div class="stat-number">256-bit</div>
                            <div class="stat-label">SSL Encryption</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="security-stat">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Security Monitoring</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="security-stat">
                            <div class="stat-number">0</div>
                            <div class="stat-label">Data Breaches</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Security Features Section -->
        <section class="py-5" aria-labelledby="features-heading">
            <div class="container">
                <h2 id="features-heading" class="section-title text-center">Comprehensive Security Features</h2>
                
                <div class="row g-4">
                    <!-- Data Encryption -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card feature-card">
                            <div class="card-body text-center p-4">
                                <div class="feature-icon">
                                    <i class="fas fa-lock" aria-hidden="true"></i>
                                </div>
                                <h3 class="h4 card-title mb-3">End-to-End Encryption</h3>
                                <p class="card-text">
                                    All data transmission is protected with 256-bit SSL encryption. Your personal 
                                    information, payment details, and communications are encrypted both in transit 
                                    and at rest using military-grade security protocols.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Secure Payments -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card feature-card">
                            <div class="card-body text-center p-4">
                                <div class="feature-icon">
                                    <i class="fas fa-credit-card" aria-hidden="true"></i>
                                </div>
                                <h3 class="h4 card-title mb-3">PCI DSS Compliance</h3>
                                <p class="card-text">
                                    Our payment processing meets the highest PCI DSS standards. Credit card 
                                    information is tokenized and never stored on our servers. We work with 
                                    certified payment processors to ensure secure transactions.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Fraud Protection -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card feature-card">
                            <div class="card-body text-center p-4">
                                <div class="feature-icon">
                                    <i class="fas fa-user-shield" aria-hidden="true"></i>
                                </div>
                                <h3 class="h4 card-title mb-3">Fraud Detection</h3>
                                <p class="card-text">
                                    Advanced AI-powered fraud detection systems monitor transactions in real-time. 
                                    Suspicious activities are flagged immediately, and multi-factor authentication 
                                    provides additional account protection.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Server Security -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card feature-card">
                            <div class="card-body text-center p-4">
                                <div class="feature-icon">
                                    <i class="fas fa-server" aria-hidden="true"></i>
                                </div>
                                <h3 class="h4 card-title mb-3">Secure Infrastructure</h3>
                                <p class="card-text">
                                    Our servers are hosted in SOC 2 compliant data centers with physical security, 
                                    biometric access controls, and 24/7 monitoring. Regular security audits and 
                                    penetration testing ensure robust protection.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Privacy Protection -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card feature-card">
                            <div class="card-body text-center p-4">
                                <div class="feature-icon">
                                    <i class="fas fa-eye-slash" aria-hidden="true"></i>
                                </div>
                                <h3 class="h4 card-title mb-3">Privacy by Design</h3>
                                <p class="card-text">
                                    We collect only necessary data and implement privacy controls at every level. 
                                    GDPR compliance ensures your data rights are respected, with easy access to 
                                    modify, export, or delete your personal information.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Backup & Recovery -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card feature-card">
                            <div class="card-body text-center p-4">
                                <div class="feature-icon">
                                    <i class="fas fa-database" aria-hidden="true"></i>
                                </div>
                                <h3 class="h4 card-title mb-3">Data Backup & Recovery</h3>
                                <p class="card-text">
                                    Automated daily backups with geographic redundancy ensure your data is always 
                                    safe. Our disaster recovery protocols guarantee business continuity with 
                                    minimal downtime in any scenario.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Security Timeline -->
        <section class="py-5 bg-light" aria-labelledby="timeline-heading">
            <div class="container">
                <h2 id="timeline-heading" class="section-title text-center">Our Security Journey</h2>
                
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="timeline-item">
                            <h3 class="h5 text-primary mb-2">2024 - Advanced AI Security</h3>
                            <p class="mb-0">Implemented machine learning algorithms for real-time threat detection and automated response systems.</p>
                        </div>
                        
                        <div class="timeline-item">
                            <h3 class="h5 text-primary mb-2">2023 - Zero Trust Architecture</h3>
                            <p class="mb-0">Adopted zero trust security model with multi-factor authentication and continuous verification protocols.</p>
                        </div>
                        
                        <div class="timeline-item">
                            <h3 class="h5 text-primary mb-2">2022 - ISO 27001 Certification</h3>
                            <p class="mb-0">Achieved ISO 27001 certification for information security management systems and best practices.</p>
                        </div>
                        
                        <div class="timeline-item">
                            <h3 class="h5 text-primary mb-2">2021 - GDPR Full Compliance</h3>
                            <p class="mb-0">Implemented comprehensive GDPR compliance measures with enhanced data protection controls.</p>
                        </div>
                        
                        <div class="timeline-item">
                            <h3 class="h5 text-primary mb-2">2020 - Enhanced Encryption</h3>
                            <p class="mb-0">Upgraded to 256-bit SSL encryption and implemented end-to-end encryption for all data transmission.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Data Protection Policies -->
        <section class="py-5" aria-labelledby="policies-heading">
            <div class="container">
                <h2 id="policies-heading" class="section-title text-center">Data Protection Policies</h2>
                
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="alert-security">
                            <h3 class="h4 text-success mb-3">
                                <i class="fas fa-clipboard-check me-2"></i>
                                What We Collect
                            </h3>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Personal identification information (name, email, phone)</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Billing and shipping addresses</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Order history and preferences</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Website usage analytics (anonymized)</li>
                                <li class="mb-0"><i class="fas fa-check text-success me-2"></i> Communication preferences</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="alert-security">
                            <h3 class="h4 text-primary mb-3">
                                <i class="fas fa-shield-alt me-2"></i>
                                How We Protect It
                            </h3>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-lock text-primary me-2"></i> 256-bit SSL encryption for all data transmission</li>
                                <li class="mb-2"><i class="fas fa-database text-primary me-2"></i> Encrypted storage with access controls</li>
                                <li class="mb-2"><i class="fas fa-user-check text-primary me-2"></i> Multi-factor authentication required</li>
                                <li class="mb-2"><i class="fas fa-clock text-primary me-2"></i> Regular security audits and updates</li>
                                <li class="mb-0"><i class="fas fa-trash text-primary me-2"></i> Automatic data purging after retention period</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-lg-8 mx-auto">
                        <div class="text-center">
                            <h3 class="h4 mb-3">Your Data Rights</h3>
                            <p class="mb-4">
                                Under GDPR and other privacy regulations, you have the right to access, modify, 
                                or delete your personal data at any time. We provide easy-to-use tools for 
                                managing your privacy preferences and data.
                            </p>
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <button class="btn btn-outline-primary" type="button">
                                    <i class="fas fa-download me-2"></i>Export My Data
                                </button>
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-cog me-2"></i>Privacy Settings
                                </button>
                                <button class="btn btn-outline-danger" type="button">
                                    <i class="fas fa-trash me-2"></i>Delete Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Security Contact Section -->
        <section class="py-5" aria-labelledby="contact-heading">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="contact-security">
                            <h2 id="contact-heading" class="h3 mb-4">Security Concerns or Questions?</h2>
                            <p class="lead mb-4">
                                Our dedicated security team is available 24/7 to address any concerns or questions 
                                about your data security and privacy. Don't hesitate to reach out if you notice 
                                anything suspicious or need clarification about our security practices.
                            </p>
                            
                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-envelope fa-2x mb-2"></i>
                                        <strong>Email Security Team</strong>
                                        <a href="mailto:security@example.com" class="text-white">security@example.com</a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-phone fa-2x mb-2"></i>
                                        <strong>Security Hotline</strong>
                                        <a href="tel:+1234567890" class="text-white">(123) 456-7890</a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-bug fa-2x mb-2"></i>
                                        <strong>Report Vulnerability</strong>
                                        <a href="mailto:security@example.com" class="text-white">Bug Bounty Program</a>
                                    </div>
                                </div>
                            </div>
                            
                            <button class="btn btn-security btn-lg" type="button">
                                <i class="fas fa-shield-check me-2"></i>
                                Contact Security Team
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5" role="contentinfo">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="mb-3">Security Resources</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light text-decoration-none">Security Best Practices</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Two-Factor Authentication Guide</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Password Security Tips</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Phishing Protection</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="mb-3">Compliance</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.privacy') }}" class="text-light text-decoration-none">Privacy Policy</a></li>
                        <li><a href="{{ route('pages.terms') }}" class="text-light text-decoration-none">Terms of Service</a></li>
                        <li><a href="{{ route('pages.cookie.policy') }}" class="text-light text-decoration-none">Cookie Policy</a></li>
                        <li><a href="#" class="text-light text-decoration-none">GDPR Compliance</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('welcome') }}" class="text-light text-decoration-none">Home</a></li>
                        <li><a href="{{ route('pages.help') }}" class="text-light text-decoration-none">Help Center</a></li>
                        <li><a href="{{ route('pages.accessibility') }}" class="text-light text-decoration-none">Accessibility</a></li>
                        <li><a href="{{ route('pages.sitemap') }}" class="text-light text-decoration-none">Site Map</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 {{ config('app.name') }}. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex justify-content-md-end gap-3 mt-3 mt-md-0">
                        <span class="badge bg-success">
                            <i class="fas fa-shield-alt me-1"></i>Secured
                        </span>
                        <span class="badge bg-primary">
                            <i class="fas fa-lock me-1"></i>SSL Protected
                        </span>
                        <span class="badge bg-info">
                            <i class="fas fa-certificate me-1"></i>GDPR Compliant
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Custom JavaScript for enhanced interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth scrolling to anchor links
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
            
            // Animate stats on scroll
            const observerOptions = {
                threshold: 0.5,
                rootMargin: '0px 0px -100px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const statNumber = entry.target.querySelector('.stat-number');
                        if (statNumber && !statNumber.classList.contains('animated')) {
                            statNumber.classList.add('animated');
                            animateCounter(statNumber);
                        }
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('.security-stat').forEach(stat => {
                observer.observe(stat);
            });
            
            function animateCounter(element) {
                const text = element.textContent;
                const number = parseFloat(text.replace(/[^\d.]/g, ''));
                const suffix = text.replace(/[\d.]/g, '');
                const prefix = text.match(/^\D+/) ? text.match(/^\D+/)[0] : '';
                
                if (isNaN(number)) return;
                
                const duration = 2000;
                const stepTime = 50;
                const steps = duration / stepTime;
                const increment = number / steps;
                
                let currentNumber = 0;
                const timer = setInterval(() => {
                    currentNumber += increment;
                    if (currentNumber >= number) {
                        currentNumber = number;
                        clearInterval(timer);
                    }
                    
                    let displayNumber;
                    if (number % 1 === 0) {
                        displayNumber = Math.floor(currentNumber);
                    } else {
                        displayNumber = currentNumber.toFixed(1);
                    }
                    
                    element.textContent = prefix + displayNumber + suffix;
                }, stepTime);
            }
            
            // Add focus management for modal-like interactions
            const actionButtons = document.querySelectorAll('.btn-outline-primary, .btn-outline-secondary, .btn-outline-danger');
            actionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // In a real implementation, these would open modals or navigate to specific pages
                    alert('This would open the respective data management interface.');
                });
            });
            
            // Enhanced keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    // Close any open modals or dropdowns
                    document.activeElement.blur();
                }
            });
        });
    </script>
</body>
</html>