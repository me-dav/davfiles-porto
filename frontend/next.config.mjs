/** @type {import('next').NextConfig} */
const nextConfig = {
  images: {
    remotePatterns: [
      {
        protocol: 'http',
        hostname: 'localhost',
        port: '8000',
        pathname: '/storage/**',
      },
      {
        protocol: 'http',
        hostname: '127.0.0.1', // Kadang Laravel kebaca sebagai IP loopback
        port: '8000',
        pathname: '/storage/**',
      },
      {
        protocol: 'https',
        hostname: 'images.unsplash.com', // Masukkan kembali yang Unsplash tadi di sini
        pathname: '**',
      },
      // add prod domain later: { protocol: 'https', hostname: 'cdn.yourdomain.com' }
    ],
  },
};

export default nextConfig;