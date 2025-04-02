const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const Redis = require('ioredis');
const axios = require('axios');
const cron = require('node-cron');

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
  host: 'localhost', // Đây sẽ là tên của container Redis trong Docker Compose
  port: 6379,    // Cổng mặc định của Redis
});

// Lắng nghe kết nối từ client
io.on('connection', (socket) => {
  console.log('User connected');

  // Lưu thông tin người dùng khi kết nối
  let user_id, user_name;

  // Lắng nghe sự kiện 'user_info' từ client để lưu thông tin người dùng
  socket.on('user_info', (data) => {
    user_id = data.user_id;
    user_name = data.user_name;
    console.log('User info received:', user_id, user_name);
  });

  redis.lrange('chat:messages', 0, -1, (err, messages) => {
    if (err) {
      console.error('Error retrieving messages from Redis:', err);
      return;
    }
    // Gửi tất cả các tin nhắn cũ cho người dùng khi kết nối
    messages.reverse().forEach((message) => {
      socket.emit('message', JSON.parse(message));  // Gửi tin nhắn đã được lưu
    });
  });

  // Lắng nghe sự kiện 'message' từ client
  socket.on('message', (data) => {
    console.log('Message received:', data);

    // Truyền tin nhắn tới tất cả người dùng trong room "default"
    io.to('default').emit('message', { message: data, user_id: user_id, user_name: user_name });

    // Lưu tin nhắn vào Redis (tùy chọn)
    const messageData = { message: data, user_id: user_id, user_name: user_name, timestamp: Date.now() };
    redis.lpush('chat:messages', JSON.stringify(messageData)); // Lưu vào Redis với key "chat:messages"
  });

  // Tham gia vào room "default"
  socket.join('default');

  // Lắng nghe khi người dùng ngắt kết nối
  socket.on('disconnect', () => {
    console.log('User disconnected');
  });
});
cron.schedule('* * * * *', () => {
  console.log('Checking for expired messages...');
  redis.lrange('chat:messages', 0, -1, (err, messages) => {
    if (err) {
      console.error('Error retrieving messages from Redis:', err);
      return;
    }
    messages.forEach((message) => {
      const parsedMessage = JSON.parse(message);
      if (Date.now() - parsedMessage.timestamp > 60 * 60 * 1000) {
        // Nếu tin nhắn đã quá 10 phút, xóa tin nhắn khỏi Redis
        redis.lrem('chat:messages', 0, message, (err, response) => {
          if (err) {
            console.error('Error deleting expired message:', err);
          } else {
            console.log('Expired message deleted');
          }
        });
      }
    });
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
