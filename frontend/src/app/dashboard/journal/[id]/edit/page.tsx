'use client'
// src/app/dashboard/journal/[id]/edit/page.tsx

import { useEffect, useState } from 'react'
import { useParams } from 'next/navigation'
import api from '@/lib/api'
import JournalForm from '@/components/dashboard/JournalForm'
import type { JournalEntry } from '@/types/journal'

export default function EditJournalPage() {
  const { id }               = useParams<{ id: string }>()
  const [entry, setEntry]    = useState<JournalEntry | null>(null)
  const [loading, setLoading] = useState(true)
  const [error,   setError]  = useState('')

  useEffect(() => {
    // Fetch all entries then find by id
    // In production you'd add GET /admin/journal/:id
    api.get('/journal?per=100')
      .then((res) => {
        const found = (res.data.data.entries as JournalEntry[]).find((e) => e.id === Number(id))
        if (found) setEntry(found)
        else setError('Entry not found')
      })
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }, [id])

  if (loading) return <div className="p-12"><p className="font-body text-xs text-stone animate-pulse">Loading…</p></div>
  if (error)   return <div className="p-12"><p className="font-body text-xs text-red-400">{error}</p></div>
  if (!entry)  return null

  return (
    <div className="p-8 md:p-12">
      <h1 className="font-display font-black text-3xl uppercase text-sand mb-2">Edit Entry</h1>
      <p className="font-body text-xs text-stone uppercase tracking-wide2 mb-10">{entry.title}</p>
      <JournalForm initial={entry} entryId={entry.id} />
    </div>
  )
}