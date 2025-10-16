# Map Troubleshooting Guide

A comprehensive guide for diagnosing and fixing map loading issues in the Digital Nomad Website.

## 🗺️ Map Implementation Overview

The application uses **Leaflet.js** with **OpenStreetMap** tiles to display interactive maps on:

- **Home Page**: Global map with featured cities
- **Cities Index**: Map showing all cities with markers
- **City Detail**: Individual city location map
- **Coworking Spaces**: Location maps for individual spaces

## 🔧 Technical Stack

- **Leaflet.js v1.9.4**: Interactive map library
- **OpenStreetMap**: Free, open-source map tiles
- **MapDebugger**: Custom debugging class for troubleshooting

## 🚨 Common Issues and Solutions

### 1. Maps Not Loading (Blank/Empty)

**Symptoms:**
- Map container appears but shows no tiles
- Console errors about tile loading
- "Map Unavailable" error message

**Causes & Solutions:**

#### A. Content Security Policy (CSP) Blocking
**Problem**: CSP headers blocking OpenStreetMap tiles
**Solution**: Updated SecurityHeaders middleware to allow:
```php
"img-src 'self' data: https: https://*.tile.openstreetmap.org https://*.tile.osm.org; ".
"connect-src 'self' ws: wss: https://*.tile.openstreetmap.org https://*.tile.osm.org; "
```

#### B. Missing Coordinates
**Problem**: City or coworking space has no latitude/longitude
**Solution**: Check database for missing coordinates:
```sql
SELECT id, name, latitude, longitude FROM cities WHERE latitude IS NULL OR longitude IS NULL;
```

#### C. Network Connectivity
**Problem**: Cannot reach OpenStreetMap servers
**Solution**: Check network connectivity and firewall settings

### 2. JavaScript Errors

**Symptoms:**
- Console shows "L is not defined" errors
- Map initialization fails
- Leaflet library not loading

**Solutions:**

#### A. Leaflet Library Not Loading
```javascript
// Check if Leaflet is loaded
if (typeof L === 'undefined') {
    console.error('Leaflet library not loaded');
    // Reload page or check CDN
}
```

#### B. Map Container Not Found
```javascript
// Check if container exists
const container = document.getElementById('mapId');
if (!container) {
    console.error('Map container not found');
}
```

### 3. Tile Loading Issues

**Symptoms:**
- Map loads but tiles appear broken
- "Map Tile Error" placeholder images
- Slow tile loading

**Solutions:**

#### A. Add Error Tile Fallback
```javascript
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    errorTileUrl: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjU2IiBoZWlnaHQ9IjI1NiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk1hcCBUaWxlIEVycm9yPC90ZXh0Pjwvc3ZnPg=='
});
```

#### B. Alternative Tile Providers
```javascript
// CartoDB tiles as fallback
L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '© OpenStreetMap contributors © CARTO'
});
```

## 🔍 Debugging Tools

### MapDebugger Class

The application includes a comprehensive debugging class:

```javascript
const debugger = new MapDebugger();
const map = await debugger.initializeMap('mapId', lat, lng, zoom);
```

**Debug Checks:**
- ✅ Leaflet library loaded
- ✅ Map container exists and has dimensions
- ✅ Coordinates are valid
- ✅ Network connectivity to OpenStreetMap
- ✅ CSP violations detected

### Console Debugging

Enable detailed logging:
```javascript
// Check Leaflet version
console.log('Leaflet version:', L.version);

// Check map container
console.log('Map container:', document.getElementById('mapId'));

// Check coordinates
console.log('Coordinates:', lat, lng);

// Monitor tile loading
tileLayer.on('tileloadstart', () => console.log('Tile loading...'));
tileLayer.on('tileload', () => console.log('Tile loaded'));
tileLayer.on('tileerror', (e) => console.error('Tile error:', e));
```

## 🛠️ Manual Testing

### Test Map Loading

1. **Open Browser Developer Tools**
2. **Navigate to a page with maps**
3. **Check Console for errors**
4. **Verify Network tab for tile requests**

