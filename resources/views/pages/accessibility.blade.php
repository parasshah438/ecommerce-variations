<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Learn about our accessibility features, screen reader compatibility, and how we ensure our website is accessible to all users including those with disabilities.">
    <meta name="keywords" content="accessibility, screen reader, WCAG, disability support, inclusive design">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Accessibility & Screen Reader Information">
    <meta property="og:description" content="Our commitment to making our website accessible to everyone, including detailed information about screen reader support and accessibility features.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <title>Accessibility & Screen Reader Information - {{ config('app.name') }}</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS for enhanced accessibility -->
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
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--info-color) 100%);
            color: white;
            padding: 80px 0;
            margin-bottom: 0;
        }
        
        .accessibility-icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .feature-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 3rem;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }
        
        .text-left .section-title::after {
            left: 0;
            transform: none;
        }
        
        .compliance-badge {
            background: var(--success-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            margin: 0.25rem;
        }
        
        .keyboard-shortcut {
            background: var(--light-color);
            border: 2px solid var(--secondary-color);
            border-radius: 4px;
            padding: 0.25rem 0.5rem;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .contact-card {
            background: var(--light-color);
            border-left: 5px solid var(--primary-color);
            padding: 2rem;
            border-radius: 8px;
        }
        
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--dark-color);
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 1000;
            transition: top 0.3s;
        }
        
        .skip-link:focus {
            top: 6px;
            color: white;
            text-decoration: none;
        }
        
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .feature-card {
                border: 2px solid var(--dark-color);
            }
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .feature-card {
                transition: none;
            }
            
            .feature-card:hover {
                transform: none;
            }
        }
        
        /* Print styles */
        @media print {
            .hero-section {
                background: white !important;
                color: black !important;
            }
            
            .feature-card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
            }
        }
    </style>
