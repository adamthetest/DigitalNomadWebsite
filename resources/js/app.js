import './bootstrap';

// Socket.IO client configuration
import { io } from 'socket.io-client';

// Initialize Socket.IO connection
const socket = io(window.location.origin, {
    transports: ['websocket', 'polling'],
    autoConnect: true,
    reconnection: true,
    reconnectionDelay: 1000,
    reconnectionAttempts: 5,
    maxReconnectionAttempts: 5
});

// Socket.IO event handlers
socket.on('connect', () => {
    console.log('Connected to server via Socket.IO');
});

socket.on('disconnect', () => {
    console.log('Disconnected from server');
});

socket.on('connect_error', (error) => {
    console.log('Connection error:', error);
});

// Make socket available globally for use in other components
window.socket = socket;
