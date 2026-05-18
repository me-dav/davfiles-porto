'use client'
// src/app/dashboard/journal/page.tsx

import { useEffect, useState } from 'react'
import Link from 'next/link'
import api from '@/lib/api'
import type { JournalEntry } from '@/types/journal'

export default function DashboardJournalPage() {
  const [entries, setEntries] = useState<JournalEntry[]>([])
  const [loading, setLoading] = useState(true)
  const [error,   setError]   = useState('')

  const fetchEntries = async () => {
    try {
      // Fetch all (including drafts — you may want a separate admin endpoint for this)
      const res = await api.get('/journal?per=50')
      setEntries(res.data.data.entries)
    } catch (err: any) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => { fetchEntries() }, [])

  const handleDelete = async (id: number, title: string) => {
    if (!confirm(`Delete "${title}"? This cannot be undone.`)) return
    try {
      await api.delete(`/admin/journal/${id}`)
      setEntries((prev) => prev.filter((e) => e.id !== id))
    } catch (err: any) {
      alert(err.message)
    }
  }

  return (
    <div className="p-8 md:p-12">
      <div className="flex items-center justify-between mb-10">
        <div>
          <h1 className="font-display font-black text-3xl uppercase text-sand">Journal</h1>
          <p className="font-body text-xs text-stone mt-1">
            {entries.length} entr{entries.length !== 1 ? 'ies' : 'y'}
          </p>
        </div>
        <Link
          href="/dashboard/journal/new"
          className="font-body text-xs uppercase tracking-wide2 text-sand border border-stone/60 px-6 py-2.5 hover:bg-stone/15 transition-all"
        >
          + New
        </Link>
      </div>

      {error  && <p className="font-body text-xs text-red-400/80 mb-6">{error}</p>}

      {loading ? (
        <p className="font-body text-xs text-stone animate-pulse">Loading…</p>
      ) : entries.length === 0 ? (
        <div className="border border-slate p-12 text-center">
          <p className="font-body text-sm text-stone">No journal entries yet.</p>
        </div>
      ) : (
        <div className="border border-slate divide-y divide-slate">
          <div className="grid grid-cols-12 gap-4 px-5 py-3 bg-graphite">
            <span className="col-span-5 font-body text-[9px] uppercase tracking-wide2 text-stone">Title</span>
            <span className="col-span-2 font-body text-[9px] uppercase tracking-wide2 text-stone">Type</span>
            <span className="col-span-2 font-body text-[9px] uppercase tracking-wide2 text-stone">Status</span>
            <span className="col-span-3 font-body text-[9px] uppercase tracking-wide2 text-stone">Actions</span>
          </div>

          {entries.map((entry) => (
            <div key={entry.id} className="grid grid-cols-12 gap-4 px-5 py-4 items-center hover:bg-graphite/50 transition-colors">
              <div className="col-span-5">
                <p className="font-body text-sm text-sand truncate">{entry.title}</p>
                <p className="font-body text-[10px] text-stone/60 truncate">{entry.slug}</p>
              </div>
              <span className="col-span-2 font-body text-xs text-ash">{entry.type}</span>
              <span className="col-span-2">
                <span className={`font-body text-[9px] uppercase tracking-wide2 border px-2 py-0.5 ${
                  entry.status === 'published'
                    ? 'border-ash/30 text-ash'
                    : 'border-stone/30 text-stone'
                }`}>
                  {entry.status}
                </span>
              </span>
              <div className="col-span-3 flex gap-4">
                <Link
                  href={`/dashboard/journal/${entry.id}/edit`}
                  className="font-body text-[10px] uppercase tracking-wide2 text-stone hover:text-sand transition-colors"
                >
                  Edit
                </Link>
                <button
                  onClick={() => handleDelete(entry.id, entry.title)}
                  className="font-body text-[10px] uppercase tracking-wide2 text-stone/40 hover:text-red-400 transition-colors"
                >
                  Delete
                </button>
                {entry.status === 'published' && (
                  <Link
                    href={`/journal/${entry.slug}`}
                    target="_blank"
                    className="font-body text-[10px] uppercase tracking-wide2 text-stone/40 hover:text-ash transition-colors"
                  >
                    View ↗
                  </Link>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}