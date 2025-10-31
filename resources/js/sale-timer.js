/**
 * Sale Timer JavaScript
 * Professional countdown timer for sales with multiple display modes
 */

class SaleTimer {
    constructor() {
        this.timers = new Map();
        this.init();
    }

    init() {
        this.initializeTimers();
        // Re-scan for new timers periodically (for dynamic content)
        setInterval(() => this.initializeTimers(), 5000);
    }

    initializeTimers() {
        document.querySelectorAll('[data-end-date]:not([data-timer-initialized])').forEach(element => {
            const endDate = new Date(element.dataset.endDate);
            const timerId = this.generateTimerId();
            
            element.setAttribute('data-timer-initialized', 'true');
            element.setAttribute('data-timer-id', timerId);
            
            this.timers.set(timerId, {
                element: element,
                endDate: endDate,
                interval: null
            });
            
            this.startTimer(timerId);
        });
    }

    generateTimerId() {
        return 'timer_' + Math.random().toString(36).substr(2, 9);
    }

    startTimer(timerId) {
        const timer = this.timers.get(timerId);
        if (!timer) return;

        // Initial update
        this.updateTimer(timerId);
        
        // Set interval for updates
        timer.interval = setInterval(() => {
            this.updateTimer(timerId);
        }, 1000);
    }

    updateTimer(timerId) {
        const timer = this.timers.get(timerId);
        if (!timer) return;

        const now = new Date();
        const difference = timer.endDate - now;
        
        if (difference <= 0) {
            this.handleExpiredTimer(timerId);
            return;
        }

        const timeComponents = this.calculateTimeComponents(difference);
        this.displayTime(timer.element, timeComponents, difference);
    }

    calculateTimeComponents(difference) {
        const days = Math.floor(difference / (1000 * 60 * 60 * 24));
        const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((difference % (1000 * 60)) / 1000);

        return { days, hours, minutes, seconds };
    }

    displayTime(element, { days, hours, minutes, seconds }, totalDifference) {
        const display = element.querySelector('.timer-display');
        if (!display) return;

        // Determine display format based on time remaining
        let timeString = '';
        
        if (totalDifference > 24 * 60 * 60 * 1000) {
            // More than 24 hours - show days and hours
            timeString = `${days}d ${hours}h ${minutes}m`;
        } else if (totalDifference > 60 * 60 * 1000) {
            // More than 1 hour - show hours and minutes
            timeString = `${hours}h ${minutes}m ${seconds}s`;
        } else if (totalDifference > 10 * 60 * 1000) {
            // More than 10 minutes - show minutes and seconds
            timeString = `${minutes}m ${seconds}s`;
        } else {
            // Less than 10 minutes - show urgency with seconds
            timeString = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            this.addUrgencyEffects(element);
        }

        display.textContent = timeString;
        
        // Add urgency classes based on time remaining
        this.updateUrgencyClasses(element, totalDifference);
    }

    addUrgencyEffects(element) {
        element.classList.add('timer-urgent');
        
        // Add pulsing effect for last 10 minutes
        if (!element.classList.contains('timer-pulse')) {
            element.classList.add('timer-pulse');
        }
    }

    updateUrgencyClasses(element, totalDifference) {
        // Remove existing urgency classes
        element.classList.remove('timer-warning', 'timer-danger', 'timer-critical');
        
        if (totalDifference <= 10 * 60 * 1000) {
            // Last 10 minutes - critical
            element.classList.add('timer-critical');
        } else if (totalDifference <= 60 * 60 * 1000) {
            // Last hour - danger
            element.classList.add('timer-danger');
        } else if (totalDifference <= 6 * 60 * 60 * 1000) {
            // Last 6 hours - warning
            element.classList.add('timer-warning');
        }
    }

    handleExpiredTimer(timerId) {
        const timer = this.timers.get(timerId);
        if (!timer) return;

        // Clear interval
        if (timer.interval) {
            clearInterval(timer.interval);
        }

        // Update display
        const display = timer.element.querySelector('.timer-display');
        if (display) {
            display.textContent = 'Sale Ended';
        }

        // Add expired classes
        timer.element.classList.add('timer-expired');
        timer.element.classList.remove('timer-warning', 'timer-danger', 'timer-critical', 'timer-pulse');

        // Trigger custom event
        timer.element.dispatchEvent(new CustomEvent('saleExpired', {
            detail: { timerId, element: timer.element }
        }));

        // Remove from active timers
        this.timers.delete(timerId);
    }

