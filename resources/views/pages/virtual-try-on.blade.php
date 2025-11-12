<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ $isAuthenticated ? 'true' : 'false' }}">
    <title>Virtual Try-On - Try Before You Buy</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- AR.js and Three.js Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/pose/pose.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js"></script>
    
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

        /* Loading Spinner */
        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Enhanced Product Cards */
        .product-card.selected {
            border: 2px solid var(--bs-primary);
            background: rgba(var(--bs-primary-rgb), 0.1);
            transform: scale(1.02);
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        /* AR Overlay System */
        .ar-container {
            position: relative;
            overflow: hidden;
        }

        .ar-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10;
        }

        .ar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 15;
        }

        .tracking-points {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #00ff00;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.7;
            z-index: 20;
        }

        .body-outline {
            position: absolute;
            border: 2px solid rgba(0, 255, 0, 0.5);
            border-radius: 50%;
            pointer-events: none;
            z-index: 5;
        }

        /* AR Control Panel */
        .ar-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            border-radius: 15px;
            padding: 10px 20px;
            z-index: 25;
        }

        .ar-toggle {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            margin: 0 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .ar-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .ar-toggle.active {
            background: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        /* Performance Indicators */
        .performance-stats {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
            z-index: 30;
        }

        /* 3D Product Renderer */
        .product-3d-renderer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 12;
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
                        <div class="camera-container ar-container" id="cameraContainer">
                            <div class="camera-placeholder" id="cameraPlaceholder">
                                <i class="bi bi-camera display-1 text-secondary mb-3"></i>
                                <h5 class="text-muted">Click "Start AR Camera" to begin</h5>
                                <p class="small text-muted">Make sure to allow camera access when prompted</p>
                                <div class="mt-3">
                                    <small class="text-info">
                                        <i class="bi bi-lightbulb me-1"></i>
                                        Enhanced with AI body tracking for realistic try-on experience
                                    </small>
                                </div>
                            </div>
                            
                            <!-- AR Canvas for Three.js rendering -->
                            <canvas class="ar-canvas" id="arCanvas" style="display: none;"></canvas>
                            
                            <!-- Body tracking overlay -->
                            <div class="ar-overlay" id="arOverlay" style="display: none;">
                                <!-- Tracking points will be added here dynamically -->
                            </div>
                            
                            <!-- 3D Product Renderer -->
                            <div class="product-3d-renderer" id="product3dRenderer" style="display: none;"></div>
                            
                            <!-- Performance Stats -->
                            <div class="performance-stats" id="performanceStats" style="display: none;">
                                <div>FPS: <span id="fpsCounter">0</span></div>
                                <div>Tracking: <span id="trackingStatus">Inactive</span></div>
                                <div>Quality: <span id="renderQuality">High</span></div>
                            </div>
                            
                            <!-- AR Controls -->
                            <div class="ar-controls" id="arControls" style="display: none;">
                                <button class="ar-toggle" id="bodyTrackingToggle" onclick="toggleBodyTracking()">
                                    <i class="bi bi-person-bounding-box me-1"></i>Body Tracking
                                </button>
                                <button class="ar-toggle" id="faceTrackingToggle" onclick="toggleFaceTracking()">
                                    <i class="bi bi-person-circle me-1"></i>Face Tracking
                                </button>
                                <button class="ar-toggle" id="arRenderingToggle" onclick="toggleARRendering()">
                                    <i class="bi bi-cube me-1"></i>3D Rendering
                                </button>
                            </div>
                        </div>

                        <!-- Camera Controls -->
                        <div class="control-panel mt-4">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex gap-3 justify-content-center justify-content-md-start">
                                        <button class="control-btn btn-success" onclick="startCamera()" id="startBtn" title="Start AR Camera">
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
                                <div class="size-selector" id="sizeSelector">
                                    @if($products->isNotEmpty() && $products->first()->available_sizes->isNotEmpty())
                                        @foreach($products->first()->available_sizes->take(6) as $index => $size)
                                            <div class="size-btn {{ $index === 0 ? 'selected' : '' }}" onclick="selectSize(this, '{{ $size }}')">{{ $size }}</div>
                                        @endforeach
                                    @else
                                        <div class="size-btn selected" onclick="selectSize(this, 'M')">M</div>
                                        <div class="size-btn" onclick="selectSize(this, 'L')">L</div>
                                        <div class="size-btn" onclick="selectSize(this, 'XL')">XL</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-center mb-3">Select Color</h6>
                                <div class="color-picker" id="colorPicker">
                                    @if($products->isNotEmpty() && $products->first()->available_colors->isNotEmpty())
                                        @foreach($products->first()->available_colors->take(8) as $index => $color)
                                            @php
                                                $colorCode = match(strtolower($color)) {
                                                    'red' => '#dc3545',
                                                    'blue' => '#007bff',
                                                    'green' => '#28a745',
                                                    'yellow' => '#ffc107',
                                                    'purple' => '#6f42c1',
                                                    'orange' => '#fd7e14',
                                                    'teal' => '#20c997',
                                                    'gray', 'grey' => '#6c757d',
                                                    'black' => '#000000',
                                                    'white' => '#ffffff',
                                                    'pink' => '#e83e8c',
                                                    'brown' => '#8b4513',
                                                    default => '#' . dechex(crc32($color) & 0xFFFFFF)
                                                };
                                            @endphp
                                            <div class="color-btn {{ $index === 0 ? 'selected' : '' }}" 
                                                 style="background: {{ $colorCode }}; {{ $color === 'white' || $colorCode === '#ffffff' ? 'border: 2px solid #dee2e6;' : '' }}" 
                                                 onclick="selectColor(this, '{{ $colorCode }}', '{{ $color }}')"
                                                 title="{{ ucfirst($color) }}"></div>
                                        @endforeach
                                    @else
                                        <div class="color-btn selected" style="background: #dc3545;" onclick="selectColor(this, '#dc3545', 'Red')"></div>
                                        <div class="color-btn" style="background: #007bff;" onclick="selectColor(this, '#007bff', 'Blue')"></div>
                                        <div class="color-btn" style="background: #28a745;" onclick="selectColor(this, '#28a745', 'Green')"></div>
                                        <div class="color-btn" style="background: #ffc107;" onclick="selectColor(this, '#ffc107', 'Yellow')"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Selection -->
                <div class="col-lg-4">
                    <h5 class="mb-3">Choose Product</h5>
                    <div class="row g-3" style="max-height: 400px; overflow-y: auto;">
                        @forelse($products->take(6) as $index => $product)
                            <div class="col-12">
                                <div class="product-card {{ $index === 0 ? 'selected' : '' }}" 
                                     onclick="selectProduct(this, {{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->default_variation ? $product->default_variation->id : 'null' }})">
                                    <div class="text-center">
                                        @if($product->thumbnail)
                                            <img src="{{ asset('storage/' . $product->thumbnail->image_path) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="img-fluid mb-2" 
                                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                        @else
                                            <i class="bi bi-person display-4 text-primary mb-2"></i>
                                        @endif
                                        <h6 class="mb-1">{{ $product->name }}</h6>
                                        <p class="small text-muted mb-2">
                                            @if($product->discount_percentage > 0)
                                                <span class="text-decoration-line-through">₹{{ number_format($product->price, 2) }}</span>
                                                <span class="text-success fw-bold">₹{{ number_format($product->sale_price, 2) }}</span>
                                            @else
                                                ₹{{ number_format($product->price, 2) }}
                                            @endif
                                        </p>
                                        <div class="d-flex justify-content-center">
                                            @if($product->average_rating)
                                                <span class="badge bg-success me-1">{{ number_format($product->average_rating, 1) }}★</span>
                                                <span class="small text-muted">({{ $product->reviews_count }} reviews)</span>
                                            @else
                                                <span class="small text-muted">New Product</span>
                                            @endif
                                        </div>
                                        
                                        @if($product->discount_percentage > 0)
                                            <div class="mt-1">
                                                <span class="badge bg-danger">{{ $product->discount_percentage }}% OFF</span>
                                            </div>
                                        @endif
                                        
                                        <!-- Hidden data for JavaScript -->
                                        <div class="d-none product-data">
                                            <span class="product-id">{{ $product->id }}</span>
                                            <span class="product-name">{{ $product->name }}</span>
                                            <span class="product-price">{{ $product->sale_price }}</span>
                                            <span class="product-slug">{{ $product->slug }}</span>
                                            <span class="default-variation-id">{{ $product->default_variation ? $product->default_variation->id : '' }}</span>
                                            <span class="available-sizes">{{ $product->available_sizes->implode(',') }}</span>
                                            <span class="available-colors">{{ $product->available_colors->implode(',') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center py-4">
                                    <i class="bi bi-box-seam display-4 text-muted mb-3"></i>
                                    <h6 class="text-muted">No products available for virtual try-on</h6>
                                    <p class="small text-muted">Please check back later or browse our full catalog.</p>
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">
                                        Browse All Products
                                    </a>
                                </div>
                            </div>
                        @endforelse
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
                @forelse($products->take(8) as $product)
                    <div class="col-lg-3 col-md-6">
                        <div class="product-card h-100">
                            <div class="position-relative mb-3">
                                @if($product->thumbnail)
                                    <img src="{{ asset('storage/' . $product->thumbnail->image_path) }}" 
                                         alt="{{ $product->name }}" 
                                         class="img-fluid rounded-3" 
                                         style="width: 100%; height: 200px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded-3 p-4 text-center" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-person display-1 text-primary"></i>
                                    </div>
                                @endif
                                
                                @if($product->discount_percentage > 0)
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-danger">{{ $product->discount_percentage }}% OFF</span>
                                    </div>
                                @elseif($product->created_at->diffInDays() < 30)
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-success">New</span>
                                    </div>
                                @endif
                            </div>
                            
                            <h5 class="mb-2">{{ Str::limit($product->name, 30) }}</h5>
                            <p class="text-muted small mb-2">{{ Str::limit($product->description, 60) }}</p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                @if($product->discount_percentage > 0)
                                    <div>
                                        <span class="h6 mb-0 text-muted text-decoration-line-through">₹{{ number_format($product->price, 2) }}</span>
                                        <span class="h5 mb-0 text-primary">₹{{ number_format($product->sale_price, 2) }}</span>
                                    </div>
                                @else
                                    <span class="h5 mb-0 text-primary">₹{{ number_format($product->price, 2) }}</span>
                                @endif
                                
                                <div>
                                    @if($product->average_rating)
                                        <span class="badge bg-warning text-dark me-1">{{ number_format($product->average_rating, 1) }}★</span>
                                        <span class="small text-muted">({{ $product->reviews_count }})</span>
                                    @else
                                        <span class="small text-muted">New Product</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" onclick="tryProduct({{ $product->id }}, '{{ addslashes($product->name) }}')">
                                    <i class="bi bi-camera me-2"></i>Try On
                                </button>
                                <a href="{{ route('products.show', $product->slug) }}" class="btn btn-sm btn-outline-secondary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-box-seam display-4 text-muted mb-3"></i>
                            <h5 class="text-muted">No products available</h5>
                            <p class="text-muted">Check back later for new arrivals!</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary">
                                Browse All Products
                            </a>
                        </div>
                    </div>
                @endforelse
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
        let selectedProductId = null;
        let selectedProductName = '';
        let selectedVariationId = null;
        let selectedSize = '';
        let selectedColor = '';
        let selectedColorName = '';
        let isFilterEnabled = false;
        let isAuthenticated = document.querySelector('meta[name="user-authenticated"]')?.getAttribute('content') === 'true';

        // AR System Variables
        let arSystem = {
            scene: null,
            camera: null,
            renderer: null,
            pose: null,
            faceMesh: null,
            bodyTrackingEnabled: false,
            faceTrackingEnabled: false,
            arRenderingEnabled: false,
            trackingPoints: [],
            productModels: {},
            animationId: null,
            performanceMonitor: {
                fps: 0,
                frameCount: 0,
                lastTime: performance.now()
            }
        };

        // Initialize with first product if available
        document.addEventListener('DOMContentLoaded', function() {
            const firstProduct = document.querySelector('.product-card.selected');
            if (firstProduct) {
                const productData = firstProduct.querySelector('.product-data');
                if (productData) {
                    selectedProductId = parseInt(productData.querySelector('.product-id').textContent);
                    selectedProductName = productData.querySelector('.product-name').textContent;
                    selectedVariationId = productData.querySelector('.default-variation-id').textContent || null;
                    
                    // Set default size and color
                    const firstSize = document.querySelector('.size-btn.selected');
                    const firstColor = document.querySelector('.color-btn.selected');
                    if (firstSize) selectedSize = firstSize.textContent.trim();
                    if (firstColor) {
                        selectedColor = firstColor.style.backgroundColor || firstColor.getAttribute('onclick').match(/'([^']+)'/)[1];
                        selectedColorName = firstColor.getAttribute('title') || 'Default';
                    }
                }
            }
        });

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

        // Enhanced AR Camera functions
        async function startCamera() {
            try {
                const startBtn = document.getElementById('startBtn');
                const stopBtn = document.getElementById('stopBtn');
                const placeholder = document.getElementById('cameraPlaceholder');
                
                // Show loading state
                startBtn.innerHTML = '<i class="bi bi-circle-notch spin me-2"></i>Initializing AR...';
                startBtn.disabled = true;
                
                // Request camera access with higher resolution for AR
                cameraStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: { ideal: 1920, min: 1280 }, 
                        height: { ideal: 1080, min: 720 },
                        facingMode: 'user',
                        frameRate: { ideal: 30, min: 15 }
                    } 
                });
                
                // Create video element
                const video = document.createElement('video');
                video.srcObject = cameraStream;
                video.autoplay = true;
                video.playsInline = true;
                video.muted = true;
                video.style.width = '100%';
                video.style.height = '100%';
                video.style.objectFit = 'cover';
                video.style.borderRadius = '15px';
                video.style.transform = 'scaleX(-1)'; // Mirror for better UX
                
                // Wait for video to load
                await new Promise((resolve) => {
                    video.addEventListener('loadedmetadata', resolve);
                });
                
                // Replace placeholder with video and AR elements
                const container = document.getElementById('cameraContainer');
                const arCanvas = document.getElementById('arCanvas');
                const arOverlay = document.getElementById('arOverlay');
                const arControls = document.getElementById('arControls');
                const performanceStats = document.getElementById('performanceStats');
                
                // Show video
                placeholder.style.display = 'none';
                container.insertBefore(video, container.firstChild);
                
                // Show AR elements
                arCanvas.style.display = 'block';
                arOverlay.style.display = 'block';
                arControls.style.display = 'flex';
                
                // Initialize AR system
                const arInitialized = await initializeARSystem();
                
                if (arInitialized) {
                    // Set up camera processing for MediaPipe
                    setupCameraProcessing(video);
                    showToast('AR Camera started successfully!', 'success');
                } else {
                    showToast('Camera started in basic mode', 'info');
                }
                
                // Show product overlay
                showProductOverlay();
                
                // Update button states
                startBtn.innerHTML = '<i class="bi bi-camera-video"></i>';
                startBtn.disabled = true;
                stopBtn.disabled = false;
                
            } catch (error) {
                console.error('Error starting AR camera:', error);
                document.getElementById('startBtn').innerHTML = '<i class="bi bi-camera-video"></i>';
                document.getElementById('startBtn').disabled = false;
                
                let errorMessage = 'Failed to start AR camera. ';
                if (error.name === 'NotAllowedError') {
                    errorMessage += 'Please allow camera access and try again.';
                } else if (error.name === 'NotFoundError') {
                    errorMessage += 'No camera found on this device.';
                } else if (error.name === 'OverconstrainedError') {
                    errorMessage += 'Camera quality requirements not met. Trying basic mode...';
                    // Fallback to lower quality
                    setTimeout(() => startCameraBasic(), 1000);
                    return;
                } else {
                    errorMessage += 'Please check your camera settings.';
                }
                
                showToast(errorMessage, 'error');
            }
        }

        // Fallback basic camera mode
        async function startCameraBasic() {
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: { ideal: 1280 }, 
                        height: { ideal: 720 },
                        facingMode: 'user'
                    } 
                });
                
                const video = document.createElement('video');
                video.srcObject = cameraStream;
                video.autoplay = true;
                video.playsInline = true;
                video.style.cssText = 'width: 100%; height: 100%; object-fit: cover; border-radius: 15px; transform: scaleX(-1);';
                
                const container = document.getElementById('cameraContainer');
                document.getElementById('cameraPlaceholder').style.display = 'none';
                container.insertBefore(video, container.firstChild);
                
                document.getElementById('startBtn').disabled = true;
                document.getElementById('stopBtn').disabled = false;
                
                showToast('Camera started in basic mode', 'info');
            } catch (error) {
                console.error('Basic camera also failed:', error);
                showToast('Camera initialization failed completely', 'error');
            }
        }

        // Setup camera processing for MediaPipe
        function setupCameraProcessing(video) {
            const processFrame = async () => {
                if (video && video.readyState === 4) {
                    // Process with pose tracking
                    if (arSystem.pose && arSystem.bodyTrackingEnabled) {
                        await arSystem.pose.send({image: video});
                    }
                    
                    // Process with face tracking
                    if (arSystem.faceMesh && arSystem.faceTrackingEnabled) {
                        await arSystem.faceMesh.send({image: video});
                    }
                }
                
                // Continue processing
                if (cameraStream) {
                    requestAnimationFrame(processFrame);
                }
            };
            
            // Start processing
            processFrame();
        }

        function stopCamera() {
            if (cameraStream) {
                // Stop camera stream
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
                
                // Stop AR rendering
                stopARRenderLoop();
                
                // Clear AR resources
                if (arSystem.pose) {
                    arSystem.pose.close();
                }
                if (arSystem.faceMesh) {
                    arSystem.faceMesh.close();
                }
                
                // Hide AR elements
                document.getElementById('arCanvas').style.display = 'none';
                document.getElementById('arOverlay').style.display = 'none';
                document.getElementById('arControls').style.display = 'none';
                document.getElementById('performanceStats').style.display = 'none';
                
                // Reset to placeholder
                const container = document.getElementById('cameraContainer');
                const video = container.querySelector('video');
                if (video) {
                    video.remove();
                }
                
                document.getElementById('cameraPlaceholder').style.display = 'block';
                
                // Update button states
                document.getElementById('startBtn').disabled = false;
                document.getElementById('stopBtn').disabled = true;
                
                showToast('AR Camera stopped', 'info');
            }
        }

        // ========== AR CONTROL FUNCTIONS ==========

        function toggleBodyTracking() {
            arSystem.bodyTrackingEnabled = !arSystem.bodyTrackingEnabled;
            const btn = document.getElementById('bodyTrackingToggle');
            
            if (arSystem.bodyTrackingEnabled) {
                btn.classList.add('active');
                showToast('Body tracking enabled', 'success');
                
                // Initialize pose tracking if not already done
                if (!arSystem.pose && cameraStream) {
                    initPoseTracking();
                }
            } else {
                btn.classList.remove('active');
                showToast('Body tracking disabled', 'info');
                
                // Clear tracking points
                document.querySelectorAll('.tracking-points').forEach(point => point.remove());
                document.getElementById('trackingStatus').textContent = 'Inactive';
            }
        }

        function toggleFaceTracking() {
            arSystem.faceTrackingEnabled = !arSystem.faceTrackingEnabled;
            const btn = document.getElementById('faceTrackingToggle');
            
            if (arSystem.faceTrackingEnabled) {
                btn.classList.add('active');
                showToast('Face tracking enabled', 'success');
                
                // Initialize face tracking if not already done
                if (!arSystem.faceMesh && cameraStream) {
                    initFaceTracking();
                }
            } else {
                btn.classList.remove('active');
                showToast('Face tracking disabled', 'info');
            }
        }

        function toggleARRendering() {
            arSystem.arRenderingEnabled = !arSystem.arRenderingEnabled;
            const btn = document.getElementById('arRenderingToggle');
            const canvas = document.getElementById('arCanvas');
            const renderer3d = document.getElementById('product3dRenderer');
            
            if (arSystem.arRenderingEnabled) {
                btn.classList.add('active');
                canvas.style.display = 'block';
                renderer3d.style.display = 'block';
                showToast('3D AR rendering enabled', 'success');
                
                // Start 3D rendering
                if (selectedProductId) {
                    renderProduct3D();
                }
                
                // Show performance stats
                document.getElementById('performanceStats').style.display = 'block';
                document.getElementById('renderQuality').textContent = 'High';
            } else {
                btn.classList.remove('active');
                canvas.style.display = 'none';
                renderer3d.style.display = 'none';
                stopARRenderLoop();
                showToast('3D AR rendering disabled', 'info');
                
                // Hide performance stats
                document.getElementById('performanceStats').style.display = 'none';
            }
        }

        // ========== PERFORMANCE OPTIMIZATION ==========

        function optimizePerformance() {
            // Adjust quality based on performance
            const fps = arSystem.performanceMonitor.fps;
            const qualityElement = document.getElementById('renderQuality');
            
            if (fps < 15) {
                // Low performance - reduce quality
                if (arSystem.renderer) {
                    arSystem.renderer.setPixelRatio(0.5);
                }
                qualityElement.textContent = 'Low';
                qualityElement.style.color = '#dc3545';
            } else if (fps < 25) {
                // Medium performance
                if (arSystem.renderer) {
                    arSystem.renderer.setPixelRatio(0.75);
                }
                qualityElement.textContent = 'Medium';
                qualityElement.style.color = '#ffc107';
            } else {
                // High performance
                if (arSystem.renderer) {
                    arSystem.renderer.setPixelRatio(1.0);
                }
                qualityElement.textContent = 'High';
                qualityElement.style.color = '#28a745';
            }
        }

        // Auto-optimize performance every 5 seconds
        setInterval(optimizePerformance, 5000);

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
            if (arSystem.arRenderingEnabled) {
                renderProduct3D();
            } else {
                // Fallback to 2D overlay
                const overlay = document.getElementById('arOverlay');
                overlay.style.display = 'block';
                overlay.innerHTML = `
                    <div style="position: absolute; top: 20%; left: 50%; transform: translateX(-50%); 
                                background: rgba(0,0,0,0.7); color: white; padding: 10px 20px; 
                                border-radius: 10px; text-align: center;">
                        <i class="bi bi-shirt me-2"></i>${selectedProductName}
                        <br><small>Size: ${selectedSize} | Color: ${selectedColorName}</small>
                    </div>
                `;
            }
        }

        // ========== AR SYSTEM IMPLEMENTATION ==========

        // Initialize AR System
        async function initializeARSystem() {
            try {
                // Initialize Three.js scene
                initThreeJS();
                
                // Initialize MediaPipe Pose
                if (arSystem.bodyTrackingEnabled) {
                    await initPoseTracking();
                }
                
                // Initialize MediaPipe Face Mesh
                if (arSystem.faceTrackingEnabled) {
                    await initFaceTracking();
                }
                
                console.log('AR System initialized successfully');
                return true;
            } catch (error) {
                console.error('Failed to initialize AR system:', error);
                showToast('AR system initialization failed. Falling back to basic mode.', 'warning');
                return false;
            }
        }

        // Initialize Three.js Scene
        function initThreeJS() {
            const container = document.getElementById('cameraContainer');
            const canvas = document.getElementById('arCanvas');
            
            // Scene setup
            arSystem.scene = new THREE.Scene();
            
            // Camera setup
            arSystem.camera = new THREE.PerspectiveCamera(
                75, 
                container.clientWidth / container.clientHeight, 
                0.1, 
                1000
            );
            arSystem.camera.position.z = 5;
            
            // Renderer setup
            arSystem.renderer = new THREE.WebGLRenderer({ 
                canvas: canvas,
                alpha: true,
                antialias: true 
            });
            arSystem.renderer.setSize(container.clientWidth, container.clientHeight);
            arSystem.renderer.setClearColor(0x000000, 0); // Transparent background
            
            // Lighting
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
            arSystem.scene.add(ambientLight);
            
            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(10, 10, 5);
            arSystem.scene.add(directionalLight);
            
            console.log('Three.js initialized');
        }

        // Initialize Pose Tracking
        async function initPoseTracking() {
            return new Promise((resolve, reject) => {
                arSystem.pose = new Pose({
                    locateFile: (file) => {
                        return `https://cdn.jsdelivr.net/npm/@mediapipe/pose/${file}`;
                    }
                });

                arSystem.pose.setOptions({
                    modelComplexity: 1,
                    smoothLandmarks: true,
                    enableSegmentation: false,
                    smoothSegmentation: true,
                    minDetectionConfidence: 0.5,
                    minTrackingConfidence: 0.5
                });

                arSystem.pose.onResults((results) => {
                    if (results.poseLandmarks) {
                        updateBodyTracking(results.poseLandmarks);
                        updatePerformanceStats();
                    }
                });

                arSystem.pose.initialize().then(() => {
                    console.log('Pose tracking initialized');
                    resolve();
                }).catch(reject);
            });
        }

        // Initialize Face Tracking
        async function initFaceTracking() {
            return new Promise((resolve, reject) => {
                arSystem.faceMesh = new FaceMesh({
                    locateFile: (file) => {
                        return `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`;
                    }
                });

                arSystem.faceMesh.setOptions({
                    maxNumFaces: 1,
                    refineLandmarks: true,
                    minDetectionConfidence: 0.5,
                    minTrackingConfidence: 0.5
                });

                arSystem.faceMesh.onResults((results) => {
                    if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
                        updateFaceTracking(results.multiFaceLandmarks[0]);
                    }
                });

                arSystem.faceMesh.initialize().then(() => {
                    console.log('Face tracking initialized');
                    resolve();
                }).catch(reject);
            });
        }

        // Update Body Tracking
        function updateBodyTracking(landmarks) {
            const overlay = document.getElementById('arOverlay');
            const container = document.getElementById('cameraContainer');
            const containerRect = container.getBoundingClientRect();
            
            // Clear previous tracking points
            overlay.querySelectorAll('.tracking-points').forEach(point => point.remove());
            
            // Key body points for clothing placement
            const keyPoints = [
                11, 12, // Shoulders
                23, 24, // Hips
                13, 14, // Elbows
                15, 16, // Wrists
                0       // Nose (for reference)
            ];
            
            keyPoints.forEach(pointIndex => {
                if (landmarks[pointIndex]) {
                    const point = landmarks[pointIndex];
                    const x = point.x * containerRect.width;
                    const y = point.y * containerRect.height;
                    
                    const trackingPoint = document.createElement('div');
                    trackingPoint.className = 'tracking-points';
                    trackingPoint.style.left = x + 'px';
                    trackingPoint.style.top = y + 'px';
                    overlay.appendChild(trackingPoint);
                }
            });
            
            // Update 3D product position based on body tracking
            if (arSystem.arRenderingEnabled && landmarks[11] && landmarks[12]) {
                updateProductPosition(landmarks);
            }
            
            document.getElementById('trackingStatus').textContent = 'Active';
        }

        // Update Face Tracking
        function updateFaceTracking(landmarks) {
            // Implement face-specific tracking for accessories like glasses, hats, etc.
            if (selectedProductName.toLowerCase().includes('hat') || 
                selectedProductName.toLowerCase().includes('cap') ||
                selectedProductName.toLowerCase().includes('glasses')) {
                
                // Position product on head/face
                const faceCenter = landmarks[6]; // Nose tip
                if (faceCenter && arSystem.arRenderingEnabled) {
                    updateFaceProductPosition(faceCenter);
                }
            }
        }

        // Update 3D Product Position
        function updateProductPosition(bodyLandmarks) {
            if (!arSystem.scene || !selectedProductId) return;
            
            const leftShoulder = bodyLandmarks[11];
            const rightShoulder = bodyLandmarks[12];
            
            if (leftShoulder && rightShoulder) {
                // Calculate center position between shoulders
                const centerX = (leftShoulder.x + rightShoulder.x) / 2;
                const centerY = (leftShoulder.y + rightShoulder.y) / 2;
                const centerZ = (leftShoulder.z + rightShoulder.z) / 2;
                
                // Convert to Three.js coordinates
                const x = (centerX - 0.5) * 10;
                const y = -(centerY - 0.5) * 10;
                const z = centerZ * 10;
                
                // Update product model position
                const productModel = arSystem.productModels[selectedProductId];
                if (productModel) {
                    productModel.position.set(x, y, z);
                    
                    // Scale based on shoulder width
                    const shoulderWidth = Math.abs(leftShoulder.x - rightShoulder.x);
                    const scale = shoulderWidth * 15; // Adjust multiplier as needed
                    productModel.scale.set(scale, scale, scale);
                }
            }
        }

        // Create 3D Product Model
        async function create3DProductModel(productId, productName) {
            // Create a basic 3D model for the product
            // In a real implementation, you would load actual 3D models
            
            let geometry, material;
            
            if (productName.toLowerCase().includes('shirt') || productName.toLowerCase().includes('top')) {
                // T-shirt geometry
                geometry = new THREE.BoxGeometry(2, 2.5, 0.1);
                material = new THREE.MeshLambertMaterial({ 
                    color: selectedColor,
                    transparent: true,
                    opacity: 0.8
                });
            } else if (productName.toLowerCase().includes('jacket')) {
                // Jacket geometry
                geometry = new THREE.BoxGeometry(2.2, 2.8, 0.2);
                material = new THREE.MeshLambertMaterial({ 
                    color: selectedColor,
                    transparent: true,
                    opacity: 0.7
                });
            } else if (productName.toLowerCase().includes('dress')) {
                // Dress geometry
                geometry = new THREE.ConeGeometry(1.5, 4, 8);
                material = new THREE.MeshLambertMaterial({ 
                    color: selectedColor,
                    transparent: true,
                    opacity: 0.7
                });
            } else {
                // Default geometry
                geometry = new THREE.BoxGeometry(2, 2, 0.1);
                material = new THREE.MeshLambertMaterial({ 
                    color: selectedColor,
                    transparent: true,
                    opacity: 0.8
                });
            }
            
            const mesh = new THREE.Mesh(geometry, material);
            mesh.userData = { productId, productName };
            
            // Add to scene and store reference
            arSystem.scene.add(mesh);
            arSystem.productModels[productId] = mesh;
            
            console.log(`Created 3D model for ${productName}`);
            return mesh;
        }

        // Render 3D Product
        async function renderProduct3D() {
            if (!arSystem.arRenderingEnabled || !selectedProductId) return;
            
            // Remove existing product models
            Object.values(arSystem.productModels).forEach(model => {
                arSystem.scene.remove(model);
            });
            arSystem.productModels = {};
            
            // Create new product model
            await create3DProductModel(selectedProductId, selectedProductName);
            
            // Start rendering loop if not already running
            if (!arSystem.animationId) {
                startARRenderLoop();
            }
        }

        // AR Rendering Loop
        function startARRenderLoop() {
            function animate() {
                arSystem.animationId = requestAnimationFrame(animate);
                
                // Update performance stats
                arSystem.performanceMonitor.frameCount++;
                const currentTime = performance.now();
                if (currentTime >= arSystem.performanceMonitor.lastTime + 1000) {
                    arSystem.performanceMonitor.fps = arSystem.performanceMonitor.frameCount;
                    arSystem.performanceMonitor.frameCount = 0;
                    arSystem.performanceMonitor.lastTime = currentTime;
                    
                    document.getElementById('fpsCounter').textContent = arSystem.performanceMonitor.fps;
                }
                
                // Render the scene
                if (arSystem.renderer && arSystem.scene && arSystem.camera) {
                    arSystem.renderer.render(arSystem.scene, arSystem.camera);
                }
            }
            animate();
        }

        // Stop AR Rendering
        function stopARRenderLoop() {
            if (arSystem.animationId) {
                cancelAnimationFrame(arSystem.animationId);
                arSystem.animationId = null;
            }
        }

        // Update Performance Stats
        function updatePerformanceStats() {
            const stats = document.getElementById('performanceStats');
            if (stats) {
                stats.style.display = 'block';
            }
        }

        // Product selection functions
        function selectProduct(element, productId, productName, variationId = null) {
            // Remove selected class from all products
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked product
            element.classList.add('selected');
            
            // Update global variables
            selectedProductId = productId;
            selectedProductName = productName;
            selectedVariationId = variationId;
            
            // Get product data
            const productData = element.querySelector('.product-data');
            if (productData) {
                const availableSizes = productData.querySelector('.available-sizes').textContent.split(',').filter(s => s.trim());
                const availableColors = productData.querySelector('.available-colors').textContent.split(',').filter(c => c.trim());
                
                // Update size options
                updateSizeOptions(availableSizes);
                // Update color options  
                updateColorOptions(availableColors);
            }
            
            // Update overlay if camera is active
            if (cameraStream) {
                showProductOverlay();
            }
            
            showToast(`Selected: ${productName}`, 'success');
        }

        function updateSizeOptions(sizes) {
            const sizeSelector = document.getElementById('sizeSelector');
            if (!sizeSelector || sizes.length === 0) return;
            
            sizeSelector.innerHTML = '';
            sizes.forEach((size, index) => {
                const sizeBtn = document.createElement('div');
                sizeBtn.className = `size-btn ${index === 0 ? 'selected' : ''}`;
                sizeBtn.textContent = size.trim();
                sizeBtn.onclick = () => selectSize(sizeBtn, size.trim());
                sizeSelector.appendChild(sizeBtn);
            });
            
            // Update selected size
            if (sizes.length > 0) {
                selectedSize = sizes[0].trim();
            }
        }

        function updateColorOptions(colors) {
            const colorPicker = document.getElementById('colorPicker');
            if (!colorPicker || colors.length === 0) return;
            
            colorPicker.innerHTML = '';
            colors.forEach((color, index) => {
                const colorCode = getColorCode(color.trim());
                const colorBtn = document.createElement('div');
                colorBtn.className = `color-btn ${index === 0 ? 'selected' : ''}`;
                colorBtn.style.background = colorCode;
                colorBtn.title = color.trim();
                if (color.toLowerCase().includes('white')) {
                    colorBtn.style.border = '2px solid #dee2e6';
                }
                colorBtn.onclick = () => selectColor(colorBtn, colorCode, color.trim());
                colorPicker.appendChild(colorBtn);
            });
            
            // Update selected color
            if (colors.length > 0) {
                selectedColor = getColorCode(colors[0].trim());
                selectedColorName = colors[0].trim();
            }
        }

        function getColorCode(colorName) {
            const colorMap = {
                'red': '#dc3545', 'blue': '#007bff', 'green': '#28a745', 'yellow': '#ffc107',
                'purple': '#6f42c1', 'orange': '#fd7e14', 'teal': '#20c997', 'gray': '#6c757d',
                'grey': '#6c757d', 'black': '#000000', 'white': '#ffffff', 'pink': '#e83e8c',
                'brown': '#8b4513', 'navy': '#000080', 'maroon': '#800000', 'lime': '#00ff00'
            };
            return colorMap[colorName.toLowerCase()] || '#' + Math.floor(Math.random()*16777215).toString(16);
        }

        function selectSize(element, size) {
            // Remove selected class from all sizes
            document.querySelectorAll('.size-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            // Add selected class to clicked size
            element.classList.add('selected');
            selectedSize = size;
            
            // Find matching variation for the selected size/color combination
            updateSelectedVariation();
            
            showToast(`Size changed to: ${size}`, 'info');
        }

        function selectColor(element, colorCode, colorName = 'Color') {
            // Remove selected class from all colors
            document.querySelectorAll('.color-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            // Add selected class to clicked color
            element.classList.add('selected');
            selectedColor = colorCode;
            selectedColorName = colorName;
            
            // Find matching variation for the selected size/color combination
            updateSelectedVariation();
            
            showToast(`Color changed to: ${colorName}`, 'info');
        }

        function updateSelectedVariation() {
            // In a real implementation, you would query the available variations
            // based on the selected size and color to get the correct variation ID
            // For now, we'll keep the default variation ID
            console.log(`Updated selection - Product: ${selectedProductName}, Size: ${selectedSize}, Color: ${selectedColorName}`);
        }

        // Shopping functions
        function addToCart() {
            // Check authentication
            if (!isAuthenticated) {
                showToast('Please login to add items to cart', 'warning');
                setTimeout(() => {
                    window.location.href = '{{ route("login") }}';
                }, 1500);
                return;
            }

            // Validate selection
            if (!selectedProductId || !selectedSize) {
                showToast('Please select a product and size', 'warning');
                return;
            }

            const btn = event.target;
            const originalText = btn.innerHTML;
            
            // Show loading state
            btn.innerHTML = '<i class="bi bi-circle-notch spin me-2"></i>Adding...';
            btn.disabled = true;

            // Prepare cart data
            const cartData = {
                product_variation_id: selectedVariationId || selectedProductId, // Use variation ID if available
                quantity: 1,
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };

            // Make AJAX request to add to cart
            fetch('{{ route("cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(cartData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast(`Added ${selectedProductName} (${selectedSize}) to cart!`, 'success');
                    
                    // Update cart count if available
                    if (data.cart_count !== undefined) {
                        updateCartCount(data.cart_count);
                    }
                    
                    // Success animation
                    btn.innerHTML = '<i class="bi bi-check2 me-2"></i>Added to Cart';
                    
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Failed to add to cart');
                }
            })
            .catch(error => {
                console.error('Error adding to cart:', error);
                showToast(error.message || 'Failed to add to cart. Please try again.', 'error');
                
                // Restore button
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        function saveToWishlist() {
            // Check authentication
            if (!isAuthenticated) {
                showToast('Please login to save items to wishlist', 'warning');
                setTimeout(() => {
                    window.location.href = '{{ route("login") }}';
                }, 1500);
                return;
            }

            // Validate selection
            if (!selectedProductId) {
                showToast('Please select a product', 'warning');
                return;
            }

            // Make AJAX request to toggle wishlist
            fetch('{{ route("wishlist.toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    product_id: selectedProductId,
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const action = data.added ? 'added to' : 'removed from';
                    showToast(`${selectedProductName} ${action} wishlist!`, 'success');
                } else {
                    throw new Error(data.message || 'Failed to update wishlist');
                }
            })
            .catch(error => {
                console.error('Error updating wishlist:', error);
                showToast('Failed to update wishlist. Please try again.', 'error');
            });
        }

        function updateCartCount(count) {
            // Update cart count in navigation or other elements
            const cartCountElements = document.querySelectorAll('.cart-count, .cart-badge');
            cartCountElements.forEach(element => {
                element.textContent = count;
                element.style.display = count > 0 ? 'inline' : 'none';
            });
        }

        function tryProduct(productId, productName = 'Product') {
            showToast(`Loading ${productName} for virtual try-on...`, 'info');
            
            // Find and select the product if it exists in the selection panel
            const productCards = document.querySelectorAll('.product-card');
            let foundProduct = false;
            
            productCards.forEach(card => {
                const productData = card.querySelector('.product-data');
                if (productData) {
                    const cardProductId = parseInt(productData.querySelector('.product-id').textContent);
                    if (cardProductId === productId) {
                        // Simulate clicking on this product
                        const cardName = productData.querySelector('.product-name').textContent;
                        const variationId = productData.querySelector('.default-variation-id').textContent || null;
                        selectProduct(card, productId, cardName, variationId);
                        foundProduct = true;
                    }
                }
            });
            
            if (!foundProduct) {
                // If product not found in selection panel, still update global variables
                selectedProductId = productId;
                selectedProductName = productName;
                selectedVariationId = null;
                showToast(`Selected ${productName} for virtual try-on`, 'success');
            }
            
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