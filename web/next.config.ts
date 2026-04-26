import type { NextConfig } from 'next';

const cmsUrl = process.env.NEXT_PUBLIC_CMS_URL || 'http://localhost:3000';

const config: NextConfig = {
  reactStrictMode: true,
  output: 'standalone',
  images: {
    remotePatterns: [
      // Media uploaded to slatestack is served from <CMS>/uploads/*
      ...(() => {
        try {
          const u = new URL(cmsUrl);
          return [{ protocol: u.protocol.replace(':', '') as 'http' | 'https', hostname: u.hostname, port: u.port || undefined, pathname: '/uploads/**' }];
        } catch {
          return [];
        }
      })(),
    ],
  },
};

export default config;