    // Public methods for manual control
    pauseTimer(timerId) {
        const timer = this.timers.get(timerId);
        if (timer && timer.interval) {
            clearInterval(timer.interval);
            timer.interval = null;
        }
    }

    resumeTimer(timerId) {
        const timer = this.timers.get(timerId);
        if (timer && !timer.interval) {
            this.startTimer(timerId);
        }
    }

    destroyTimer(timerId) {
        const timer = this.timers.get(timerId);
        if (timer) {
            if (timer.interval) {
                clearInterval(timer.interval);
            }
            this.timers.delete(timerId);
        }
    }

    // Utility method to get timer info
    getTimerInfo(timerId) {
        const timer = this.timers.get(timerId);
        if (!timer) return null;

        const now = new Date();
        const difference = timer.endDate - now;
        
        return {
            timerId,
            endDate: timer.endDate,
            timeRemaining: difference,
            isExpired: difference <= 0,
            timeComponents: this.calculateTimeComponents(Math.max(0, difference))
        };
    }
}

// CSS for timer effects
const timerStyles = `
.timer-urgent {
    font-weight: bold !important;
}

.timer-pulse {
    animation: timerPulse 2s infinite;
}

.timer-warning {
    color: #ffc107 !important;
}

.timer-danger {
    color: #dc3545 !important;
}

.timer-critical {
    color: #dc3545 !important;
    animation: timerBlink 1s infinite;
}

.timer-expired {
    color: #6c757d !important;
    opacity: 0.7;
}

@keyframes timerPulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

@keyframes timerBlink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.5; }
}

/* Sale banner specific styles */
.sale-timer {
    transition: all 0.3s ease;
}

.sale-timer.timer-critical {
    background-color: #dc3545 !important;
    transform: scale(1.02);
}

.sale-banner-card:hover .timer-pulse {
    animation-duration: 1s;
}

/* Product card timer styles */
.product-card .sale-timer {
    font-size: 0.875rem;
}

.product-card .timer-critical {
    background-color: rgba(220, 53, 69, 0.1) !important;
    border: 1px solid #dc3545;
    border-radius: 4px;
    padding: 2px 6px;
}
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = timerStyles;
document.head.appendChild(styleSheet);

// Initialize global timer instance
window.saleTimer = new SaleTimer();

// Additional utility functions
window.SaleTimerUtils = {
    // Format time for display
    formatTimeRemaining: function(endDate) {
        const now = new Date();
        const difference = endDate - now;
        
        if (difference <= 0) return 'Expired';
        
        const components = window.saleTimer.calculateTimeComponents(difference);
        
        if (difference > 24 * 60 * 60 * 1000) {
            return `${components.days}d ${components.hours}h`;
        } else if (difference > 60 * 60 * 1000) {
            return `${components.hours}h ${components.minutes}m`;
        } else {
            return `${components.minutes}m ${components.seconds}s`;
        }
    },

    // Get all active timers
    getActiveTimers: function() {
        return Array.from(window.saleTimer.timers.keys()).map(id => 
            window.saleTimer.getTimerInfo(id)
        ).filter(timer => timer && !timer.isExpired);
    },

    // Check if any sales are ending soon (within 1 hour)
    getSalesEndingSoon: function() {
        return this.getActiveTimers().filter(timer => 
            timer.timeRemaining <= 60 * 60 * 1000
        );
    }
};

// Event listeners for sale expiration
document.addEventListener('saleExpired', function(event) {
    const { element } = event.detail;
    
    // Optional: Show notification or redirect
    console.log('Sale expired for element:', element);
    
    // You can add custom logic here, such as:
    // - Showing a notification
    // - Redirecting to another page
    // - Updating the UI
    // - Sending analytics events
});

// Debug helper (remove in production)
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    window.debugSaleTimers = function() {
        console.log('Active timers:', window.SaleTimerUtils.getActiveTimers());
        console.log('Sales ending soon:', window.SaleTimerUtils.getSalesEndingSoon());
    };
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sale Timer System Initialized');
});