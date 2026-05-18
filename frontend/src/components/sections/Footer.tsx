// src/components/sections/Footer.tsx
import Link from 'next/link'

const NAV = [
  { label: 'Work',    href: '/work' },
  { label: 'Journal', href: '/journal' },
  { label: 'About',   href: '/about' },
]

const SOCIALS = [
  { label: 'GitHub',    href: 'https://github.com/mdavid' },
  { label: 'LinkedIn',  href: 'https://linkedin.com/in/mdavid' },
  { label: 'Instagram', href: 'https://instagram.com/mdavid' },
]

export default function Footer() {
  return (
    <footer className="border-t border-slate px-8 md:px-16 py-12">
      <div className="flex flex-col items-center justify-between gap-2">
        {/* Brand */}
        <Link
          href="/"
          className="font-display font-black text-xs uppercase tracking-wide2 text-sand hover:text-ash transition-colors mb-0 underline-offset-2"
        >
          Mohammad David Nur Syahfrudin
        </Link>

        {/* Copyright */}
        <span className="font-body text-[16px] text-stone">
          © dav-files {new Date().getFullYear()}
        </span>
      </div>
    </footer>
  )
}