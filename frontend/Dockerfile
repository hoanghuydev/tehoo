FROM node:18-alpine

# Set working directory
WORKDIR /app

# Add a work group and user
RUN addgroup --system --gid 1001 nodejs
RUN adduser --system --uid 1001 nextjs

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci --only=production

# Copy source code
COPY . .

# Set ownership
RUN chown -R nextjs:nodejs /app
USER nextjs

# Expose port
EXPOSE 3000

# Set environment variable
ENV NODE_ENV=development

# Start the application in development mode
CMD ["npm", "run", "dev"]
