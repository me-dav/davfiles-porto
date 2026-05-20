// src/app/layout.tsx
import type { Metadata } from 'next'
import './globals.css'

export const metadata: Metadata = {
  title: {
    default: 'DavFiles',
    template: '%s — DavFiles',
  },
  description:
    'Full-Stack Developer, IoT Engineer, Editorial Designer based in Malang, Indonesia.',
  openGraph: {
    type: 'website',
    locale: 'en_US',
    siteName: 'Mohammad David Nur Syahfrudin',
  },
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en" className="dark">
      <body className="bg-ink text-sand font-body antialiased">
        {/* Grain overlay — fixed, pointer-events-none */}
        <div
          aria-hidden="true"
          className="pointer-events-none fixed inset-0 z-[9999] opacity-[0.035] mix-blend-overlay"
          style={{
            backgroundImage: 'url(/images/grain.png)',
            backgroundSize: '180px',
            backgroundRepeat: 'repeat',
          }}
        />
        {children}
      </body>
    </html>
  )
}