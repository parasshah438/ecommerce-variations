<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Your Company</title>
    
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
            padding: 100px 0;
        }
        
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .team-member {
            transition: transform 0.3s ease;
        }
        
        .team-member:hover {
            transform: scale(1.05);
        }
        
        .stats-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }
        
        .counter {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-color);
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
                        <a class="nav-link active" href="{{ route('pages.about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pages.faq') }}">FAQ</a>
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
                    <h1 class="display-4 fw-bold mb-4">About Our Story</h1>
                    <p class="lead mb-4">
                        We're passionate about delivering exceptional products and creating meaningful connections with our customers. 
                        Our journey started with a simple mission: to make quality accessible to everyone.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="{{ route('products.index') }}" class="btn btn-light btn-lg">
                            <i class="bi bi-shop me-2"></i>Shop Now
                        </a>
                        <a href="#mission" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-arrow-down me-2"></i>Learn More
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <img src="https://via.placeholder.com/500x400/007bff/ffffff?text=Our+Story" 
                             alt="About Us" class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="counter">50K+</div>
                    <h5>Happy Customers</h5>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="counter">10K+</div>
                    <h5>Products Sold</h5>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="counter">5+</div>
                    <h5>Years Experience</h5>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="counter">99%</div>
                    <h5>Satisfaction Rate</h5>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section id="mission" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center">Our Mission & Vision</h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card feature-card h-100 shadow">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-bullseye text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h4>Our Mission</h4>
                            <p class="text-muted">
                                To provide high-quality products that enhance our customers' lives while building 
                                lasting relationships based on trust and exceptional service.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card feature-card h-100 shadow">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-eye text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h4>Our Vision</h4>
                            <p class="text-muted">
                                To become the most trusted and innovative company in our industry, 
                                setting new standards for quality and customer satisfaction.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card feature-card h-100 shadow">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-heart text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h4>Our Values</h4>
                            <p class="text-muted">
                                Integrity, innovation, customer focus, and sustainability guide everything we do. 
                                We believe in making a positive impact on our community.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center">Meet Our Team</h2>
                    <p class="text-center text-muted mb-5">
                        The passionate people behind our success
                    </p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card team-member border-0 shadow">
                        <img src="https://via.placeholder.com/300x300/007bff/ffffff?text=CEO" 
                             class="card-img-top" alt="CEO">
                        <div class="card-body text-center">
                            <h5 class="card-title">John Smith</h5>
                            <p class="text-muted">Chief Executive Officer</p>
                            <p class="card-text">
                                With over 15 years of experience in the industry, John leads our team 
                                with vision and dedication.
                            </p>
                            <div class="social-links">
                                <a href="#" class="text-primary me-3"><i class="bi bi-linkedin"></i></a>
                                <a href="#" class="text-primary me-3"><i class="bi bi-twitter"></i></a>
                                <a href="#" class="text-primary"><i class="bi bi-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card team-member border-0 shadow">
                        <img src="https://via.placeholder.com/300x300/fd7e14/ffffff?text=CTO" 
                             class="card-img-top" alt="CTO">
                        <div class="card-body text-center">
                            <h5 class="card-title">Sarah Johnson</h5>
                            <p class="text-muted">Chief Technology Officer</p>
                            <p class="card-text">
                                Sarah drives our technological innovation and ensures we stay ahead 
                                of industry trends.
                            </p>
                            <div class="social-links">
                                <a href="#" class="text-primary me-3"><i class="bi bi-linkedin"></i></a>
                                <a href="#" class="text-primary me-3"><i class="bi bi-twitter"></i></a>
                                <a href="#" class="text-primary"><i class="bi bi-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card team-member border-0 shadow">
                        <img src="https://via.placeholder.com/300x300/28a745/ffffff?text=CMO" 
                             class="card-img-top" alt="CMO">
                        <div class="card-body text-center">
                            <h5 class="card-title">Michael Brown</h5>
                            <p class="text-muted">Chief Marketing Officer</p>
                            <p class="card-text">
                                Michael crafts our brand story and ensures our message reaches 
                                the right audience effectively.
                            </p>
                            <div class="social-links">
                                <a href="#" class="text-primary me-3"><i class="bi bi-linkedin"></i></a>
                                <a href="#" class="text-primary me-3"><i class="bi bi-twitter"></i></a>
                                <a href="#" class="text-primary"><i class="bi bi-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center">Why Choose Us</h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="bi bi-shield-check text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5>Quality Assurance</h5>
                        <p class="text-muted">
                            Every product undergoes rigorous quality checks to ensure excellence.
                        </p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="bi bi-truck text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5>Fast Shipping</h5>
                        <p class="text-muted">
                            Quick and reliable delivery to get your orders to you as fast as possible.
                        </p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="bi bi-headset text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5>24/7 Support</h5>
                        <p class="text-muted">
                            Our customer support team is always ready to help you with any questions.
                        </p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="bi bi-award text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h5>Best Prices</h5>
                        <p class="text-muted">
                            Competitive pricing without compromising on quality or service.
                        </p>
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

        // Counter animation
        function animateCounters() {
            const counters = document.querySelectorAll('.counter');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
                const increment = target / 100;
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    let displayValue = Math.floor(current);
                    if (counter.textContent.includes('K')) {
                        displayValue = Math.floor(current / 1000) + 'K+';
                    } else if (counter.textContent.includes('%')) {
                        displayValue = Math.floor(current) + '%';
                    } else {
                        displayValue = Math.floor(current) + '+';
                    }
                    
                    counter.textContent = displayValue;
                }, 20);
            });
        }

        // Trigger counter animation when stats section is in view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        });

        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            observer.observe(statsSection);
        }
    </script>
</body>
</html>