</head>
<body>
    <!-- Skip to main content link for screen readers -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Hero Section -->
    <header class="hero-section" role="banner">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <div class="accessibility-icon" role="img" aria-label="Accessibility symbol">
                        <i class="fas fa-universal-access"></i>
                    </div>
                    <h1 class="display-4 fw-bold mb-4">Accessibility & Screen Reader Information</h1>
                    <p class="lead mb-4">
                        We are committed to ensuring our website is accessible to everyone, including users with disabilities. 
                        Learn about our accessibility features and how we support screen readers and assistive technologies.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                        <span class="compliance-badge">WCAG 2.1 AA Compliant</span>
                        <span class="compliance-badge">Section 508 Compatible</span>
                        <span class="compliance-badge">ADA Compliant</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main id="main-content" role="main">
        <!-- Accessibility Features Section -->
        <section class="py-5" aria-labelledby="features-heading">
            <div class="container">
                <h2 id="features-heading" class="section-title text-center">Our Accessibility Features</h2>
                
                <div class="row g-4">
                    <!-- Screen Reader Support -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card feature-card h-100">
                            <div class="card-body text-center">
                                <div class="feature-icon">
                                    <i class="fas fa-volume-up" aria-hidden="true"></i>
                                </div>
                                <h3 class="h5 card-title">Screen Reader Compatible</h3>
                                <p class="card-text">
                                    Full compatibility with popular screen readers including JAWS, NVDA, ORCA, and VoiceOver. 
                                    All content is properly structured with semantic HTML and ARIA labels.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Keyboard Navigation -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card feature-card h-100">
                            <div class="card-body text-center">
                                <div class="feature-icon">
                                    <i class="fas fa-keyboard" aria-hidden="true"></i>
                                </div>
                                <h3 class="h5 card-title">Keyboard Navigation</h3>
                                <p class="card-text">
                                    Navigate our entire website using only your keyboard. All interactive elements are 
                                    accessible via Tab, Enter, and Arrow keys with visible focus indicators.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- High Contrast -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card feature-card h-100">
                            <div class="card-body text-center">
                                <div class="feature-icon">
                                    <i class="fas fa-adjust" aria-hidden="true"></i>
                                </div>
                                <h3 class="h5 card-title">High Contrast Support</h3>
                                <p class="card-text">
                                    Automatic detection and support for high contrast mode. Enhanced color ratios meet 
                                    WCAG AA standards for improved readability.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Text Scaling -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card feature-card h-100">
                            <div class="card-body text-center">
                                <div class="feature-icon">
                                    <i class="fas fa-search-plus" aria-hidden="true"></i>
                                </div>
                                <h3 class="h5 card-title">Text Scaling</h3>
                                <p class="card-text">
                                    Text can be scaled up to 200% without loss of functionality or content. 
                                    Responsive design ensures readability at all zoom levels.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Alternative Text -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card feature-card h-100">
                            <div class="card-body text-center">
                                <div class="feature-icon">
                                    <i class="fas fa-image" aria-hidden="true"></i>
                                </div>
                                <h3 class="h5 card-title">Image Descriptions</h3>
                                <p class="card-text">
                                    All images include descriptive alternative text. Complex images and charts 
                                    provide extended descriptions for complete understanding.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Motion Control -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card feature-card h-100">
                            <div class="card-body text-center">
                                <div class="feature-icon">
                                    <i class="fas fa-pause-circle" aria-hidden="true"></i>
                                </div>
                                <h3 class="h5 card-title">Motion & Animation Control</h3>
                                <p class="card-text">
                                    Respects user preferences for reduced motion. Animations can be paused or disabled 
                                    to prevent vestibular disorders and improve focus.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Keyboard Shortcuts Section -->
        <section class="py-5 bg-light" aria-labelledby="shortcuts-heading">
            <div class="container">
                <h2 id="shortcuts-heading" class="section-title text-center">Keyboard Shortcuts</h2>
                
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" role="table" aria-describedby="shortcuts-description">
                                <caption id="shortcuts-description" class="text-muted">
                                    Keyboard shortcuts available throughout our website for improved navigation
                                </caption>
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">Action</th>
                                        <th scope="col">Windows/Linux</th>
                                        <th scope="col">Mac</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Skip to main content</td>
                                        <td><span class="keyboard-shortcut">Tab</span> then <span class="keyboard-shortcut">Enter</span></td>
                                        <td><span class="keyboard-shortcut">Tab</span> then <span class="keyboard-shortcut">Return</span></td>
                                    </tr>
                                    <tr>
                                        <td>Navigate between elements</td>
                                        <td><span class="keyboard-shortcut">Tab</span> / <span class="keyboard-shortcut">Shift + Tab</span></td>
                                        <td><span class="keyboard-shortcut">Tab</span> / <span class="keyboard-shortcut">Shift + Tab</span></td>
                                    </tr>
                                    <tr>
                                        <td>Activate links/buttons</td>
                                        <td><span class="keyboard-shortcut">Enter</span> or <span class="keyboard-shortcut">Space</span></td>
                                        <td><span class="keyboard-shortcut">Return</span> or <span class="keyboard-shortcut">Space</span></td>
                                    </tr>
                                    <tr>
                                        <td>Navigate dropdown menus</td>
                                        <td><span class="keyboard-shortcut">Arrow Keys</span></td>
                                        <td><span class="keyboard-shortcut">Arrow Keys</span></td>
                                    </tr>
                                    <tr>
                                        <td>Close modal dialogs</td>
                                        <td><span class="keyboard-shortcut">Escape</span></td>
                                        <td><span class="keyboard-shortcut">Escape</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Screen Reader Instructions -->
        <section class="py-5" aria-labelledby="instructions-heading">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <h2 id="instructions-heading" class="section-title text-left">Screen Reader Instructions</h2>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h3 class="h5 text-primary">Navigation Landmarks</h3>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2" aria-hidden="true"></i> Header navigation</li>
                                    <li><i class="fas fa-check text-success me-2" aria-hidden="true"></i> Main content area</li>
                                    <li><i class="fas fa-check text-success me-2" aria-hidden="true"></i> Footer information</li>
                                    <li><i class="fas fa-check text-success me-2" aria-hidden="true"></i> Search functionality</li>
                                    <li><i class="fas fa-check text-success me-2" aria-hidden="true"></i> Product listings</li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h3 class="h5 text-primary">Content Structure</h3>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2" aria-hidden="true"></i> Proper heading hierarchy</li>
                                    <li><i class="fas fa-check text-success me-2" aria-hidden="true"></i> Descriptive link text</li>
                                    <li><i class="fas fa-check text-success me-2" aria-hidden="true"></i> Form labels and instructions</li>
                                    <li><i class="fas fa-check text-success me-2" aria-hidden="true"></i> Table headers and captions</li>
                                    <li><i class="fas fa-check text-success me-2" aria-hidden="true"></i> List structures for content</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h3 class="h5 text-primary">Recommended Screen Readers</h3>
                            <div class="row g-3">
                                <div class="col-sm-6 col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <strong>JAWS</strong><br>
                                        <small class="text-muted">Windows</small>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <strong>NVDA</strong><br>
                                        <small class="text-muted">Windows (Free)</small>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <strong>VoiceOver</strong><br>
                                        <small class="text-muted">Mac/iOS</small>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <strong>ORCA</strong><br>
                                        <small class="text-muted">Linux</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="py-5 bg-light" aria-labelledby="contact-heading">
            <div class="container">
                <h2 id="contact-heading" class="section-title text-center">Accessibility Support</h2>
                
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="contact-card">
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <h3 class="h5 text-primary">Need Assistance?</h3>
                                    <p>
                                        If you experience any accessibility barriers while using our website, or if you have 
                                        suggestions for improving our accessibility features, please don't hesitate to contact us.
                                    </p>
                                    <p class="mb-0">
                                        <strong>Our commitment:</strong> We will respond to accessibility inquiries within 24 hours 
                                        and work to resolve any issues promptly.
                                    </p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="feature-icon text-primary">
                                        <i class="fas fa-headset" aria-hidden="true"></i>
                                    </div>
                                    <p class="mb-2"><strong>Email:</strong><br>
                                    <a href="mailto:accessibility@example.com">accessibility@example.com</a></p>
                                    <p class="mb-0"><strong>Phone:</strong><br>
                                    <a href="tel:+1234567890">(123) 456-7890</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4" role="contentinfo">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 {{ config('app.name') }}. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="{{ route('welcome') }}" class="text-light text-decoration-none">Back to Home</a> |
                    <a href="{{ route('pages.privacy') }}" class="text-light text-decoration-none">Privacy Policy</a> |
                    <a href="{{ route('pages.terms') }}" class="text-light text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Accessibility enhancements -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add focus indicators for keyboard navigation
            const focusableElements = document.querySelectorAll('a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])');
            
            focusableElements.forEach(element => {
                element.addEventListener('focus', function() {
                    this.style.outline = '2px solid #0d6efd';
                    this.style.outlineOffset = '2px';
                });
                
                element.addEventListener('blur', function() {
                    this.style.outline = '';
                    this.style.outlineOffset = '';
                });
            });
            
            // Announce page load to screen readers
            const announcement = document.createElement('div');
            announcement.setAttribute('aria-live', 'polite');
            announcement.setAttribute('aria-atomic', 'true');
            announcement.className = 'sr-only';
            announcement.textContent = 'Accessibility page loaded successfully. Use Tab to navigate through the page.';
            document.body.appendChild(announcement);
            
            // Remove announcement after it's been read
            setTimeout(() => {
                if (announcement.parentNode) {
                    announcement.parentNode.removeChild(announcement);
                }
            }, 3000);
        });
    </script>
</body>
</html>