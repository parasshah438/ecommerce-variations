<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Try-On - Try Before You Buy</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
        }

        /* Hero Section */
        .hero-section {
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(2deg); }
            66% { transform: translateY(-10px) rotate(-1deg); }
        }

        /* Try-On Interface */
        .try-on-interface {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .camera-container {
            position: relative;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            aspect-ratio: 4/3;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .camera-placeholder {
            color: #666;
            text-align: center;
        }

        .product-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.8;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        /* Product Cards */
        .product-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }

        .product-card:hover::before {
            left: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border-color: var(--bs-primary);
        }

        .product-card.selected {
            border-color: var(--bs-success);
            background: linear-gradient(135deg, #f0fff4, #ffffff);
        }

        /* Feature Cards */
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(102,126,234,0.1) 0%, transparent 70%);
            transform: scale(0);
            transition: transform 0.5s ease;
        }

        .feature-card:hover::before {
            transform: scale(1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            position: relative;
            z-index: 2;
        }

        /* Control Panel */
        .control-panel {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .control-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .control-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transition: all 0.3s ease;
            transform: translate(-50%, -50%);
        }

        .control-btn:hover::before {
            width: 100%;
            height: 100%;
        }

        .control-btn:active {
            transform: scale(0.95);
        }

        /* Size Selector */
        .size-selector {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .size-btn {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            border: 2px solid #dee2e6;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .size-btn:hover {
            border-color: var(--bs-primary);
            background: var(--bs-primary);
            color: white;
            transform: scale(1.1);
        }

        .size-btn.selected {
            border-color: var(--bs-success);
            background: var(--bs-success);
            color: white;
        }

        /* Color Picker */
        .color-picker {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .color-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .color-btn::before {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            border-radius: 50%;
            border: 2px solid transparent;
            transition: border-color 0.3s ease;
        }

        .color-btn:hover::before,
        .color-btn.selected::before {
            border-color: var(--bs-primary);
        }

        .color-btn:hover {
            transform: scale(1.2);
        }

        /* Stats */
        .stat-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 15px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section {
                min-height: 80vh;
                padding: 2rem 0;
            }
            
            .try-on-interface {
                padding: 1rem;
                margin-bottom: 2rem;
            }
            
            .control-btn {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
            
            .feature-card {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .feature-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .size-btn {
                width: 35px;
                height: 35px;
                font-size: 0.8rem;
            }
            
            .color-btn {
                width: 30px;
                height: 30px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }

        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 30px;
            height: 30px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Pulse Animation */
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Gradient Text */
        .gradient-text {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#home">
                <i class="bi bi-camera me-2"></i>VirtualTry
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#try-on">Try On</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="display-3 fw-bold text-white mb-4 floating-animation">
                        Try Before You <span class="gradient-text text-warning">Buy</span>
                    </h1>
                    <p class="lead text-white-50 mb-5">
                        Experience the future of online shopping with our revolutionary virtual try-on technology. 
                        See how clothes look on you without leaving your home.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <button class="btn btn-light btn-lg px-4 py-3 pulse" onclick="scrollToTryOn()">
                            <i class="bi bi-camera-fill me-2"></i>Start Virtual Try-On
                        </button>
                        <button class="btn btn-outline-light btn-lg px-4 py-3" onclick="watchDemo()">
                            <i class="bi bi-play-circle me-2"></i>Watch Demo
                        </button>
                    </div>
                    
                    <!-- Stats -->
                    <div class="row mt-5">
                        <div class="col-4">
                            <div class="stat-card">
                                <div class="stat-number">95%</div>
                                <p class="text-muted small mb-0">Accuracy</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-card">
                                <div class="stat-number">2.5M</div>
                                <p class="text-muted small mb-0">Users</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-card">
                                <div class="stat-number">50k</div>
                                <p class="text-muted small mb-0">Products</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 text-center">
                    <div class="floating-animation">
                        <svg width="400" height="500" viewBox="0 0 400 500" style="max-width: 100%; height: auto;">
                            <!-- Phone frame -->
                            <rect x="50" y="50" width="300" height="400" rx="30" fill="#333" stroke="#555" stroke-width="4"/>
                            <rect x="65" y="75" width="270" height="350" rx="15" fill="#000"/>
                            
                            <!-- Screen -->
                            <rect x="75" y="85" width="250" height="330" rx="10" fill="#1a1a1a"/>
                            
                            <!-- Camera interface -->
                            <circle cx="200" cy="200" r="60" fill="none" stroke="#667eea" stroke-width="3" stroke-dasharray="10,5"/>
                            <circle cx="200" cy="200" r="40" fill="rgba(102,126,234,0.2)"/>
                            
                            <!-- Person silhouette -->
                            <ellipse cx="200" cy="160" rx="20" ry="25" fill="#667eea"/>
                            <rect x="180" y="185" width="40" height="60" rx="20" fill="#667eea"/>
                            <rect x="185" y="245" width="30" height="80" rx="15" fill="#667eea"/>
                            
                            <!-- Clothing overlay -->
                            <rect x="175" y="185" width="50" height="65" rx="25" fill="rgba(245,87,108,0.8)" stroke="#f5576c" stroke-width="2"/>
                            
                            <!-- UI elements -->
                            <circle cx="100" cy="120" r="15" fill="#28a745"/>
                            <rect x="85" y="110" width="30" height="6" rx="3" fill="white"/>
                            <rect x="85" y="124" width="30" height="6" rx="3" fill="white"/>
                            
                            <circle cx="300" cy="120" r="15" fill="#dc3545"/>
                            <text x="300" y="125" text-anchor="middle" fill="white" font-size="12">×</text>
                            
                            <!-- Bottom controls -->
                            <rect x="120" y="380" width="160" height="30" rx="15" fill="rgba(255,255,255,0.1)"/>
                            <circle cx="140" cy="395" r="8" fill="#667eea"/>
                            <circle cx="170" cy="395" r="8" fill="#f5576c"/>
                            <circle cx="200" cy="395" r="8" fill="#28a745"/>
                            <circle cx="230" cy="395" r="8" fill="#ffc107"/>
                            <circle cx="260" cy="395" r="8" fill="#6f42c1"/>
                            
                            <!-- Floating elements -->
                            <circle cx="80" cy="300" r="3" fill="#667eea" opacity="0.6">
                                <animate attributeName="cy" values="300;280;300" dur="3s" repeatCount="indefinite"/>
                            </circle>
                            <circle cx="320" cy="250" r="2" fill="#f5576c" opacity="0.8">
                                <animate attributeName="cy" values="250;230;250" dur="4s" repeatCount="indefinite"/>
                            </circle>
                            <circle cx="60" cy="180" r="4" fill="#28a745" opacity="0.7">
                                <animate attributeName="cy" values="180;160;180" dur="5s" repeatCount="indefinite"/>
                            </circle>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Virtual Try-On Interface -->
    <section id="try-on" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Virtual Try-On Studio</h2>
                <p class="lead text-muted">Use your camera to see how our products look on you in real-time</p>
            </div>

            <div class="row g-4">
                <!-- Camera/Try-On Area -->
                <div class="col-lg-8">
                    <div class="try-on-interface">
                        <div class="camera-container" id="cameraContainer">
                            <div class="camera-placeholder" id="cameraPlaceholder">
                                <i class="bi bi-camera display-1 text-secondary mb-3"></i>
                                <h5 class="text-muted">Click "Start Camera" to begin</h5>
                                <p class="small text-muted">Make sure to allow camera access when prompted</p>
                            </div>
                            
                            <!-- Product overlay will be added here -->
                            <div class="product-overlay" id="productOverlay" style="display: none;">
                                <img src="" alt="Virtual Product" style="max-width: 200px; height: auto;">
                            </div>
                        </div>

                        <!-- Camera Controls -->
                        <div class="control-panel mt-4">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex gap-3 justify-content-center justify-content-md-start">
                                        <button class="control-btn btn-success" onclick="startCamera()" id="startBtn">
                                            <i class="bi bi-camera-video"></i>
                                        </button>
                                        <button class="control-btn btn-danger" onclick="stopCamera()" id="stopBtn" disabled>
                                            <i class="bi bi-camera-video-off"></i>
                                        </button>
                                        <button class="control-btn btn-primary" onclick="takeScreenshot()">
                                            <i class="bi bi-camera"></i>
                                        </button>
                                        <button class="control-btn btn-warning" onclick="toggleFilters()">
                                            <i class="bi bi-magic"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                                    <div class="btn-group" role="group">
                                        <input type="radio" class="btn-check" name="viewMode" id="front" autocomplete="off" checked>
                                        <label class="btn btn-outline-primary" for="front">Front View</label>
                                        
                                        <input type="radio" class="btn-check" name="viewMode" id="side" autocomplete="off">
                                        <label class="btn btn-outline-primary" for="side">Side View</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Size and Color Controls -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h6 class="text-center mb-3">Select Size</h6>
                                <div class="size-selector">
                                    <div class="size-btn" onclick="selectSize(this, 'XS')">XS</div>
                                    <div class="size-btn" onclick="selectSize(this, 'S')">S</div>
                                    <div class="size-btn selected" onclick="selectSize(this, 'M')">M</div>
                                    <div class="size-btn" onclick="selectSize(this, 'L')">L</div>
                                    <div class="size-btn" onclick="selectSize(this, 'XL')">XL</div>
                                    <div class="size-btn" onclick="selectSize(this, 'XXL')">XXL</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-center mb-3">Select Color</h6>
                                <div class="color-picker">
                                    <div class="color-btn selected" style="background: #dc3545;" onclick="selectColor(this, '#dc3545')"></div>
                                    <div class="color-btn" style="background: #007bff;" onclick="selectColor(this, '#007bff')"></div>
                                    <div class="color-btn" style="background: #28a745;" onclick="selectColor(this, '#28a745')"></div>
                                    <div class="color-btn" style="background: #ffc107;" onclick="selectColor(this, '#ffc107')"></div>
                                    <div class="color-btn" style="background: #6f42c1;" onclick="selectColor(this, '#6f42c1')"></div>
                                    <div class="color-btn" style="background: #fd7e14;" onclick="selectColor(this, '#fd7e14')"></div>
                                    <div class="color-btn" style="background: #20c997;" onclick="selectColor(this, '#20c997')"></div>
                                    <div class="color-btn" style="background: #6c757d;" onclick="selectColor(this, '#6c757d')"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Selection -->
                <div class="col-lg-4">
                    <h5 class="mb-3">Choose Product</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="product-card selected" onclick="selectProduct(this, 'T-Shirt')">
                                <div class="text-center">
                                    <i class="bi bi-person display-4 text-primary mb-2"></i>
                                    <h6 class="mb-1">Classic T-Shirt</h6>
                                    <p class="small text-muted mb-2">$29.99</p>
                                    <div class="d-flex justify-content-center">
                                        <span class="badge bg-success me-1">4.8★</span>
                                        <span class="small text-muted">(324 reviews)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="product-card" onclick="selectProduct(this, 'Hoodie')">
                                <div class="text-center">
                                    <i class="bi bi-person-arms-up display-4 text-primary mb-2"></i>
                                    <h6 class="mb-1">Comfy Hoodie</h6>
                                    <p class="small text-muted mb-2">$59.99</p>
                                    <div class="d-flex justify-content-center">
                                        <span class="badge bg-success me-1">4.9★</span>
                                        <span class="small text-muted">(189 reviews)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="product-card" onclick="selectProduct(this, 'Jacket')">
                                <div class="text-center">
                                    <i class="bi bi-person-standing display-4 text-primary mb-2"></i>
                                    <h6 class="mb-1">Winter Jacket</h6>
                                    <p class="small text-muted mb-2">$129.99</p>
                                    <div class="d-flex justify-content-center">
                                        <span class="badge bg-success me-1">4.7★</span>
                                        <span class="small text-muted">(256 reviews)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="product-card" onclick="selectProduct(this, 'Dress')">
                                <div class="text-center">
                                    <i class="bi bi-person-dress display-4 text-primary mb-2"></i>
                                    <h6 class="mb-1">Summer Dress</h6>
                                    <p class="small text-muted mb-2">$79.99</p>
                                    <div class="d-flex justify-content-center">
                                        <span class="badge bg-success me-1">4.6★</span>
                                        <span class="small text-muted">(432 reviews)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add to Cart -->
                    <div class="mt-4">
                        <button class="btn btn-primary w-100 py-3" onclick="addToCart()">
                            <i class="bi bi-cart-plus me-2"></i>Add to Cart
                        </button>
                        <button class="btn btn-outline-secondary w-100 mt-2" onclick="saveToWishlist()">
                            <i class="bi bi-heart me-2"></i>Save to Wishlist
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Why Choose Virtual Try-On?</h2>
                <p class="lead text-muted">Experience the future of online shopping with cutting-edge AR technology</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card h-100">
                        <div class="feature-icon" style="background: var(--primary-gradient);">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <h4 class="mb-3">Real-Time Preview</h4>
                        <p class="text-muted">See how clothes look on you instantly with our advanced AR technology. No waiting, no guessing.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card h-100">
                        <div class="feature-icon" style="background: var(--secondary-gradient);">
                            <i class="bi bi-rulers"></i>
                        </div>
                        <h4 class="mb-3">Perfect Fit</h4>
                        <p class="text-muted">Our AI analyzes your body measurements to recommend the perfect size every time.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card h-100">
                        <div class="feature-icon" style="background: var(--success-gradient);">
                            <i class="bi bi-palette"></i>
                        </div>
                        <h4 class="mb-3">Multiple Colors</h4>
                        <p class="text-muted">Try different colors and patterns without ordering multiple items. Save time and money.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card h-100">
                        <div class="feature-icon" style="background: var(--warning-gradient);">
                            <i class="bi bi-share"></i>
                        </div>
                        <h4 class="mb-3">Share & Compare</h4>
                        <p class="text-muted">Share your virtual try-on with friends and family to get their opinion before buying.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card h-100">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="mb-3">Privacy First</h4>
                        <p class="text-muted">Your images are processed locally on your device. We never store or share your photos.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card h-100">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="bi bi-arrow-return-left"></i>
                        </div>
                        <h4 class="mb-3">Hassle-Free Returns</h4>
                        <p class="text-muted">Not satisfied? Easy returns within 30 days. Try before you buy with confidence.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Showcase -->
    <section id="products" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Featured Products</h2>
                <p class="lead text-muted">Try these popular items with our virtual try-on technology</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="product-card h-100">
                        <div class="position-relative mb-3">
                            <div class="bg-light rounded-3 p-4 text-center" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-person display-1 text-primary"></i>
                            </div>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-success">New</span>
                            </div>
                        </div>
                        <h5 class="mb-2">Premium Cotton T-Shirt</h5>
                        <p class="text-muted small mb-2">100% organic cotton, perfect for everyday wear</p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="h5 mb-0 text-primary">$34.99</span>
                            <div>
                                <span class="badge bg-warning text-dark me-1">4.8★</span>
                                <span class="small text-muted">(156)</span>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary w-100 mb-2" onclick="tryProduct('premium-tshirt')">
                            <i class="bi bi-camera me-2"></i>Try On
                        </button>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="product-card h-100">
                        <div class="position-relative mb-3">
                            <div class="bg-light rounded-3 p-4 text-center" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-person-arms-up display-1 text-primary"></i>
                            </div>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-danger">Hot</span>
                            </div>
                        </div>
                        <h5 class="mb-2">Cozy Pullover Hoodie</h5>
                        <p class="text-muted small mb-2">Soft fleece interior, adjustable drawstring hood</p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="h5 mb-0 text-primary">$64.99</span>
                            <div>
                                <span class="badge bg-warning text-dark me-1">4.9★</span>
                                <span class="small text-muted">(203)</span>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary w-100 mb-2" onclick="tryProduct('cozy-hoodie')">
                            <i class="bi bi-camera me-2"></i>Try On
                        </button>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="product-card h-100">
                        <div class="position-relative mb-3">
                            <div class="bg-light rounded-3 p-4 text-center" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-person-standing display-1 text-primary"></i>
                            </div>
                        </div>
                        <h5 class="mb-2">Weather Shield Jacket</h5>
                        <p class="text-muted small mb-2">Waterproof, breathable, perfect for outdoor activities</p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="h5 mb-0 text-primary">$149.99</span>
                            <div>
                                <span class="badge bg-warning text-dark me-1">4.7★</span>
                                <span class="small text-muted">(89)</span>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary w-100 mb-2" onclick="tryProduct('weather-jacket')">
                            <i class="bi bi-camera me-2"></i>Try On
                        </button>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="product-card h-100">
                        <div class="position-relative mb-3">
                            <div class="bg-light rounded-3 p-4 text-center" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-person-dress display-1 text-primary"></i>
                            </div>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-info">Trending</span>
                            </div>
                        </div>
                        <h5 class="mb-2">Elegant Summer Dress</h5>
                        <p class="text-muted small mb-2">Lightweight fabric, perfect for warm weather</p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="h5 mb-0 text-primary">$89.99</span>
                            <div>
                                <span class="badge bg-warning text-dark me-1">4.6★</span>
                                <span class="small text-muted">(142)</span>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary w-100 mb-2" onclick="tryProduct('summer-dress')">
                            <i class="bi bi-camera me-2"></i>Try On
                        </button>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5">
                <button class="btn btn-primary btn-lg px-5" onclick="viewAllProducts()">
                    View All Products
                    <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-dark text-light py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="mb-4">
                        <i class="bi bi-camera me-2"></i>VirtualTry
                    </h5>
                    <p class="text-light opacity-75 mb-4">
                        Revolutionizing online shopping with cutting-edge virtual try-on technology. 
                        Try before you buy, every time.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light opacity-75 fs-5"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light opacity-75 fs-5"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light opacity-75 fs-5"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light opacity-75 fs-5"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="#home" class="text-light opacity-75 text-decoration-none">Home</a></li>
                        <li><a href="#try-on" class="text-light opacity-75 text-decoration-none">Try On</a></li>
                        <li><a href="#features" class="text-light opacity-75 text-decoration-none">Features</a></li>
                        <li><a href="#products" class="text-light opacity-75 text-decoration-none">Products</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light opacity-75 text-decoration-none">Help Center</a></li>
                        <li><a href="#" class="text-light opacity-75 text-decoration-none">Size Guide</a></li>
                        <li><a href="#" class="text-light opacity-75 text-decoration-none">Returns</a></li>
                        <li><a href="#" class="text-light opacity-75 text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4">
                    <h6 class="mb-3">Stay Updated</h6>
                    <p class="text-light opacity-75 mb-3">Get the latest updates on new features and products.</p>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Enter your email">
                        <button class="btn btn-primary" type="button">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <hr class="my-4 opacity-25">
            
            <div class="text-center text-light opacity-75">
                <p class="mb-0">&copy; 2024 VirtualTry. All rights reserved. | Privacy Policy | Terms of Service</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Global variables
        let cameraStream = null;
        let selectedProduct = 'T-Shirt';
        let selectedSize = 'M';
        let selectedColor = '#dc3545';
        let isFilterEnabled = false;

        // Smooth scrolling for navigation links
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

        // Update active nav link on scroll
        window.addEventListener('scroll', () => {
            let current = '';
            const sections = document.querySelectorAll('section[id]');
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                if (window.pageYOffset >= sectionTop) {
                    current = section.getAttribute('id');
                }
            });

            document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });

        // Camera functions
        async function startCamera() {
            try {
                const startBtn = document.getElementById('startBtn');
                const stopBtn = document.getElementById('stopBtn');
                const placeholder = document.getElementById('cameraPlaceholder');
                
                // Show loading state
                startBtn.innerHTML = '<div class="loading-spinner"></div>';
                startBtn.disabled = true;
                
                // Request camera access
                cameraStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: { ideal: 1280 }, 
                        height: { ideal: 720 },
                        facingMode: 'user'
                    } 
                });
                
                // Create video element
                const video = document.createElement('video');
                video.srcObject = cameraStream;
                video.autoplay = true;
                video.playsInline = true;
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.objectFit = 'cover';
                video.style.borderRadius = '15px';
                
                // Replace placeholder with video
                const container = document.getElementById('cameraContainer');
                container.innerHTML = '';
                container.appendChild(video);
                
                // Show product overlay
                showProductOverlay();
                
                // Update button states
                startBtn.innerHTML = '<i class="bi bi-camera-video"></i>';
                startBtn.disabled = true;
                stopBtn.disabled = false;
                
                // Show success message
                showToast('Camera started successfully!', 'success');
                
            } catch (error) {
                console.error('Error accessing camera:', error);
                document.getElementById('startBtn').innerHTML = '<i class="bi bi-camera-video"></i>';
                document.getElementById('startBtn').disabled = false;
                
                let errorMessage = 'Failed to access camera. ';
                if (error.name === 'NotAllowedError') {
                    errorMessage += 'Please allow camera access and try again.';
                } else if (error.name === 'NotFoundError') {
                    errorMessage += 'No camera found on this device.';
                } else {
                    errorMessage += 'Please check your camera settings.';
                }
                
                showToast(errorMessage, 'error');
            }
        }

        function stopCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
                
                // Reset to placeholder
                const container = document.getElementById('cameraContainer');
                container.innerHTML = `
                    <div class="camera-placeholder" id="cameraPlaceholder">
                        <i class="bi bi-camera display-1 text-secondary mb-3"></i>
                        <h5 class="text-muted">Click "Start Camera" to begin</h5>
                        <p class="small text-muted">Make sure to allow camera access when prompted</p>
                    </div>
                `;
                
                // Update button states
                document.getElementById('startBtn').disabled = false;
                document.getElementById('stopBtn').disabled = true;
                
                showToast('Camera stopped', 'info');
            }
        }

        function takeScreenshot() {
            if (!cameraStream) {
                showToast('Please start the camera first', 'warning');
                return;
            }
            
            const video = document.querySelector('#cameraContainer video');
            if (!video) return;
            
            // Create canvas for screenshot
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            
            // Download screenshot
            const link = document.createElement('a');
            link.download = `virtual-tryron-${Date.now()}.png`;
            link.href = canvas.toDataURL();
            link.click();
            
            showToast('Screenshot saved!', 'success');
        }

        function toggleFilters() {
            isFilterEnabled = !isFilterEnabled;
            const video = document.querySelector('#cameraContainer video');
            
            if (video) {
                if (isFilterEnabled) {
                    video.style.filter = 'brightness(1.1) contrast(1.1) saturate(1.2)';
                    showToast('Beauty filter enabled', 'success');
                } else {
                    video.style.filter = 'none';
                    showToast('Beauty filter disabled', 'info');
                }
            }
        }

        function showProductOverlay() {
            const overlay = document.getElementById('productOverlay');
            overlay.style.display = 'block';
            // In a real implementation, this would show the actual product overlay
            // For demo purposes, we'll just show it's working
        }

        // Product selection functions
        function selectProduct(element, productName) {
            // Remove selected class from all products
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked product
            element.classList.add('selected');
            selectedProduct = productName;
            
            // Update overlay if camera is active
            if (cameraStream) {
                showProductOverlay();
            }
            
            showToast(`Selected: ${productName}`, 'success');
        }

        function selectSize(element, size) {
            // Remove selected class from all sizes
            document.querySelectorAll('.size-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            // Add selected class to clicked size
            element.classList.add('selected');
            selectedSize = size;
            
            showToast(`Size changed to: ${size}`, 'info');
        }

        function selectColor(element, color) {
            // Remove selected class from all colors
            document.querySelectorAll('.color-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            // Add selected class to clicked color
            element.classList.add('selected');
            selectedColor = color;
            
            showToast('Color updated', 'info');
        }

        // Shopping functions
        function addToCart() {
            showToast(`Added ${selectedProduct} (${selectedSize}) to cart!`, 'success');
            
            // Simulate cart animation
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 me-2"></i>Added to Cart';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        }

        function saveToWishlist() {
            showToast(`Saved ${selectedProduct} to wishlist!`, 'success');
        }

        function tryProduct(productId) {
            showToast(`Loading ${productId} for virtual try-on...`, 'info');
            
            // Scroll to try-on section
            document.getElementById('try-on').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            
            // Auto-start camera if not already running
            setTimeout(() => {
                if (!cameraStream) {
                    startCamera();
                }
            }, 1000);
        }

        // Utility functions
        function scrollToTryOn() {
            document.getElementById('try-on').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        function watchDemo() {
            showToast('Demo video coming soon!', 'info');
        }

        function viewAllProducts() {
            showToast('Redirecting to product catalog...', 'info');
        }

        function showToast(message, type = 'info') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible position-fixed`;
            toast.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
            
            toast.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 5000);
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth animations to feature cards
            const observeFeatures = () => {
                const cards = document.querySelectorAll('.feature-card, .product-card');
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry, index) => {
                        if (entry.isIntersecting) {
                            setTimeout(() => {
                                entry.target.style.opacity = '1';
                                entry.target.style.transform = 'translateY(0)';
                            }, index * 100);
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '50px'
                });

                cards.forEach(card => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(30px)';
                    card.style.transition = 'all 0.6s ease';
                    observer.observe(card);
                });
            };

            observeFeatures();
            
            // Show welcome message
            setTimeout(() => {
                showToast('Welcome to Virtual Try-On! Start by clicking "Start Camera" to begin.', 'info');
            }, 1000);
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
</body>
</html>