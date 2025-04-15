const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const Redis = require('ioredis');
const { v4: uuidv4 } = require('uuid'); // Thư viện để tạo UUID (npm install uuid)
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
  port: 6379,        // Cổng mặc định của Redis
});

// Lắng nghe kết nối từ client
io.on('connection', (socket) => {
  console.log('User connected');

  // Lưu thông tin người dùng khi kết nối
  let user_id, user_name, avatar;

  // Lắng nghe sự kiện 'user_info' từ client để lưu thông tin người dùng
  socket.on('user_info', (data) => {
    user_id = data.user_id;
    user_name = data.user_name;
    avatar = data.avatar;
    reply_id = data.reply_id;
    reply_to = data.reply_to;
    console.log('User info received:', user_id, user_name);
  });

  // Lấy tất cả tin nhắn cũ từ Redis khi người dùng kết nối
  redis.lrange('chat:messages', 0, -1, (err, messages) => {
    if (err) {
      console.error('Error retrieving messages from Redis:', err);
      return;
    }
    messages.reverse().forEach((message) => {
      socket.emit('message', JSON.parse(message));  // Gửi tin nhắn đã được lưu
    });
  });

  // Lắng nghe sự kiện 'message' từ client
  socket.on('message', (data) => {
    console.log('Message received:', data);
  
    const messageId = uuidv4();
  
    const messageData = {
      id: messageId,
      message: data.message,
      user_id: user_id,
      user_name: user_name,
      avatar: avatar,
      timestamp: Date.now(),
      reply_id: data.reply_id || null,
      reply_to: data.reply_to || null,
    };
  
    if (messageData.reply_to) {
      redis.lrange('chat:messages', 0, -1, (err, messages) => {
        if (err) {
          console.error('Error retrieving messages from Redis:', err);
          return;
        }
  
        const repliedMessage = messages
          .map(msg => JSON.parse(msg))
          .find(msg => msg.id === messageData.reply_to);
  
        if (repliedMessage) {
          messageData.replied_message = {
            id: repliedMessage.id,
            message: repliedMessage.message,
            user_name: repliedMessage.user_name,
            avatar: repliedMessage.avatar,
          };
        }
  
        io.to('default').emit('message', messageData);
        redis.lpush('chat:messages', JSON.stringify(messageData));
      });
    } else {
      io.to('default').emit('message', messageData);
      redis.lpush('chat:messages', JSON.stringify(messageData));
    }
  });
  
  // Lắng nghe sự kiện 'send_name' từ client
  socket.on('send_name', (name) => {
    console.log(`Name received: ${name}`);
  
    const notificationId = uuidv4();
    const notification = {
      id: notificationId,
      name: name,
      user_id: user_id,
      user_name: user_name,
      avatar: avatar,
      timestamp: Date.now(),
    };
  
    io.emit('show_name', notification);
  
    setTimeout(() => {
      io.emit('remove_name', { id: notificationId });
      console.log(`Notification with ID ${notificationId} removed`);
    }, 2000);
  });

  // Tham gia vào room "default"
  socket.join('default');

  // Lắng nghe khi người dùng ngắt kết nối
  socket.on('disconnect', () => {
    console.log('User disconnected');
  });
});

// Cron job để xóa tin nhắn cũ
cron.schedule('* * * * *', () => {
  console.log('Checking for expired messages...');
  redis.lrange('chat:messages', 0, -1, (err, messages) => {
    if (err) {
      console.error('Error retrieving messages from Redis:', err);
      return;
    }
    messages.forEach((message) => {
      const parsedMessage = JSON.parse(message);
      if (Date.now() - parsedMessage.timestamp > 120 * 60 * 1000) {
        // Nếu tin nhắn đã quá 120 phút, xóa tin nhắn khỏi Redis
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