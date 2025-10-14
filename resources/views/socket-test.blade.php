<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Socket.IO Test - Digital Nomad Website</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Socket.IO Real-time Test</h1>
            
            <!-- Connection Status -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Connection Status</h2>
                <div id="connection-status" class="flex items-center">
                    <div id="status-indicator" class="w-3 h-3 rounded-full bg-gray-400 mr-2"></div>
                    <span id="status-text">Connecting...</span>
                </div>
            </div>

            <!-- Notification Test -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Send Notifications</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <input type="text" id="message-input" placeholder="Enter your message..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex space-x-4">
                        <button id="broadcast-all" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                            Broadcast to All
                        </button>
                        <button id="broadcast-current" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">
                            Send to Current User
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notifications Display -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Received Notifications</h2>
                <div id="notifications" class="space-y-2 max-h-96 overflow-y-auto">
                    <p class="text-gray-500 italic">No notifications yet...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Socket.IO and our app.js -->
    @vite(['resources/js/app.js'])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusIndicator = document.getElementById('status-indicator');
            const statusText = document.getElementById('status-text');
            const messageInput = document.getElementById('message-input');
            const broadcastAllBtn = document.getElementById('broadcast-all');
            const broadcastCurrentBtn = document.getElementById('broadcast-current');
            const notificationsContainer = document.getElementById('notifications');

            // Wait for socket to be available
            function waitForSocket() {
                if (window.socket) {
                    setupSocketListeners();
                } else {
                    setTimeout(waitForSocket, 100);
                }
            }

            function setupSocketListeners() {
                const socket = window.socket;

                // Connection status listeners
                socket.on('connect', () => {
                    statusIndicator.className = 'w-3 h-3 rounded-full bg-green-400 mr-2';
                    statusText.textContent = 'Connected';
                });

                socket.on('disconnect', () => {
                    statusIndicator.className = 'w-3 h-3 rounded-full bg-red-400 mr-2';
                    statusText.textContent = 'Disconnected';
                });

                socket.on('connect_error', () => {
                    statusIndicator.className = 'w-3 h-3 rounded-full bg-yellow-400 mr-2';
                    statusText.textContent = 'Connection Error';
                });

                // Listen for notifications
                socket.on('notification', (data) => {
                    addNotification(data);
                });

                // Listen for private notifications
                socket.on('App\\Events\\UserNotification', (data) => {
                    addNotification(data);
                });
            }

            function addNotification(data) {
                const notificationDiv = document.createElement('div');
                notificationDiv.className = 'bg-blue-50 border-l-4 border-blue-400 p-3 rounded';
                
                const message = data.message || 'No message';
                const timestamp = data.timestamp ? new Date(data.timestamp).toLocaleTimeString() : new Date().toLocaleTimeString();
                
                notificationDiv.innerHTML = `
                    <div class="flex justify-between items-start">
                        <p class="text-gray-800">${message}</p>
                        <span class="text-xs text-gray-500 ml-2">${timestamp}</span>
                    </div>
                `;

                // Remove "No notifications yet" message if it exists
                const noNotificationsMsg = notificationsContainer.querySelector('.text-gray-500.italic');
                if (noNotificationsMsg) {
                    noNotificationsMsg.remove();
                }

                notificationsContainer.insertBefore(notificationDiv, notificationsContainer.firstChild);
            }

            // Button event listeners
            broadcastAllBtn.addEventListener('click', function() {
                const message = messageInput.value || 'Hello from Socket.IO!';
                sendNotification('/notifications/broadcast-all', { message: message });
            });

            broadcastCurrentBtn.addEventListener('click', function() {
                const message = messageInput.value || 'Personal message!';
                sendNotification('/notifications/broadcast-current', { message: message });
            });

            function sendNotification(url, data) {
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    console.log('Notification sent:', result);
                })
                .catch(error => {
                    console.error('Error sending notification:', error);
                });
            }

            // Initialize
            waitForSocket();
        });
    </script>
</body>
</html>
