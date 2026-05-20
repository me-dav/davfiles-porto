/** @type {import('next').NextConfig} */
const nextConfig = {
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'api.davfiles.my.id', // Kadang Laravel kebaca sebagai IP loopback
        pathname: '/storage/**',
      },
      {
        protocol: 'http',
        hostname: 'localhost',
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