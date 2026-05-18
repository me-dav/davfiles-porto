'use client'
// src/app/dashboard/login/page.tsx

import { useState, useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { login, isAuthenticated } from '@/lib/auth'

export default function LoginPage() {
  const router = useRouter()
  const [form, setForm]     = useState({ email: '', password: '' })
  const [error, setError]   = useState('')
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    if (isAuthenticated()) router.replace('/dashboard/projects')
  }, [router])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      await login(form.email, form.password)
      router.replace('/dashboard/projects')
    } catch (err: any) {
      setError(err.message ?? 'Login failed')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-ink flex items-center justify-center px-6">
      <div className="w-full max-w-sm">
        <h1 className="font-display font-black text-4xl uppercase text-sand mb-2">
          Dashboard
        </h1>
        <p className="font-body text-xs text-stone uppercase tracking-wide2 mb-10">
          Sign in to continue
        </p>

        {error && (
          <p className="font-body text-xs text-red-400/80 mb-6 border border-red-900/40 bg-red-900/10 px-4 py-3">
            {error}
          </p>
        )}

        <form onSubmit={handleSubmit} className="flex flex-col gap-6">
          <div className="flex flex-col gap-2">
            <label htmlFor="login-email" className="font-body text-[10px] uppercase tracking-wide2 text-stone">
              Email
            </label>
            <input
              id="login-email"
              type="email"
              required
              autoComplete="email"
              value={form.email}
              onChange={(e) => setForm({ ...form, email: e.target.value })}
              className="bg-graphite border border-slate px-4 py-3 font-body text-sm text-sand focus:outline-none focus:border-ash transition-colors"
            />
          </div>

          <div className="flex flex-col gap-2">
            <label htmlFor="login-password" className="font-body text-[10px] uppercase tracking-wide2 text-stone">
              Password
            </label>
            <input
              id="login-password"
              type="password"
              required
              autoComplete="current-password"
              value={form.password}
              onChange={(e) => setForm({ ...form, password: e.target.value })}
              className="bg-graphite border border-slate px-4 py-3 font-body text-sm text-sand focus:outline-none focus:border-ash transition-colors"
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="mt-2 font-body text-xs uppercase tracking-wide2 text-sand border border-stone/60 px-8 py-3 hover:bg-stone/15 transition-all disabled:opacity-40"
          >
            {loading ? 'Signing in…' : 'Sign In'}
          </button>
        </form>
      </div>
    </div>
  )
}