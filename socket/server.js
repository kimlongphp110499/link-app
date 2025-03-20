const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const Redis = require('ioredis');
const axios = require('axios'); 
// Tạo một ứng dụng Express
const app = express();
const server = http.createServer(app);

// Cấu hình CORS cho socket.io
const io = socketIo(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
    allowedHeaders: ["my-custom-header"],
    credentials: true
  }
});


// Kết nối Redis
const redis = new Redis({
    host: 'redis', // Đây sẽ là tên của container Redis trong Docker Compose
    port: 6379,    // Cổng mặc định của Redis
});

// Lắng nghe kết nối từ client
io.on('connection', (socket) => {
    console.log('User connected');

    redis.lrange('chat:messages', 0, -1, (err, messages) => {
      if (err) {
          console.error('Error retrieving messages from Redis:', err);
          return;
      }
      // Gửi tất cả các tin nhắn cũ cho người dùng khi kết nối
      messages.reverse().forEach((message) => {
        console.log( JSON.parse(message))
          socket.emit('message', JSON.parse(message));  // Gửi tin nhắn đã được lưu
      });
  });
    // Lắng nghe sự kiện 'message' từ client
    socket.on('message', (data, user_id) => {
        console.log('Message received:', data, user_id);
      
        axios.post('http://link.local/api/messages', {
          message: data,
          user_id: user_id
        })
        .then((response) => {
          console.log('Message saved to queue:', response.data);
        })
        .catch((error) => {
          console.error('Error saving message:', error);
        });
        // Truyền tin nhắn tới tất cả người dùng trong room "default"
        io.to('default').emit('message', data, user_id);
        
        // Lưu tin nhắn vào Redis (tùy chọn)
       redis.lpush('chat:messages', JSON.stringify(data)); // Lưu vào Redis với key "chat:messages"
    });

    // Tham gia vào room "default"
    socket.join('default');

    // Lắng nghe khi người dùng ngắt kết nối
    socket.on('disconnect', () => {
        console.log('User disconnected');
    });
});

// Serve các tài nguyên tĩnh (tùy chọn)
app.get('/', (req, res) => {
    res.send('Socket.IO server is running');
});

// Khởi chạy server
server.listen(4000, () => {
    console.log('Server listening on port 4000');
});
