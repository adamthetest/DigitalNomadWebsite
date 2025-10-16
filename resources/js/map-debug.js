/**
 * Map Debugging Script
 * 
 * Helps diagnose map loading issues by checking dependencies,
 * network connectivity, and providing fallback solutions.
 */

class MapDebugger {
    constructor() {
        this.debugInfo = {
            leafletLoaded: false,
            mapContainerExists: false,
            coordinatesValid: false,
            networkConnectivity: false,
            cspBlocked: false,
            errors: []
        };
    }

    /**
     * Check if Leaflet is properly loaded
     */
    checkLeaflet() {
        if (typeof L !== 'undefined') {
            this.debugInfo.leafletLoaded = true;
            console.log('✅ Leaflet is loaded');
        } else {
            this.debugInfo.leafletLoaded = false;
            this.debugInfo.errors.push('Leaflet library not loaded');
            console.error('❌ Leaflet library not found');
        }
        return this.debugInfo.leafletLoaded;
    }

    /**
     * Check if map container exists
     */
    checkMapContainer(containerId) {
        const container = document.getElementById(containerId);
        if (container) {
            this.debugInfo.mapContainerExists = true;
            console.log(`✅ Map container '${containerId}' exists`);
            
            // Check container dimensions
            const rect = container.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) {
                this.debugInfo.errors.push(`Map container '${containerId}' has no dimensions`);
                console.error(`❌ Map container '${containerId}' has no dimensions`);
            } else {
                console.log(`✅ Map container dimensions: ${rect.width}x${rect.height}`);
            }
        } else {
            this.debugInfo.mapContainerExists = false;
            this.debugInfo.errors.push(`Map container '${containerId}' not found`);
            console.error(`❌ Map container '${containerId}' not found`);
        }
        return this.debugInfo.mapContainerExists;
    }

    /**
     * Check if coordinates are valid
     */
    checkCoordinates(lat, lng) {
        if (lat && lng && !isNaN(lat) && !isNaN(lng) && 
            lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            this.debugInfo.coordinatesValid = true;
            console.log(`✅ Valid coordinates: ${lat}, ${lng}`);
        } else {
            this.debugInfo.coordinatesValid = false;
            this.debugInfo.errors.push(`Invalid coordinates: ${lat}, ${lng}`);
            console.error(`❌ Invalid coordinates: ${lat}, ${lng}`);
        }
        return this.debugInfo.coordinatesValid;
    }

    /**
     * Test network connectivity to OpenStreetMap
     */
    async checkNetworkConnectivity() {
        try {
            const response = await fetch('https://tile.openstreetmap.org/0/0/0.png', {
                method: 'HEAD',
                mode: 'no-cors'
            });
            this.debugInfo.networkConnectivity = true;
            console.log('✅ Network connectivity to OpenStreetMap OK');
        } catch (error) {
            this.debugInfo.networkConnectivity = false;
            this.debugInfo.errors.push('Network connectivity to OpenStreetMap failed');
            console.error('❌ Network connectivity to OpenStreetMap failed:', error);
        }
        return this.debugInfo.networkConnectivity;
    }

    /**
     * Check for CSP violations
     */
    checkCSPViolations() {
        // Listen for CSP violations
        document.addEventListener('securitypolicyviolation', (event) => {
            if (event.violatedDirective.includes('img-src') || 
                event.violatedDirective.includes('connect-src')) {
                this.debugInfo.cspBlocked = true;
                this.debugInfo.errors.push(`CSP violation: ${event.violatedDirective} blocked ${event.blockedURI}`);
                console.error('❌ CSP violation detected:', event);
            }
        });
    }

    /**
     * Initialize map with error handling
     */
    async initializeMap(containerId, lat, lng, zoom = 12) {
        console.log('🗺️ Initializing map...');
        
        // Run all checks
        this.checkLeaflet();
        this.checkMapContainer(containerId);
        this.checkCoordinates(lat, lng);
        await this.checkNetworkConnectivity();
        this.checkCSPViolations();

        // If any critical checks fail, show error
        if (!this.debugInfo.leafletLoaded || !this.debugInfo.mapContainerExists) {
            this.showMapError(containerId, 'Map dependencies not loaded properly');
            return null;
        }

        try {
            // Initialize map
            const map = L.map(containerId).setView([lat, lng], zoom);
            
            // Add tile layer with error handling
            const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
                errorTileUrl: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjU2IiBoZWlnaHQ9IjI1NiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk1hcCBUaWxlIEVycm9yPC90ZXh0Pjwvc3ZnPg=='
            });

            tileLayer.addTo(map);

            // Add error handling for tile loading
            tileLayer.on('tileerror', (error) => {
                console.error('❌ Tile loading error:', error);
                this.debugInfo.errors.push('Tile loading failed');
            });

            tileLayer.on('tileloadstart', () => {
                console.log('🔄 Tile loading started');
            });

            tileLayer.on('tileload', () => {
                console.log('✅ Tile loaded successfully');
            });

            console.log('✅ Map initialized successfully');
            return map;

        } catch (error) {
            console.error('❌ Map initialization failed:', error);
            this.debugInfo.errors.push(`Map initialization failed: ${error.message}`);
            this.showMapError(containerId, 'Failed to initialize map');
            return null;
        }
    }

    /**
     * Show map error message
     */
    showMapError(containerId, message) {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = `
                <div class="flex items-center justify-center h-full bg-gray-100 rounded-lg">
                    <div class="text-center p-8">
                        <div class="text-6xl mb-4">🗺️</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Map Unavailable</h3>
                        <p class="text-gray-600 mb-4">${message}</p>
                        <div class="text-sm text-gray-500">
                            <p>Debug Info:</p>
                            <ul class="mt-2 text-left">
                                <li>Leaflet Loaded: ${this.debugInfo.leafletLoaded ? '✅' : '❌'}</li>
                                <li>Container Exists: ${this.debugInfo.mapContainerExists ? '✅' : '❌'}</li>
                                <li>Network OK: ${this.debugInfo.networkConnectivity ? '✅' : '❌'}</li>
                                <li>CSP Blocked: ${this.debugInfo.cspBlocked ? '❌' : '✅'}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    /**
     * Get debug report
     */
    getDebugReport() {
        return {
            ...this.debugInfo,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
            url: window.location.href
        };
    }
}

// Export for use in other scripts
window.MapDebugger = MapDebugger;
