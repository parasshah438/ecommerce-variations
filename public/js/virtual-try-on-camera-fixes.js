// Enhanced Camera Security and Compatibility
async function startCameraSecure() {
    try {
        // Check for HTTPS in production
        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            showToast('Camera access requires HTTPS. Please use a secure connection.', 'error');
            return;
        }
        
        // Check browser support
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showToast('Your browser does not support camera access. Please use a modern browser.', 'error');
            return;
        }
        
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const placeholder = document.getElementById('cameraPlaceholder');
        
        // Show loading state
        startBtn.innerHTML = '<div class="loading-spinner"></div>';
        startBtn.disabled = true;
        
        // Request camera access with proper constraints
        const constraints = {
            video: {
                width: { ideal: 1280, max: 1920 },
                height: { ideal: 720, max: 1080 },
                facingMode: 'user',
                frameRate: { ideal: 30, max: 60 }
            }
        };
        
        cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
        
        // Create video element with proper attributes
        const video = document.createElement('video');
        video.srcObject = cameraStream;
        video.autoplay = true;
        video.playsInline = true; // Important for iOS
        video.muted = true; // Prevent audio feedback
        video.style.cssText = `
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 15px;
            transform: scaleX(-1); /* Mirror effect for better UX */
        `;
        
        // Wait for video to load before replacing placeholder
        video.addEventListener('loadedmetadata', () => {
            const container = document.getElementById('cameraContainer');
            container.innerHTML = '';
            container.appendChild(video);
            
            // Show product overlay
            showProductOverlay();
            
            // Update button states
            startBtn.innerHTML = '<i class="bi bi-camera-video"></i>';
            startBtn.disabled = true;
            stopBtn.disabled = false;
            
            showToast('Camera started successfully!', 'success');
        });
        
        // Handle video loading errors
        video.addEventListener('error', (e) => {
            console.error('Video error:', e);
            stopCamera();
            showToast('Error loading video stream', 'error');
        });
        
    } catch (error) {
        console.error('Error accessing camera:', error);
        
        // Restore button state
        const startBtn = document.getElementById('startBtn');
        startBtn.innerHTML = '<i class="bi bi-camera-video"></i>';
        startBtn.disabled = false;
        
        // Provide specific error messages
        let errorMessage = 'Failed to access camera. ';
        switch (error.name) {
            case 'NotAllowedError':
                errorMessage += 'Please allow camera access and try again.';
                break;
            case 'NotFoundError':
                errorMessage += 'No camera found on this device.';
                break;
            case 'NotSupportedError':
                errorMessage += 'Camera access is not supported in this browser.';
                break;
            case 'OverconstrainedError':
                errorMessage += 'Camera constraints could not be satisfied.';
                break;
            case 'SecurityError':
                errorMessage += 'Camera access blocked due to security restrictions.';
                break;
            default:
                errorMessage += 'Please check your camera settings and try again.';
        }
        
        showToast(errorMessage, 'error');
    }
}

function stopCameraSecure() {
    if (cameraStream) {
        // Stop all tracks properly
        cameraStream.getTracks().forEach(track => {
            track.stop();
        });
        cameraStream = null;
        
        // Clean up video element
        const container = document.getElementById('cameraContainer');
        const video = container.querySelector('video');
        if (video) {
            video.srcObject = null;
            video.remove();
        }
        
        // Reset to placeholder
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

// Replace the original functions
if (typeof startCamera !== 'undefined') {
    startCamera = startCameraSecure;
}
if (typeof stopCamera !== 'undefined') {
    stopCamera = stopCameraSecure;
}