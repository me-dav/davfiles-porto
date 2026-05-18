// src/app/not-found.tsx
import Link from 'next/link'
import Layout from '@/components/layout/Layout'

export default function NotFound() {
  return (
    <Layout>
      <div className="flex flex-col items-center justify-center min-h-[70vh] px-8 text-center">
        <p className="font-display font-black text-[8rem] leading-none text-slate select-none" aria-hidden="true">
          404
        </p>
        <h1 className="font-display font-black text-display-md uppercase text-sand mt-2">
          Not Found
        </h1>
        <p className="font-body text-sm text-stone mt-4 mb-8">
          This page doesn't exist — or has moved.
        </p>
        <Link
          href="/"
          className="font-body text-xs uppercase tracking-wide2 text-sand border border-stone/60 px-8 py-3 hover:bg-stone/15 transition-all"
        >
          ← Back Home
        </Link>
      </div>
    </Layout>
  )
}