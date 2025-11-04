import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Enable hot reloading in Docker
  webpack: (config, { dev }) => {
    if (dev) {
      config.watchOptions = {
        poll: 1000,
        aggregateTimeout: 300,
      };
    }
    return config;
  },
  // Ensure the dev server accepts connections from any host
  experimental: {
    // This helps with Docker networking
  },
};

export default nextConfig;
