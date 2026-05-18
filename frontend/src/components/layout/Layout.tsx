// src/components/layout/Layout.tsx
import Navbar from './Navbar'
import Footer from '@/components/sections/Footer'
import ContactBlock from '../sections/ContactBlock'

interface LayoutProps {
  children: React.ReactNode
  /** Set true on pages with a full-bleed hero so Navbar overlays it */
  heroPage?: boolean
}

export default function Layout({ children, heroPage = false }: LayoutProps) {
  return (
    <div className="relative min-h-screen flex flex-col">
      <Navbar overlaid={heroPage} />
      <main className={`flex-1 ${heroPage ? '' : 'pt-20'}`}>{children}</main>
      <ContactBlock/>
      <Footer />
    </div>
  )
}