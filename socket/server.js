const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const Redis = require('ioredis');
const { v4: uuidv4 } = require('uuid');
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
  host: 'localhost',
  port: 6379,
});

// Redis subscriber cho m4u_database_points-update
const subscriber = new Redis({ host: 'localhost', port: 6379 });
subscriber.on('connect', () => {
  console.log('Redis subscriber connected');
});
subscriber.on('error', (err) => {
  console.error('Redis subscriber error:', err);
});
subscriber.subscribe('m4u_database_points-update', (err) => {
  if (err) {
    console.error('Failed to subscribe:', err);
  } else {
    console.log('Subscribed to m4u_database_points-update');
  }
});

subscriber.on('message', (channel, message) => {
  console.log('Received message on channel:', channel, message);
  let data;
  data = JSON.parse(message);
  if (channel === 'm4u_database_points-update') {
    try {
      console.log('Parsed points update:', data);
      // Emit trực tiếp points_updated tới room user:${user_id}
      io.to(`user:${data.user_id}`).emit('points_updated', {
        user_id: data.user_id,
        points: parseInt(data.points), // Chuyển chuỗi thành số
        timestamp: Date.now()
      });
      console.log(`Emitted points_updated to user:${data.user_id}`, { user_id: data.user_id, points: data.points });
    } catch (e) {
      console.error('Failed to parse Redis message:', e, message);
    }
  }
});

subscriber.subscribe('m4u_database_honors', (err) => {
  if (err) {
    console.error('Failed to subscribe:', err);
  } else {
    console.log('Subscribed to m4u_database_honors');
  }
});
subscriber.on('message', (channel, message) => {
  console.log('Received message on channel:', channel, message);
  let data;
  data = JSON.parse(message);
  if (channel === 'm4u_database_honors') {
    try {
        console.log('Parsed honor update ');
        if (data.event === 'honor.updated') {
            io.emit('honor.updated', data.data);
            console.log('Emitted honor.updated:', data.data);
        }
    } catch (e) {
        console.error('Failed to parse Redis message for honors:', e, message);
    }
 }
});

// Lắng nghe kết nối từ client
io.on('connection', (socket) => {
  console.log('User connected:', socket.id);

  // Lưu thông tin người dùng khi kết nối
  let user_id, user_name, avatar;

  // Lắng nghe sự kiện 'user_info' từ client để lưu thông tin người dùng
  socket.on('user_info', (data) => {
    console.log('Received user_info:', data);
    user_id = data.user_id;
    user_name = data.user_name;
    avatar = data.avatar;
    reply_id = data.reply_id;
    reply_to = data.reply_to;
    console.log('User info received:', user_id, user_name);
    socket.join(`user:${user_id}`);
    console.log(`User ${user_id} joined room user:${user_id}`);
  });

  // Lấy tất cả tin nhắn cũ từ Redis khi người dùng kết nối
  redis.lrange('chat:messages', 0, -1, (err, messages) => {
    if (err) {
      console.error('Error retrieving messages from Redis:', err);
      return;
    }
    console.log('Retrieved messages from Redis:', messages.length);
    messages.reverse().forEach((message) => {
      socket.emit('message', JSON.parse(message));
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
      timestamp: data.timestamp || Date.now(),
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
            user_id: repliedMessage.user_id,
            message: repliedMessage.message,
            user_name: repliedMessage.user_name,
            avatar: repliedMessage.avatar,
            timestamp: repliedMessage.timestamp,
            reply_id: repliedMessage.reply_id,
            reply_to: repliedMessage.reply_to,
          };
        }

        io.to('default').emit('message', messageData);
        redis.lpush('chat:messages', JSON.stringify(messageData));
        console.log('Emitted message to default room:', messageData);
      });
    } else {
      io.to('default').emit('message', messageData);
      redis.lpush('chat:messages', JSON.stringify(messageData));
      console.log('Emitted message to default room:', messageData);
    }
  });

  // Lắng nghe sự kiện 'send_name' từ client
  socket.on('send_name', (name, photo) => {
    console.log(`Name received: ${name}`);

    const notificationId = uuidv4();
    const notification = {
      id: notificationId,
      name: name,
      avatar: photo,
    };

    io.emit('show_name', notification);
    console.log('Emitted show_name:', notification);

    setTimeout(() => {
      io.emit('remove_name', { id: notificationId });
      console.log(`Notification with ID ${notificationId} removed`);
    }, 2000);
  });

  // Tham gia vào room "default"
  socket.join('default');

  // Lắng nghe khi người dùng ngắt kết nối
  socket.on('disconnect', () => {
    console.log('User disconnected:', socket.id);
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
