<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Your Company</title>
    
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
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .error-content {
            text-align: center;
            color: white;
            z-index: 10;
            position: relative;
        }
        
        .error-number {
            font-size: 12rem;
            font-weight: 900;
            line-height: 1;
            color: rgba(255, 255, 255, 0.1);
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
            animation: glow 2s ease-in-out infinite alternate;
            margin-bottom: -2rem;
        }
        
        @keyframes glow {
            from {
                text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
            }
            to {
                text-shadow: 0 0 30px rgba(255, 255, 255, 0.8), 0 0 40px rgba(255, 255, 255, 0.8);
            }
        }
        
        .error-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            animation: slideInUp 1s ease-out;
        }
        
        .error-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: slideInUp 1s ease-out 0.2s both;
        }
        
        .error-description {
            font-size: 1.1rem;
            margin-bottom: 3rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0.8;
            animation: slideInUp 1s ease-out 0.4s both;
        }
        
        .error-actions {
            animation: slideInUp 1s ease-out 0.6s both;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
        }
        
        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            top: 60%;
            left: 80%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            top: 40%;
            left: 20%;
            animation-delay: 4s;
        }
        
        .shape:nth-child(4) {
            top: 80%;
            left: 70%;
            animation-delay: 1s;
        }
        
        .shape:nth-child(5) {
            top: 10%;
            left: 60%;
            animation-delay: 3s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }
        
        .btn-custom {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            margin: 0.5rem;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            color: white;
        }
        
        .btn-outline-custom {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            margin: 0.5rem;
        }
        
        .btn-outline-custom:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.3);
        }
        
        .search-container {
            max-width: 500px;
            margin: 2rem auto;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-color);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            background: var(--accent-color);
            transform: translateY(-50%) scale(1.1);
        }
        
        .suggestions {
            margin-top: 2rem;
            animation: slideInUp 1s ease-out 0.8s both;
        }
        
        .suggestion-item {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            margin: 5px;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .suggestion-item:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }
        
        .robot-container {
            position: relative;
            display: inline-block;
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: particle 3s linear infinite;
        }
        
        @keyframes particle {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }
        
        .error-code {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.2);
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
        }
        
        @media (max-width: 768px) {
            .error-number {
                font-size: 8rem;
            }
            
            .error-title {
                font-size: 2rem;
            }
            
            .error-subtitle {
                font-size: 1.2rem;
            }
            
            .btn-custom, .btn-outline-custom {
                display: block;
                width: 100%;
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body>
    <div class="error-code">Error 404</div>
    
    <!-- Floating Particles -->
    <div class="particles" id="particles"></div>
    
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <i class="bi bi-triangle shape" style="font-size: 3rem;"></i>
        <i class="bi bi-circle shape" style="font-size: 2.5rem;"></i>
        <i class="bi bi-square shape" style="font-size: 2rem;"></i>
        <i class="bi bi-hexagon shape" style="font-size: 3.5rem;"></i>
        <i class="bi bi-star shape" style="font-size: 2.8rem;"></i>
    </div>
    
    <div class="error-container">
        <div class="container">
            <div class="error-content">
                <div class="error-number">404</div>
                
                <div class="robot-container mb-4">
                    <i class="bi bi-robot" style="font-size: 5rem; color: rgba(255, 255, 255, 0.8);"></i>
                </div>
                
                <h1 class="error-title">Oops! Page Not Found</h1>
                <p class="error-subtitle">The page you're looking for seems to have gone on vacation!</p>
                <p class="error-description">
                    Don't worry, it happens to the best of us. The page you're trying to reach might have been moved, 
                    deleted, or perhaps you've mistyped the URL. Let's get you back on track!
                </p>
                
                <!-- Search Box -->
                <div class="search-container">
                    <form onsubmit="handleSearch(event)">
                        <input type="text" class="search-input" placeholder="Search for products, pages, or help..." id="searchInput">
                        <button type="submit" class="search-btn">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
                
                <!-- Action Buttons -->
                <div class="error-actions">
                    <a href="{{ route('welcome') }}" class="btn btn-custom">
                        <i class="bi bi-house-fill me-2"></i>Back to Home
                    </a>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-custom">
                        <i class="bi bi-shop me-2"></i>Browse Products
                    </a>
                </div>
                
                <!-- Suggestions -->
                <div class="suggestions">
                    <p class="mb-3">Or try one of these popular pages:</p>
                    <a href="{{ route('pages.about') }}" class="suggestion-item">About Us</a>
                    <a href="{{ route('pages.help') }}" class="suggestion-item">Help & Support</a>
                    <a href="{{ route('pages.faq') }}" class="suggestion-item">FAQ</a>
                    <a href="{{ route('pages.lookbook') }}" class="suggestion-item">Lookbook</a>
                    <a href="{{ route('pages.size.guide') }}" class="suggestion-item">Size Guide</a>
                    <a href="{{ route('pages.shipping') }}" class="suggestion-item">Shipping Info</a>
                </div>
                
                <!-- Contact Information -->
                <div class="mt-5" style="animation: slideInUp 1s ease-out 1s both;">
                    <p class="mb-2">Still can't find what you're looking for?</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="{{ route('pages.help') }}" class="text-white text-decoration-none">
                            <i class="bi bi-chat-dots me-2"></i>Live Chat
                        </a>
                        <a href="mailto:support@yourcompany.com" class="text-white text-decoration-none">
                            <i class="bi bi-envelope me-2"></i>Email Support
                        </a>
                        <a href="tel:+15551234567" class="text-white text-decoration-none">
                            <i class="bi bi-telephone me-2"></i>Call Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                setTimeout(() => {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.animationDuration = (Math.random() * 3 + 2) + 's';
                    particle.style.animationDelay = Math.random() * 2 + 's';
                    
                    particlesContainer.appendChild(particle);
                    
                    // Remove particle after animation
                    setTimeout(() => {
                        if (particle.parentNode) {
                            particle.parentNode.removeChild(particle);
                        }
                    }, 5000);
                }, i * 100);
            }
        }
        
        // Handle search form submission
        function handleSearch(event) {
            event.preventDefault();
            const searchTerm = document.getElementById('searchInput').value.trim();
            
            if (searchTerm) {
                // In a real application, this would perform an actual search
                // For now, we'll redirect to the products page with a search parameter
                window.location.href = `{{ route('products.index') }}?search=${encodeURIComponent(searchTerm)}`;
            }
        }
        
        // Add interactive effects to buttons
        function addButtonEffects() {
            const buttons = document.querySelectorAll('.btn-custom, .btn-outline-custom');
            
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.05)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        }
        
        // Add typing effect to the search placeholder
        function addTypingEffect() {
            const searchInput = document.getElementById('searchInput');
            const phrases = [
                'Search for products...',
                'Find what you need...',
                'Type your query here...',
                'Search our catalog...'
            ];
            let currentPhrase = 0;
            let currentChar = 0;
            let isDeleting = false;
            
            function typeEffect() {
                const phrase = phrases[currentPhrase];
                
                if (!isDeleting && currentChar <= phrase.length) {
                    searchInput.placeholder = phrase.substring(0, currentChar);
                    currentChar++;
                } else if (isDeleting && currentChar >= 0) {
                    searchInput.placeholder = phrase.substring(0, currentChar);
                    currentChar--;
                } else if (!isDeleting && currentChar > phrase.length) {
                    isDeleting = true;
                    setTimeout(typeEffect, 1000);
                    return;
                } else if (isDeleting && currentChar < 0) {
                    isDeleting = false;
                    currentPhrase = (currentPhrase + 1) % phrases.length;
                    currentChar = 0;
                }
                
                const typingSpeed = isDeleting ? 50 : 100;
                setTimeout(typeEffect, typingSpeed);
            }
            
            typeEffect();
        }
        
        // Add mouse parallax effect
        function addParallaxEffect() {
            document.addEventListener('mousemove', (e) => {
                const shapes = document.querySelectorAll('.shape');
                const errorNumber = document.querySelector('.error-number');
                
                const mouseX = e.clientX / window.innerWidth;
                const mouseY = e.clientY / window.innerHeight;
                
                shapes.forEach((shape, index) => {
                    const speed = (index + 1) * 0.5;
                    const x = (mouseX - 0.5) * speed * 50;
                    const y = (mouseY - 0.5) * speed * 50;
                    
                    shape.style.transform = `translate(${x}px, ${y}px) rotate(${x}deg)`;
                });
                
                // Parallax effect for 404 number
                const x = (mouseX - 0.5) * 20;
                const y = (mouseY - 0.5) * 20;
                errorNumber.style.transform = `translate(${x}px, ${y}px)`;
            });
        }
        
        // Add glitch effect to error number
        function addGlitchEffect() {
            const errorNumber = document.querySelector('.error-number');
            
            setInterval(() => {
                if (Math.random() < 0.1) { // 10% chance
                    errorNumber.style.textShadow = `
                        ${Math.random() * 10 - 5}px ${Math.random() * 10 - 5}px 0 #ff00ff,
                        ${Math.random() * 10 - 5}px ${Math.random() * 10 - 5}px 0 #00ffff
                    `;
                    
                    setTimeout(() => {
                        errorNumber.style.textShadow = '0 0 30px rgba(255, 255, 255, 0.8), 0 0 40px rgba(255, 255, 255, 0.8)';
                    }, 100);
                }
            }, 2000);
        }
        
        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Start particle animation
            createParticles();
            setInterval(createParticles, 5000); // Create new particles every 5 seconds
            
            // Add interactive effects
            addButtonEffects();
            addTypingEffect();
            addParallaxEffect();
            addGlitchEffect();
            
            // Add pulse effect to robot
            const robot = document.querySelector('.robot-container i');
            setInterval(() => {
                robot.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    robot.style.transform = 'scale(1)';
                }, 200);
            }, 3000);
            
            // Add click effect to search suggestions
            const suggestions = document.querySelectorAll('.suggestion-item');
            suggestions.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Add ripple effect
                    const ripple = document.createElement('span');
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.background = 'rgba(255, 255, 255, 0.5)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.left = (e.clientX - this.offsetLeft) + 'px';
                    ripple.style.top = (e.clientY - this.offsetTop) + 'px';
                    
                    this.style.position = 'relative';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        });
        
        // Add service worker for offline functionality (optional)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('ServiceWorker registration successful');
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
</body>
</html>