# Dockerfile for Node.js
FROM node:16

WORKDIR /app

# Install dependencies
COPY package.json package-lock.json /app/
RUN npm install

# Copy the app
COPY . /app

# Expose the port
EXPOSE 4000

CMD ["node", "server.js"]
