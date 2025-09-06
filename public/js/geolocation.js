/**
 * Geolocation Utility Class
 * Provides comprehensive location detection and management functionality
 * Similar to BigBasket's location system
 */
class GeolocationManager {
    constructor(options = {}) {
        this.options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000,
            fallbackToIP: true,
            autoDetect: false,
            ...options
        };
        
        this.currentLocation = null;
        this.callbacks = {
            onLocationDetected: [],
            onLocationError: [],
            onLocationChanged: []
        };
        
        // Initialize CSRF token
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (this.options.autoDetect) {
            this.detectLocation();
        }
    }
    
    /**
     * Add event listener
     */
    on(event, callback) {
        if (this.callbacks[event]) {
            this.callbacks[event].push(callback);
        }
    }
    
    /**
     * Trigger event callbacks
     */
    trigger(event, data) {
        if (this.callbacks[event]) {
            this.callbacks[event].forEach(callback => callback(data));
        }
    }
    
    /**
     * Detect user's current location using GPS
     */
    detectLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                const error = new Error('Geolocation is not supported by this browser');
                this.trigger('onLocationError', error);
                
                if (this.options.fallbackToIP) {
                    this.getLocationFromIP().then(resolve).catch(reject);
                } else {
                    reject(error);
                }
                return;
            }
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const coords = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    };
                    
                    this.getLocationDetails(coords.latitude, coords.longitude)
                        .then((locationData) => {
                            this.currentLocation = { ...locationData, ...coords };
                            this.trigger('onLocationDetected', this.currentLocation);
                            resolve(this.currentLocation);
                        })
                        .catch(reject);
                },
                (error) => {
                    this.trigger('onLocationError', error);
                    
                    if (this.options.fallbackToIP) {
                        this.getLocationFromIP().then(resolve).catch(reject);
                    } else {
                        reject(error);
                    }
                },
                {
                    enableHighAccuracy: this.options.enableHighAccuracy,
                    timeout: this.options.timeout,
                    maximumAge: this.options.maximumAge
                }
            );
        });
    }
    
    /**
     * Get location details from coordinates
     */
    async getLocationDetails(latitude, longitude) {
        try {
            const response = await fetch('/api/location-details', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ latitude, longitude })
            });
            
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.error || 'Failed to get location details');
            }
        } catch (error) {
            console.error('Error getting location details:', error);
            throw error;
        }
    }
    
    /**
     * Get location from IP address
     */
    async getLocationFromIP() {
        try {
            const response = await fetch('/api/location-from-ip');
            const data = await response.json();
            
            if (data.success) {
                this.currentLocation = data.data;
                this.trigger('onLocationDetected', this.currentLocation);
                return this.currentLocation;
            } else {
                throw new Error(data.error || 'Failed to get location from IP');
            }
        } catch (error) {
            console.error('Error getting location from IP:', error);
            this.trigger('onLocationError', error);
            throw error;
        }
    }
    
    /**
     * Search locations by query
     */
    async searchLocations(query) {
        try {
            const response = await fetch(`/api/search-locations?query=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.error || 'Failed to search locations');
            }
        } catch (error) {
            console.error('Error searching locations:', error);
            throw error;
        }
    }
    
    /**
     * Get pincode details
     */
    async getPincodeDetails(pincode) {
        try {
            const response = await fetch(`/api/pincode-details?pincode=${pincode}`);
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.error || 'Invalid pincode');
            }
        } catch (error) {
            console.error('Error getting pincode details:', error);
            throw error;
        }
    }
    
    /**
     * Set current location manually
     */
    setLocation(locationData) {
        const oldLocation = this.currentLocation;
        this.currentLocation = locationData;
        this.trigger('onLocationChanged', { old: oldLocation, new: this.currentLocation });
    }
    
    /**
     * Get current location
     */
    getCurrentLocation() {
        return this.currentLocation;
    }
    
    /**
     * Check if location is available
     */
    hasLocation() {
        return this.currentLocation !== null;
    }
    
    /**
     * Clear current location
     */
    clearLocation() {
        this.currentLocation = null;
    }
    
    /**
     * Auto-fill form fields with location data
     */
    fillForm(formSelector, fieldMapping = {}) {
        if (!this.currentLocation) {
            console.warn('No location data available to fill form');
            return;
        }
        
        const form = document.querySelector(formSelector);
        if (!form) {
            console.warn(`Form not found: ${formSelector}`);
            return;
        }
        
        const defaultMapping = {
            area: 'area',
            city: 'city',
            state: 'state',
            pincode: 'pincode',
            country: 'country',
            address: 'formatted_address',
            latitude: 'latitude',
            longitude: 'longitude'
        };
        
        const mapping = { ...defaultMapping, ...fieldMapping };
        
        Object.keys(mapping).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"], #${fieldName}`);
            if (field && this.currentLocation[mapping[fieldName]]) {
                field.value = this.currentLocation[mapping[fieldName]];
                
                // Trigger change event
                field.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }
    
    /**
     * Create location picker widget
     */
    createLocationPicker(container, options = {}) {
        const config = {
            showDetectButton: true,
            showSearchBox: true,
            showPincodeInput: true,
            placeholder: 'Search for area, city, or landmark...',
            pincodePlaceholder: 'Enter Pincode',
            detectButtonText: 'Detect My Location',
            ...options
        };
        
        const containerEl = typeof container === 'string' ? document.querySelector(container) : container;
        
        if (!containerEl) {
            console.error('Container not found for location picker');
            return;
        }
        
        const html = `
            <div class="location-picker">
                ${config.showDetectButton ? `
                    <div class="location-detect mb-3">
                        <button type="button" class="btn btn-primary location-detect-btn">
                            <i class="bi bi-geo-alt me-2"></i>
                            ${config.detectButtonText}
                        </button>
                        <div class="location-loading" style="display: none;">
                            <div class="spinner-border spinner-border-sm me-2"></div>
                            Detecting location...
                        </div>
                    </div>
                ` : ''}
                
                <div class="row">
                    ${config.showSearchBox ? `
                        <div class="col-md-8 mb-3">
                            <div class="location-search-container position-relative">
                                <input type="text" class="form-control location-search-input" 
                                       placeholder="${config.placeholder}" autocomplete="off">
                                <div class="location-search-results position-absolute w-100 bg-white border rounded shadow-sm" 
                                     style="display: none; z-index: 1000; max-height: 300px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    ` : ''}
                    
                    ${config.showPincodeInput ? `
                        <div class="col-md-4 mb-3">
                            <input type="text" class="form-control location-pincode-input" 
                                   placeholder="${config.pincodePlaceholder}" maxlength="6" pattern="[0-9]{6}">
                        </div>
                    ` : ''}
                </div>
                
                <div class="location-info" style="display: none;">
                    <div class="alert alert-success">
                        <strong>Location Detected:</strong>
                        <span class="location-display"></span>
                    </div>
                </div>
            </div>
        `;
        
        containerEl.innerHTML = html;
        
        // Bind events
        this.bindLocationPickerEvents(containerEl);
        
        return containerEl;
    }
    
    /**
     * Bind events for location picker
     */
    bindLocationPickerEvents(container) {
        const detectBtn = container.querySelector('.location-detect-btn');
        const loading = container.querySelector('.location-loading');
        const searchInput = container.querySelector('.location-search-input');
        const searchResults = container.querySelector('.location-search-results');
        const pincodeInput = container.querySelector('.location-pincode-input');
        const locationInfo = container.querySelector('.location-info');
        const locationDisplay = container.querySelector('.location-display');
        
        let searchTimeout;
        
        // Detect location button
        if (detectBtn) {
            detectBtn.addEventListener('click', () => {
                detectBtn.style.display = 'none';
                loading.style.display = 'block';
                
                this.detectLocation()
                    .then((location) => {
                        this.showLocationInfo(locationInfo, locationDisplay, location);
                    })
                    .catch((error) => {
                        console.error('Location detection failed:', error);
                        alert('Failed to detect location. Please try manual search.');
                    })
                    .finally(() => {
                        detectBtn.style.display = 'block';
                        loading.style.display = 'none';
                    });
            });
        }
        
        // Search input
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length >= 3) {
                    searchTimeout = setTimeout(() => {
                        this.searchLocations(query)
                            .then((results) => {
                                this.showSearchResults(searchResults, results, locationInfo, locationDisplay);
                            })
                            .catch((error) => {
                                console.error('Search failed:', error);
                                searchResults.style.display = 'none';
                            });
                    }, 500);
                } else {
                    searchResults.style.display = 'none';
                }
            });
            
            // Hide results when clicking outside
            document.addEventListener('click', (e) => {
                if (!container.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
        }
        
        // Pincode input
        if (pincodeInput) {
            pincodeInput.addEventListener('input', (e) => {
                // Only allow numbers
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                
                const pincode = e.target.value.trim();
                
                if (pincode.length === 6) {
                    this.getPincodeDetails(pincode)
                        .then((location) => {
                            this.setLocation(location);
                            this.showLocationInfo(locationInfo, locationDisplay, location);
                        })
                        .catch((error) => {
                            console.error('Pincode lookup failed:', error);
                            alert('Invalid pincode or lookup failed');
                        });
                }
            });
        }
    }
    
    /**
     * Show search results
     */
    showSearchResults(container, results, locationInfo, locationDisplay) {
        if (!results || results.length === 0) {
            container.style.display = 'none';
            return;
        }
        
        container.innerHTML = results.map(result => `
            <div class="location-search-result p-3 border-bottom cursor-pointer" data-result='${JSON.stringify(result)}'>
                <div class="fw-bold">${result.display_name}</div>
                <small class="text-muted">
                    ${result.city ? result.city + ', ' : ''}
                    ${result.state ? result.state + ', ' : ''}
                    ${result.country}
                    ${result.pincode ? ' - ' + result.pincode : ''}
                </small>
            </div>
        `).join('');
        
        container.style.display = 'block';
        
        // Bind click events
        container.querySelectorAll('.location-search-result').forEach(item => {
            item.addEventListener('click', () => {
                const result = JSON.parse(item.dataset.result);
                
                if (result.latitude && result.longitude) {
                    this.getLocationDetails(result.latitude, result.longitude)
                        .then((location) => {
                            this.setLocation(location);
                            this.showLocationInfo(locationInfo, locationDisplay, location);
                        })
                        .catch((error) => {
                            console.error('Failed to get detailed location:', error);
                            this.setLocation(result);
                            this.showLocationInfo(locationInfo, locationDisplay, result);
                        });
                } else {
                    this.setLocation(result);
                    this.showLocationInfo(locationInfo, locationDisplay, result);
                }
                
                container.style.display = 'none';
            });
        });
    }
    
    /**
     * Show location information
     */
    showLocationInfo(container, display, location) {
        if (display) {
            display.textContent = location.formatted_address || 
                `${location.area || ''} ${location.city || ''} ${location.state || ''} ${location.pincode || ''}`.trim();
        }
        
        if (container) {
            container.style.display = 'block';
        }
    }
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GeolocationManager;
}

// Global instance for easy access
window.GeolocationManager = GeolocationManager;