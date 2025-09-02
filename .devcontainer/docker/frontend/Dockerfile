FROM node:18-alpine

# Thư mục làm việc
WORKDIR /workspace/frontend

# Cài đặt deps hệ thống cần thiết (sharp, v.v.)
RUN apk add --no-cache python3 make g++

# Copy package.json từ current context
COPY package.json package-lock.json* ./

# Cài deps
RUN npm install

# Expose port Next.js
EXPOSE 3000

CMD ["npm", "run", "dev", "--", "--hostname", "0.0.0.0"]
