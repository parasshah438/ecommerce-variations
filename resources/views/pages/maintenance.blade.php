<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Under Maintenance - Your Company</title>
    
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
            --success-color: #28a745;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        .maintenance-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .maintenance-content {
            text-align: center;
            color: white;
            z-index: 10;
            position: relative;
            max-width: 800px;
        }
        
        .gear-container {
            position: relative;
            display: inline-block;
            margin-bottom: 2rem;
        }
        
        .gear {
            font-size: 6rem;
            color: rgba(255, 255, 255, 0.9);
            animation: rotate 4s linear infinite;
            margin: 0 1rem;
        }
        
        .gear:nth-child(2) {
            animation: rotate 4s linear infinite reverse;
            animation-delay: -2s;
        }
        
        .gear:nth-child(3) {
            animation: rotate 3s linear infinite;
            animation-delay: -1s;
        }
        
        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .maintenance-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            animation: slideInUp 1s ease-out;
        }
        
        .maintenance-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: slideInUp 1s ease-out 0.2s both;
        }
        
        .maintenance-description {
            font-size: 1.1rem;
            margin-bottom: 3rem;
            opacity: 0.8;
            animation: slideInUp 1s ease-out 0.4s both;
            line-height: 1.6;
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
        
        .countdown-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem 0;
            backdrop-filter: blur(10px);
            animation: slideInUp 1s ease-out 0.6s both;
        }
        
        .countdown {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .countdown-item {
            text-align: center;
        }
        
        .countdown-number {
            font-size: 3rem;
            font-weight: bold;
            color: #fff;
            display: block;
            line-height: 1;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .countdown-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .progress-container {
            margin: 2rem 0;
            animation: slideInUp 1s ease-out 0.8s both;
        }
        
        .progress {
            height: 8px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--accent-color), var(--primary-color));
            border-radius: 10px;
            animation: progressAnimation 3s ease-out;
        }
        
        @keyframes progressAnimation {
            from {
                width: 0%;
            }
            to {
                width: 75%;
            }
        }
        
        .status-updates {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
            backdrop-filter: blur(10px);
            animation: slideInUp 1s ease-out 1s both;
        }
        
        .status-item {
            display: flex;
            align-items: center;
            margin: 1rem 0;
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .status-item:nth-child(1) { animation-delay: 1.2s; }
        .status-item:nth-child(2) { animation-delay: 1.4s; }
        .status-item:nth-child(3) { animation-delay: 1.6s; }
        .status-item:nth-child(4) { animation-delay: 1.8s; }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
        
        .status-icon {
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        .status-complete {
            color: var(--success-color);
        }
        
        .status-progress {
            color: var(--accent-color);
            animation: pulse 2s infinite;
        }
        
        .status-pending {
            color: rgba(255, 255, 255, 0.5);
        }
        
        @keyframes pulse {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                opacity: 1;
            }
        }
        
        .notification-form {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            backdrop-filter: blur(10px);
            animation: slideInUp 1s ease-out 1.2s both;
        }
        
        .form-control-custom {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50px;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control-custom:focus {
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            outline: none;
        }
        
        .btn-custom {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            border: none;
            color: white;
            padding: 12px 30px;
            font-size: 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            color: white;
        }
        
        .social-links {
            margin: 2rem 0;
            animation: slideInUp 1s ease-out 1.4s both;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 1rem;
            color: white;
            font-size: 2rem;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            color: var(--accent-color);
            transform: translateY(-3px);
        }
        
        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
        }
        
        .floating-shape {
            position: absolute;
            opacity: 0.1;
            animation: float 8s ease-in-out infinite;
        }
        
        .floating-shape:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-shape:nth-child(2) {
            top: 70%;
            left: 80%;
            animation-delay: 2s;
        }
        
        .floating-shape:nth-child(3) {
            top: 30%;
            left: 20%;
            animation-delay: 4s;
        }
        
        .floating-shape:nth-child(4) {
            top: 80%;
            left: 70%;
            animation-delay: 6s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-30px) rotate(180deg);
            }
        }
        
        .maintenance-info {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
            backdrop-filter: blur(10px);
            animation: slideInUp 1s ease-out 1.6s both;
        }
        
        @media (max-width: 768px) {
            .maintenance-title {
                font-size: 2rem;
            }
            
            .maintenance-subtitle {
                font-size: 1.2rem;
            }
            
            .gear {
                font-size: 4rem;
            }
            
            .countdown {
                gap: 1rem;
            }
            
            .countdown-number {
                font-size: 2rem;
            }
            
            .countdown-container {
                padding: 1rem;
            }
        }
        
        .spinner {
            display: inline-block;
            animation: spin 2s linear infinite;
            margin-right: 0.5rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Floating Elements -->
    <div class="floating-elements">
        <i class="bi bi-tools floating-shape" style="font-size: 3rem;"></i>
        <i class="bi bi-gear-fill floating-shape" style="font-size: 2.5rem;"></i>
        <i class="bi bi-wrench floating-shape" style="font-size: 2rem;"></i>
        <i class="bi bi-hammer floating-shape" style="font-size: 3.5rem;"></i>
    </div>
    
    <div class="maintenance-container">
        <div class="container">
            <div class="maintenance-content">
                <!-- Animated Gears -->
                <div class="gear-container">
                    <i class="bi bi-gear-fill gear"></i>
                    <i class="bi bi-gear-wide-connected gear"></i>
                    <i class="bi bi-gear gear"></i>
                </div>
                
                <h1 class="maintenance-title">We'll Be Right Back!</h1>
                <p class="maintenance-subtitle">Our website is currently undergoing scheduled maintenance</p>
                <p class="maintenance-description">
                    We're working hard to improve your experience and will be back online soon. 
                    Thank you for your patience while we make some exciting updates!
                </p>
                
                <!-- Countdown Timer -->
                <div class="countdown-container">
                    <h4 class="mb-4">Estimated Return Time</h4>
                    <div class="countdown" id="countdown">
                        <div class="countdown-item">
                            <span class="countdown-number" id="days">00</span>
                            <span class="countdown-label">Days</span>
                        </div>
                        <div class="countdown-item">
                            <span class="countdown-number" id="hours">00</span>
                            <span class="countdown-label">Hours</span>
                        </div>
                        <div class="countdown-item">
                            <span class="countdown-number" id="minutes">00</span>
                            <span class="countdown-label">Minutes</span>
                        </div>
                        <div class="countdown-item">
                            <span class="countdown-number" id="seconds">00</span>
                            <span class="countdown-label">Seconds</span>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="progress-container">
                    <h5 class="mb-3">Maintenance Progress</h5>
                    <div class="progress">
                        <div class="progress-bar" style="width: 75%"></div>
                    </div>
                    <small class="text-white-50 mt-2 d-block">75% Complete</small>
                </div>
                
                <!-- Status Updates -->
                <div class="status-updates">
                    <h5 class="mb-3">What We're Working On</h5>
                    
                    <div class="status-item">
                        <i class="bi bi-check-circle-fill status-icon status-complete"></i>
                        <span>Database optimization - Complete</span>
                    </div>
                    
                    <div class="status-item">
                        <i class="bi bi-check-circle-fill status-icon status-complete"></i>
                        <span>Security updates - Complete</span>
                    </div>
                    
                    <div class="status-item">
                        <i class="bi bi-arrow-clockwise spinner status-icon status-progress"></i>
                        <span>UI improvements - In Progress</span>
                    </div>
                    
                    <div class="status-item">
                        <i class="bi bi-clock status-icon status-pending"></i>
                        <span>Performance enhancements - Pending</span>
                    </div>
                </div>
                
                <!-- Notification Signup -->
                <div class="notification-form">
                    <h5 class="mb-3">Get Notified When We're Back</h5>
                    <p class="small mb-3">Enter your email to receive an instant notification when the site is live again.</p>
                    
                    <form onsubmit="handleNotificationSignup(event)" class="d-flex gap-3 align-items-center flex-wrap justify-content-center">
                        <div class="flex-grow-1" style="max-width: 300px;">
                            <input type="email" class="form-control form-control-custom" placeholder="Enter your email address" required>
                        </div>
                        <button type="submit" class="btn btn-custom">
                            <i class="bi bi-bell me-2"></i>Notify Me
                        </button>
                    </form>
                </div>
                
                <!-- Emergency Contact -->
                <div class="maintenance-info">
                    <h6 class="mb-3">Need Immediate Assistance?</h6>
                    <div class="d-flex justify-content-center gap-4 flex-wrap">
                        <a href="mailto:support@yourcompany.com" class="text-white text-decoration-none">
                            <i class="bi bi-envelope-fill me-2"></i>support@yourcompany.com
                        </a>
                        <a href="tel:+15551234567" class="text-white text-decoration-none">
                            <i class="bi bi-telephone-fill me-2"></i>+1 (555) 123-4567
                        </a>
                    </div>
                </div>
                
                <!-- Social Links -->
                <div class="social-links">
                    <p class="mb-3">Follow us for live updates:</p>
                    <a href="#" title="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" title="Twitter"><i class="bi bi-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
                
                <!-- Additional Info -->
                <div class="text-center mt-4" style="animation: slideInUp 1s ease-out 1.8s both;">
                    <small class="text-white-50">
                        <i class="bi bi-shield-check me-2"></i>
                        Your data is safe and secure during this maintenance period.
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Set maintenance end time (24 hours from now)
        const maintenanceEnd = new Date().getTime() + (24 * 60 * 60 * 1000);
        
        // Countdown timer function
        function updateCountdown() {
            const now = new Date().getTime();
            const timeLeft = maintenanceEnd - now;
            
            if (timeLeft > 0) {
                const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                
                document.getElementById('days').textContent = days.toString().padStart(2, '0');
                document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
                document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
                document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
            } else {
                // Maintenance completed
                document.getElementById('countdown').innerHTML = `
                    <div class="text-center">
                        <h4 class="text-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Maintenance Complete!
                        </h4>
                        <p>Please refresh the page to continue.</p>
                        <button onclick="location.reload()" class="btn btn-success">
                            <i class="bi bi-arrow-clockwise me-2"></i>Refresh Page
                        </button>
                    </div>
                `;
            }
        }
        
        // Handle notification signup
        function handleNotificationSignup(event) {
            event.preventDefault();
            const email = event.target.querySelector('input[type="email"]').value;
            
            // Simulate API call
            const button = event.target.querySelector('button');
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Signing up...';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Subscribed!';
                button.className = 'btn btn-success';
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.className = 'btn btn-custom';
                    button.disabled = false;
                    event.target.reset();
                }, 3000);
            }, 2000);
            
            // In a real application, you would send the email to your server
            console.log('Email signed up for notifications:', email);
        }
        
        // Add dynamic progress updates
        function updateProgress() {
            const progressBar = document.querySelector('.progress-bar');
            const progressText = document.querySelector('.progress-container small');
            
            let currentProgress = 75;
            const progressInterval = setInterval(() => {
                if (currentProgress < 95) {
                    currentProgress += Math.random() * 2;
                    progressBar.style.width = currentProgress + '%';
                    progressText.textContent = Math.floor(currentProgress) + '% Complete';
                } else {
                    clearInterval(progressInterval);
                    progressBar.style.width = '100%';
                    progressText.textContent = 'Nearly Complete!';
                    progressText.className = 'text-success mt-2 d-block';
                }
            }, 5000);
        }
        
        // Add status updates animation
        function animateStatusUpdates() {
            const statusItems = document.querySelectorAll('.status-item');
            
            // Simulate progress updates
            setTimeout(() => {
                // Update third item to complete
                const thirdItem = statusItems[2];
                const thirdIcon = thirdItem.querySelector('.status-icon');
                thirdIcon.className = 'bi bi-check-circle-fill status-icon status-complete';
                thirdItem.querySelector('span').textContent = 'UI improvements - Complete';
                
                // Update fourth item to in progress
                setTimeout(() => {
                    const fourthItem = statusItems[3];
                    const fourthIcon = fourthItem.querySelector('.status-icon');
                    fourthIcon.className = 'bi bi-arrow-clockwise spinner status-icon status-progress';
                    fourthItem.querySelector('span').textContent = 'Performance enhancements - In Progress';
                }, 3000);
            }, 10000);
        }
        
        // Add particle effects
        function createParticles() {
            const particleCount = 20;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.style.position = 'fixed';
                particle.style.width = '3px';
                particle.style.height = '3px';
                particle.style.background = 'rgba(255, 255, 255, 0.5)';
                particle.style.borderRadius = '50%';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = '100%';
                particle.style.zIndex = '1';
                particle.style.pointerEvents = 'none';
                
                const animation = particle.animate([
                    {
                        transform: 'translateY(0px) scale(1)',
                        opacity: 1
                    },
                    {
                        transform: `translateY(-${window.innerHeight + 100}px) scale(0)`,
                        opacity: 0
                    }
                ], {
                    duration: Math.random() * 3000 + 2000,
                    easing: 'linear'
                });
                
                document.body.appendChild(particle);
                
                animation.addEventListener('finish', () => {
                    particle.remove();
                });
            }
        }
        
        // Add mouse interaction effects
        function addMouseEffects() {
            document.addEventListener('mousemove', (e) => {
                const gears = document.querySelectorAll('.gear');
                const mouseX = e.clientX / window.innerWidth;
                const mouseY = e.clientY / window.innerHeight;
                
                gears.forEach((gear, index) => {
                    const speed = (index + 1) * 0.5;
                    const x = (mouseX - 0.5) * speed * 30;
                    const y = (mouseY - 0.5) * speed * 30;
                    
                    gear.style.transform = `translate(${x}px, ${y}px) rotate(${gear.style.transform.match(/rotate\((.+?)deg\)/)?.[1] || 0}deg)`;
                });
            });
        }
        
        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Start countdown timer
            updateCountdown();
            setInterval(updateCountdown, 1000);
            
            // Start progress updates
            updateProgress();
            
            // Animate status updates
            animateStatusUpdates();
            
            // Add mouse effects
            addMouseEffects();
            
            // Create particles every 2 seconds
            setInterval(createParticles, 2000);
            
            // Add click effects to social links
            const socialLinks = document.querySelectorAll('.social-links a');
            socialLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Add click animation
                    this.style.transform = 'scale(1.2)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-3px)';
                    }, 150);
                    
                    // Simulate social media redirect
                    setTimeout(() => {
                        alert('This would redirect to our social media page!');
                    }, 300);
                });
            });
            
            // Add periodic "system check" messages
            const messages = [
                "Running system diagnostics...",
                "Optimizing database performance...",
                "Updating security protocols...",
                "Testing new features...",
                "Almost ready to go live!"
            ];
            
            let messageIndex = 0;
            setInterval(() => {
                const statusDiv = document.createElement('div');
                statusDiv.className = 'alert alert-info alert-dismissible fade show position-fixed';
                statusDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
                statusDiv.innerHTML = `
                    <i class="bi bi-info-circle me-2"></i>
                    ${messages[messageIndex]}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                document.body.appendChild(statusDiv);
                
                setTimeout(() => {
                    if (statusDiv.parentNode) {
                        statusDiv.remove();
                    }
                }, 5000);
                
                messageIndex = (messageIndex + 1) % messages.length;
            }, 15000);
        });
    </script>
</body>
</html>