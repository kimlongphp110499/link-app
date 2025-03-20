const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
  cors: {
    origin: '*',  // Allow all origins for testing
    methods: ['GET', 'POST']
  }
});

let room = 'chat_room';

// Cấu hình kết nối socket.io
io.on('connection', (socket) => {
    console.log('a user connected');

    socket.join(room);

    socket.on('disconnect', () => {
        console.log('user disconnected');
    });

    socket.on('chat_message', (msg) => {
        // Broadcast message to room
        io.to(room).emit('chat_message', msg);
        
        // Lưu tin nhắn vào Laravel bằng API hoặc Queue Job
        fetch('http://link.local/api/save_message', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: msg })
        });
    });
});

// Lắng nghe tại port 4000
server.listen(4000, () => {
    console.log('Socket.IO server running on port 4000');
});