### Test Specific Scenarios

```bash
# Test home page map
curl -s http://localhost:8000 | grep -i "homeMap"

# Test cities page map
curl -s http://localhost:8000/cities | grep -i "citiesMap"

# Test city detail map
curl -s http://localhost:8000/cities/1 | grep -i "cityMap"
```

## 🔧 Configuration

### Environment Variables

```env
# Map-related settings
MAP_TILE_PROVIDER=openstreetmap
MAP_DEFAULT_ZOOM=12
MAP_MAX_ZOOM=19
```

### Security Headers

Ensure CSP allows map tiles:
```php
// In SecurityHeaders middleware
"img-src 'self' data: https: https://*.tile.openstreetmap.org; ".
"connect-src 'self' ws: wss: https://*.tile.openstreetmap.org; "
```

## 🚀 Performance Optimization

### Tile Caching
```javascript
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    maxNativeZoom: 18,
    subdomains: ['a', 'b', 'c'] // Load balance across subdomains
});
```

### Lazy Loading
```javascript
// Only initialize map when container is visible
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            initializeMap();
            observer.unobserve(entry.target);
        }
    });
});
observer.observe(document.getElementById('mapId'));
```

## 🔄 Fallback Solutions

### 1. Static Map Images
```php
// Generate static map as fallback
$staticMapUrl = "https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/{$lng},{$lat},{$zoom}/400x300?access_token={$token}";
```

### 2. Alternative Map Providers
```javascript
// Google Maps fallback
if (typeof L === 'undefined') {
    // Load Google Maps instead
    loadGoogleMaps();
}
```

### 3. No-JavaScript Fallback
```html
<noscript>
    <div class="map-fallback">
        <p>📍 Location: {{ $city->name }}, {{ $city->country->name }}</p>
        <p>Coordinates: {{ $city->latitude }}, {{ $city->longitude }}</p>
    </div>
</noscript>
```

## 📊 Monitoring

### Error Tracking
```javascript
// Track map errors
window.addEventListener('error', (event) => {
    if (event.filename.includes('leaflet') || event.message.includes('map')) {
        // Send to error tracking service
        console.error('Map error:', event);
    }
});
```

### Performance Monitoring
```javascript
// Measure map load time
const startTime = performance.now();
// ... map initialization
const loadTime = performance.now() - startTime;
console.log(`Map loaded in ${loadTime}ms`);
```

## 🆘 Emergency Fixes

### Quick Map Disable
```javascript
// Temporarily disable maps
if (window.location.search.includes('nomaps')) {
    document.querySelectorAll('[id$="Map"]').forEach(el => {
        el.innerHTML = '<div class="text-center p-8 text-gray-500">Maps temporarily disabled</div>';
    });
}
```

### Force Reload Maps
```javascript
// Reload all maps
function reloadAllMaps() {
    document.querySelectorAll('.leaflet-container').forEach(container => {
        const mapId = container.id;
        const map = window[mapId + '_instance'];
        if (map) {
            map.invalidateSize();
            map.eachLayer(layer => {
                if (layer instanceof L.TileLayer) {
                    layer.redraw();
                }
            });
        }
    });
}
```

## 📞 Support

### Common Commands

```bash
# Clear browser cache
# Ctrl+Shift+R (Chrome/Firefox)

# Test map connectivity
curl -I https://tile.openstreetmap.org/0/0/0.png

# Check CSP headers
curl -I http://localhost:8000 | grep -i "content-security-policy"

# View map debug info
# Open browser console and run:
console.log(window.MapDebugger);
```

### Getting Help

1. **Check Browser Console** for JavaScript errors
2. **Verify Network Tab** for failed tile requests
3. **Test with Different Browsers** to isolate issues
4. **Check CSP Headers** for blocking policies
5. **Verify Coordinates** in database

---

**Map functionality is critical for user experience. Use this guide to quickly diagnose and resolve map loading issues.** 🗺️
