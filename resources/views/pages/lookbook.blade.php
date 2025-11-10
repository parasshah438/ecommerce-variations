<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lookbook & Gallery - Your Company</title>
    
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
            --dark-color: #212529;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--dark-color), var(--primary-color));
            color: white;
            padding: 100px 0 60px;
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" style="stop-color:rgba(255,255,255,.1)"/><stop offset="100%" style="stop-color:rgba(255,255,255,0)"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)" opacity="0.5"><animate attributeName="cx" values="200;800;200" dur="20s" repeatCount="indefinite"/><animate attributeName="cy" values="200;600;200" dur="15s" repeatCount="indefinite"/></circle><circle cx="800" cy="400" r="150" fill="url(%23a)" opacity="0.3"><animate attributeName="cx" values="800;200;800" dur="25s" repeatCount="indefinite"/><animate attributeName="cy" values="400;800;400" dur="18s" repeatCount="indefinite"/></circle></svg>');
            background-size: cover;
            opacity: 0.3;
        }
        
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            margin-bottom: 2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .gallery-item.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        .gallery-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .gallery-item img {
            width: 100%;
            height: auto;
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover img {
            transform: scale(1.1);
        }
        
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(0, 123, 255, 0.8), rgba(253, 126, 20, 0.8));
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        
        .gallery-overlay-content {
            text-align: center;
            color: white;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover .gallery-overlay-content {
            transform: translateY(0);
        }
        
        .filter-btn {
            border: 2px solid var(--primary-color);
            background: transparent;
            color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            margin: 0.25rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active,
        .filter-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
        
        .lookbook-section {
            padding: 5rem 0;
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
        
        .floating-element {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .modal-content {
            border: none;
            border-radius: 15px;
        }
        
        .modal-body {
            padding: 0;
        }
        
        .modal-body img {
            width: 100%;
            border-radius: 15px;
        }
        
        .collection-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }
        
        .collection-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .collection-content {
            padding: 2rem;
        }
        
        .stats-counter {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .masonry-grid {
            column-count: 3;
            column-gap: 1.5rem;
        }
        
        .masonry-item {
            break-inside: avoid;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .masonry-grid {
                column-count: 2;
            }
        }
        
        @media (max-width: 576px) {
            .masonry-grid {
                column-count: 1;
            }
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
                        <a class="nav-link" href="{{ route('products.index') }}">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('pages.lookbook') }}">Lookbook</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pages.size.guide') }}">Size Guide</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pages.help') }}">Help & Support</a>
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
                    <h1 class="display-4 fw-bold mb-4 floating-element">
                        <i class="bi bi-images me-3"></i>Lookbook & Gallery
                    </h1>
                    <p class="lead mb-4">
                        Discover our latest collections, styling inspiration, and fashion photography. 
                        Get inspired by our curated looks and lifestyle imagery.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#gallery" class="btn btn-light btn-lg pulse-animation">
                            <i class="bi bi-camera me-2"></i>View Gallery
                        </a>
                        <a href="#collections" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-collection me-2"></i>Collections
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <div class="floating-element">
                            <i class="bi bi-camera2 text-white" style="font-size: 8rem; opacity: 0.8;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="stats-counter" data-target="150">0</div>
                    <h5>Photography Sessions</h5>
                    <p class="text-muted">Professional shoots</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="stats-counter" data-target="500">0</div>
                    <h5>Lifestyle Images</h5>
                    <p class="text-muted">High-quality photos</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="stats-counter" data-target="25">0</div>
                    <h5>Collections</h5>
                    <p class="text-muted">Seasonal lookbooks</p>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="stats-counter" data-target="1000">0</div>
                    <h5>Style Inspirations</h5>
                    <p class="text-muted">Curated looks</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Collections Section -->
    <section id="collections" class="lookbook-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center">Featured Collections</h2>
                    <p class="text-center text-muted mb-5">
                        Explore our seasonal collections and signature styles
                    </p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="collection-card">
                        <img src="https://via.placeholder.com/400x300/007bff/ffffff?text=Spring+2024" 
                             alt="Spring 2024 Collection" class="w-100">
                        <div class="collection-content">
                            <h4>Spring 2024</h4>
                            <p class="text-muted">Fresh colors and lightweight fabrics perfect for the new season.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary">New</span>
                                <button class="btn btn-outline-primary btn-sm" onclick="viewCollection('spring-2024')">
                                    <i class="bi bi-eye me-2"></i>View Collection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="collection-card">
                        <img src="https://via.placeholder.com/400x300/fd7e14/ffffff?text=Urban+Style" 
                             alt="Urban Style Collection" class="w-100">
                        <div class="collection-content">
                            <h4>Urban Style</h4>
                            <p class="text-muted">Contemporary designs for the modern city dweller.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-warning">Popular</span>
                                <button class="btn btn-outline-primary btn-sm" onclick="viewCollection('urban-style')">
                                    <i class="bi bi-eye me-2"></i>View Collection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="collection-card">
                        <img src="https://via.placeholder.com/400x300/28a745/ffffff?text=Classic+Essentials" 
                             alt="Classic Essentials Collection" class="w-100">
                        <div class="collection-content">
                            <h4>Classic Essentials</h4>
                            <p class="text-muted">Timeless pieces that never go out of style.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Bestseller</span>
                                <button class="btn btn-outline-primary btn-sm" onclick="viewCollection('classic-essentials')">
                                    <i class="bi bi-eye me-2"></i>View Collection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Filter Section -->
    <section id="gallery" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center">Photo Gallery</h2>
                    <p class="text-center text-muted mb-4">
                        Browse our complete collection of lifestyle and product photography
                    </p>
                    
                    <!-- Filter Buttons -->
                    <div class="text-center mb-5">
                        <button class="filter-btn active" data-filter="all">All Photos</button>
                        <button class="filter-btn" data-filter="lifestyle">Lifestyle</button>
                        <button class="filter-btn" data-filter="product">Products</button>
                        <button class="filter-btn" data-filter="fashion">Fashion</button>
                        <button class="filter-btn" data-filter="behind-scenes">Behind Scenes</button>
                    </div>
                </div>
            </div>

            <!-- Masonry Gallery -->
            <div class="masonry-grid" id="photo-gallery">
                <!-- Gallery items will be dynamically generated -->
            </div>
        </div>
    </section>

    <!-- Photography Process Section -->
    <section class="lookbook-section bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title text-center text-white">Our Photography Process</h2>
                    <p class="text-center text-muted mb-5">
                        Behind the scenes of our creative process
                    </p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="bi bi-lightbulb text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h5>Concept</h5>
                        <p class="text-muted">Creative ideation and mood board development</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="bi bi-palette text-info" style="font-size: 4rem;"></i>
                        </div>
                        <h5>Styling</h5>
                        <p class="text-muted">Professional styling and wardrobe curation</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="bi bi-camera text-primary" style="font-size: 4rem;"></i>
                        </div>
                        <h5>Photography</h5>
                        <p class="text-muted">High-quality photography with professional equipment</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="bi bi-brush text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h5>Post-Production</h5>
                        <p class="text-muted">Expert editing and color grading</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="imageModalLabel">Gallery Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img id="modalImage" src="" alt="Gallery Image" class="w-100">
                    <div class="mt-3">
                        <h6 id="modalImageTitle">Image Title</h6>
                        <p id="modalImageDescription" class="text-muted">Image description</p>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary" onclick="shareImage()">
                        <i class="bi bi-share me-2"></i>Share
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="mb-3">
                        <i class="bi bi-shop me-2"></i>Your Company
                    </h5>
                    <p class="text-muted">
                        Capturing style and lifestyle through professional photography. 
                        Discover inspiration in every frame.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-pinterest"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Gallery</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.lookbook') }}" class="text-muted text-decoration-none">Lookbook</a></li>
                        <li><a href="#gallery" class="text-muted text-decoration-none">Photo Gallery</a></li>
                        <li><a href="#collections" class="text-muted text-decoration-none">Collections</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Behind Scenes</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Follow Us</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Instagram</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Pinterest</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Facebook</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">YouTube</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3">Photography Services</h6>
                    <div class="text-muted">
                        <p>Professional photography services available for:</p>
                        <ul class="list-unstyled">
                            <li>• Product photography</li>
                            <li>• Lifestyle shoots</li>
                            <li>• Fashion photography</li>
                        </ul>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center text-muted">
                <p>&copy; {{ date('Y') }} Your Company. All rights reserved. | Photography & Style Inspiration</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Gallery data
        const galleryData = [
            {
                id: 1,
                src: 'https://via.placeholder.com/400x600/007bff/ffffff?text=Lifestyle+1',
                category: 'lifestyle',
                title: 'Urban Lifestyle',
                description: 'Contemporary style in the city'
            },
            {
                id: 2,
                src: 'https://via.placeholder.com/400x300/fd7e14/ffffff?text=Product+1',
                category: 'product',
                title: 'Product Showcase',
                description: 'Premium quality products'
            },
            {
                id: 3,
                src: 'https://via.placeholder.com/400x500/28a745/ffffff?text=Fashion+1',
                category: 'fashion',
                title: 'Fashion Forward',
                description: 'Latest trends and styles'
            },
            {
                id: 4,
                src: 'https://via.placeholder.com/400x400/6f42c1/ffffff?text=Behind+Scenes+1',
                category: 'behind-scenes',
                title: 'Behind the Scenes',
                description: 'Photography process'
            },
            {
                id: 5,
                src: 'https://via.placeholder.com/400x650/dc3545/ffffff?text=Lifestyle+2',
                category: 'lifestyle',
                title: 'Casual Elegance',
                description: 'Effortless style for everyday'
            },
            {
                id: 6,
                src: 'https://via.placeholder.com/400x350/17a2b8/ffffff?text=Product+2',
                category: 'product',
                title: 'Product Details',
                description: 'Craftsmanship and quality'
            },
            {
                id: 7,
                src: 'https://via.placeholder.com/400x550/ffc107/ffffff?text=Fashion+2',
                category: 'fashion',
                title: 'Seasonal Collection',
                description: 'New arrivals for spring'
            },
            {
                id: 8,
                src: 'https://via.placeholder.com/400x450/20c997/ffffff?text=Behind+Scenes+2',
                category: 'behind-scenes',
                title: 'Studio Setup',
                description: 'Professional photography setup'
            },
            {
                id: 9,
                src: 'https://via.placeholder.com/400x600/6610f2/ffffff?text=Lifestyle+3',
                category: 'lifestyle',
                title: 'Weekend Vibes',
                description: 'Relaxed weekend styling'
            },
            {
                id: 10,
                src: 'https://via.placeholder.com/400x300/e83e8c/ffffff?text=Product+3',
                category: 'product',
                title: 'Accessory Focus',
                description: 'Perfect finishing touches'
            },
            {
                id: 11,
                src: 'https://via.placeholder.com/400x500/fd7e14/ffffff?text=Fashion+3',
                category: 'fashion',
                title: 'Evening Wear',
                description: 'Elegant evening collection'
            },
            {
                id: 12,
                src: 'https://via.placeholder.com/400x400/198754/ffffff?text=Behind+Scenes+3',
                category: 'behind-scenes',
                title: 'Team Work',
                description: 'Creative collaboration'
            }
        ];

        // Initialize gallery
        function initializeGallery() {
            const gallery = document.getElementById('photo-gallery');
            gallery.innerHTML = '';
            
            galleryData.forEach((item, index) => {
                const galleryItem = createGalleryItem(item, index);
                gallery.appendChild(galleryItem);
            });
            
            // Animate items
            setTimeout(() => {
                document.querySelectorAll('.gallery-item').forEach((item, index) => {
                    setTimeout(() => {
                        item.classList.add('animate');
                    }, index * 100);
                });
            }, 100);
        }

        // Create gallery item
        function createGalleryItem(item, index) {
            const div = document.createElement('div');
            div.className = `gallery-item masonry-item ${item.category}`;
            div.innerHTML = `
                <img src="${item.src}" alt="${item.title}" loading="lazy">
                <div class="gallery-overlay">
                    <div class="gallery-overlay-content">
                        <h5>${item.title}</h5>
                        <p>${item.description}</p>
                        <button class="btn btn-light btn-sm" onclick="openImageModal(${item.id})">
                            <i class="bi bi-eye me-2"></i>View
                        </button>
                    </div>
                </div>
            `;
            
            div.addEventListener('click', () => openImageModal(item.id));
            return div;
        }

        // Filter functionality
        function setupFilters() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Update active button
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    
                    // Filter items
                    const filter = button.dataset.filter;
                    filterGallery(filter);
                });
            });
        }

        // Filter gallery items
        function filterGallery(filter) {
            const items = document.querySelectorAll('.gallery-item');
            
            items.forEach(item => {
                if (filter === 'all' || item.classList.contains(filter)) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.classList.add('animate');
                    }, 100);
                } else {
                    item.classList.remove('animate');
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        }

        // Open image modal
        function openImageModal(imageId) {
            const image = galleryData.find(item => item.id === imageId);
            if (image) {
                document.getElementById('modalImage').src = image.src;
                document.getElementById('modalImageTitle').textContent = image.title;
                document.getElementById('modalImageDescription').textContent = image.description;
                
                const modal = new bootstrap.Modal(document.getElementById('imageModal'));
                modal.show();
            }
        }

        // Share image function
        function shareImage() {
            if (navigator.share) {
                navigator.share({
                    title: 'Check out this image from our lookbook!',
                    url: window.location.href
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                const url = window.location.href;
                navigator.clipboard.writeText(url).then(() => {
                    alert('Link copied to clipboard!');
                });
            }
        }

        // View collection function
        function viewCollection(collectionName) {
            alert(`Viewing ${collectionName} collection. This will redirect to the collection page.`);
        }

        // Animate counters
        function animateCounters() {
            const counters = document.querySelectorAll('.stats-counter');
            
            counters.forEach(counter => {
                const target = parseInt(counter.dataset.target);
                const increment = target / 100;
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    counter.textContent = Math.floor(current);
                }, 20);
            });
        }

        // Intersection observer for animations
        function setupIntersectionObserver() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        if (entry.target.classList.contains('stats-counter')) {
                            animateCounters();
                            observer.unobserve(entry.target);
                        }
                    }
                });
            }, {
                threshold: 0.5
            });

            document.querySelectorAll('.stats-counter').forEach(counter => {
                observer.observe(counter);
            });
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeGallery();
            setupFilters();
            setupIntersectionObserver();
            
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
        });

        // Lazy loading for images
        if ('loading' in HTMLImageElement.prototype) {
            const images = document.querySelectorAll('img[loading="lazy"]');
            images.forEach(img => {
                img.addEventListener('load', () => {
                    img.style.opacity = '1';
                });
            });
        } else {
            // Fallback for browsers that don't support lazy loading
            const script = document.createElement('script');
            script.src = 'https://polyfill.io/v3/polyfill.min.js?features=IntersectionObserver';
            document.head.appendChild(script);
        }
    </script>
</body>
</html>