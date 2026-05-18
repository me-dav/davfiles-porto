'use client'
// src/app/dashboard/layout.tsx

import { useEffect, useState } from 'react'
import { useRouter, usePathname } from 'next/navigation'
import Link from 'next/link'
import { isAuthenticated, logout } from '@/lib/auth'

const DASH_NAV = [
  { label: 'Projects', href: '/dashboard/projects' },
  { label: 'Journal',  href: '/dashboard/journal' },
]

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  const router   = useRouter()
  const pathname = usePathname()
  const [ready, setReady] = useState(false)

  useEffect(() => {
    if (!isAuthenticated()) {
      router.replace('/dashboard/login')
    } else {
      setReady(true)
    }
  }, [router])

  const handleLogout = async () => {
    await logout()
    router.replace('/dashboard/login')
  }

  if (!ready) {
    return (
      <div className="min-h-screen bg-ink flex items-center justify-center">
        <span className="font-body text-xs uppercase tracking-wide2 text-stone animate-pulse">
          Loading…
        </span>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-ink flex">
      {/* ── Sidebar ── */}
      <aside className="w-56 bg-graphite border-r border-slate flex flex-col justify-between py-8 px-6 shrink-0">
        <div>
          <Link
            href="/"
            className="font-display font-black text-xs uppercase tracking-wide2 text-sand block mb-10 hover:text-ash transition-colors"
          >
            ← David
          </Link>

          <p className="font-body text-[9px] uppercase tracking-wide2 text-stone/60 mb-4">
            Dashboard
          </p>

          <nav className="flex flex-col gap-1" aria-label="Dashboard navigation">
            {DASH_NAV.map((item) => {
              const active = pathname.startsWith(item.href)
              return (
                <Link
                  key={item.href}
                  href={item.href}
                  className={`
                    font-body text-xs uppercase tracking-wide2 px-3 py-2.5 rounded-sm transition-colors
                    ${active
                      ? 'bg-slate text-sand'
                      : 'text-stone hover:text-ash hover:bg-slate/40'
                    }
                  `}
                >
                  {item.label}
                </Link>
              )
            })}
          </nav>
        </div>

        <button
          onClick={handleLogout}
          className="font-body text-[10px] uppercase tracking-wide2 text-stone hover:text-sand transition-colors text-left"
        >
          Logout →
        </button>
      </aside>

      {/* ── Content ── */}
      <main className="flex-1 overflow-auto">
        {children}
      </main>
    </div>
  )
}