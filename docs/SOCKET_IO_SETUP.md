# Socket.IO Real-time Setup - Replacing Pusher.js

This document explains the new Socket.IO setup that replaces Pusher.js for real-time functionality in the Digital Nomad Website.

## What Was Changed

### 1. **Removed Pusher.js Dependency**
- Pusher.js was previously bundled with Filament but not actively used
- Replaced with Socket.IO client for better control and flexibility

### 2. **Added Socket.IO Client**
- Installed `socket.io-client` package
- Configured in `resources/js/app.js` with connection settings
- Made globally available as `window.socket`

### 3. **Added Redis Broadcasting Support**
- Installed `predis/predis` for Redis connectivity
- Created `config/broadcasting.php` with Redis driver configuration
- Redis is now the default broadcasting driver

### 4. **Created Real-time Event System**
- `App\Events\UserNotification` - Event class for broadcasting notifications
- `App\Http\Controllers\NotificationController` - Controller for sending notifications
- Routes for testing real-time functionality

## Configuration

### Broadcasting Configuration
The broadcasting is configured in `config/broadcasting.php`:

```php
'default' => env('BROADCAST_CONNECTION', 'redis'),
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
    ],
    // ... other drivers
],
```

### Environment Variables
Add these to your `.env` file:

```env
BROADCAST_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Usage Examples

### 1. **Broadcasting Events**
```php
use App\Events\UserNotification;

// Broadcast to all users
broadcast(new UserNotification('Hello everyone!'));

// Broadcast to specific user
broadcast(new UserNotification('Personal message!', $userId));
```

### 2. **Frontend Socket.IO Usage**
```javascript
// Listen for notifications
window.socket.on('notification', (data) => {
    console.log('Received notification:', data.message);
});

// Listen for private notifications
window.socket.on('App\\Events\\UserNotification', (data) => {
    console.log('Private notification:', data.message);
});
```

### 3. **Testing the Setup**
Visit `/socket-test` (requires authentication) to test the real-time functionality:
- Connection status indicator
- Send notifications to all users or current user
- View received notifications in real-time

## Benefits of This Setup

1. **Cost-effective**: No external service fees like Pusher
2. **Self-hosted**: Complete control over your data
3. **Scalable**: Redis can handle high loads
4. **Flexible**: Easy to customize and extend
5. **Laravel Native**: Built-in broadcasting support

## Next Steps

1. **Install Redis**: Make sure Redis is running on your server
2. **Configure Environment**: Update your `.env` file with Redis settings
3. **Test Functionality**: Visit `/socket-test` to verify everything works
4. **Implement Features**: Use the event system for real-time features like:
   - Live notifications
   - Real-time job updates
   - Live chat
   - Real-time cost calculator updates

## Troubleshooting

### Common Issues

1. **Redis Connection Error**: Ensure Redis is installed and running
2. **Socket Connection Failed**: Check if WebSocket connections are allowed
3. **Events Not Broadcasting**: Verify broadcasting configuration and Redis connection

### Debug Commands

```bash
# Check Redis connection
redis-cli ping

# Test broadcasting
php artisan tinker
>>> broadcast(new App\Events\UserNotification('Test message'));
```

## Migration from Pusher

If you were previously using Pusher.js:

1. **Remove Pusher Configuration**: Remove any Pusher environment variables
2. **Update Frontend Code**: Replace Pusher client code with Socket.IO
3. **Test Broadcasting**: Ensure events are properly broadcasted
4. **Update Documentation**: Update any documentation referencing Pusher

This setup provides a robust, cost-effective alternative to Pusher.js while maintaining all the real-time functionality your application needs.
