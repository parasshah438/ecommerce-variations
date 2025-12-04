/**
 * Service Worker for Geolocation Caching (PWA Support)
 * Enables offline location functionality
 */

const CACHE_NAME = 'geolocation-cache-v1';
const LOCATION_CACHE_KEY = 'user-location';

// Cache location data for offline use
self.addEventListener('message', event => {
    if (event.data.type === 'CACHE_LOCATION') {
        caches.open(CACHE_NAME).then(cache => {
            const locationData = JSON.stringify(event.data.location);
            cache.put(LOCATION_CACHE_KEY, new Response(locationData));
        });
    }
    
    if (event.data.type === 'GET_CACHED_LOCATION') {
        caches.open(CACHE_NAME).then(cache => {
            cache.match(LOCATION_CACHE_KEY).then(response => {
                if (response) {
                    response.text().then(data => {
                        event.ports[0].postMessage({
                            success: true,
                            location: JSON.parse(data)
                        });
                    });
                } else {
                    event.ports[0].postMessage({
                        success: false,
                        message: 'No cached location found'
                    });
                }
            });
        });
    }
});

// Intercept API calls for offline support
self.addEventListener('fetch', event => {
    if (event.request.url.includes('/api/location-details')) {
        event.respondWith(
            fetch(event.request).catch(() => {
                // Return cached location if network fails
                return caches.open(CACHE_NAME).then(cache => {
                    return cache.match(LOCATION_CACHE_KEY).then(response => {
                        if (response) {
                            return response;
                        }
                        return new Response(JSON.stringify({
                            success: false,
                            error: 'Offline - no cached location available'
                        }), {
                            status: 503,
                            headers: { 'Content-Type': 'application/json' }
                        });
                    });
                });
            })
        );
    }
});