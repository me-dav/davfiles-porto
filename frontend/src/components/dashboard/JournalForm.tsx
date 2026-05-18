'use client'
// src/components/dashboard/JournalForm.tsx

import { useState } from 'react'
import { useRouter } from 'next/navigation'
import api from '@/lib/api'
import type { JournalEntry, JournalType } from '@/types/journal'

interface JournalFormProps {
  initial?: Partial<JournalEntry>
  entryId?: number
}

const JOURNAL_TYPES: JournalType[] = [
  'exploration', 'server-notes', 'hiking', 'ui-thoughts', 'learning-log',
]

const EMPTY = {
  title: '', slug: '', type: 'exploration' as JournalType,
  hero_image_url: '', body: '', tags: '',
  status: 'draft' as 'draft' | 'published',
}

function slugify(s: string) {
  return s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')
}

export default function JournalForm({ initial, entryId }: JournalFormProps) {
  const router  = useRouter()
  const isEdit  = !!entryId

  const [form, setForm] = useState({
    ...EMPTY,
    ...initial,
    tags: Array.isArray(initial?.tags) ? initial.tags.join(', ') : (initial?.tags ?? ''),
  })
  const [uploading, setUploading] = useState(false)
  const [saving,    setSaving]    = useState(false)
  const [error,     setError]     = useState('')

  const set = (key: string, value: any) => setForm((f) => ({ ...f, [key]: value }))

  const handleTitleChange = (v: string) => {
    setForm((f) => ({ ...f, title: v, slug: slugify(v) }))
  }

  const handleImageUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (!file) return
    setUploading(true)
    try {
      const fd = new FormData()
      fd.append('file', file)
      const res = await api.post('/uploads', fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      set('hero_image_url', res.data.data.url)
    } catch (err: any) {
      setError('Image upload failed: ' + err.message)
    } finally {
      setUploading(false)
    }
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setSaving(true)
    setError('')
    const payload = {
      ...form,
      tags: form.tags.split(',').map((t: string) => t.trim()).filter(Boolean),
    }
    try {
      if (isEdit) {
        await api.put(`/admin/journal/${entryId}`, payload)
      } else {
        await api.post('/admin/journal', payload)
      }
      router.push('/dashboard/journal')
    } catch (err: any) {
      setError(err.message)
    } finally {
      setSaving(false)
    }
  }

  const inputClass = `
    bg-graphite border border-slate px-4 py-2.5
    font-body text-sm text-sand
    focus:outline-none focus:border-ash
    transition-colors w-full
  `
  const labelClass = 'font-body text-[10px] uppercase tracking-wide2 text-stone'

  return (
    <form onSubmit={handleSubmit} className="flex flex-col gap-6 max-w-2xl">
      {error && (
        <p className="font-body text-xs text-red-400/80 border border-red-900/30 bg-red-900/10 px-4 py-3">
          {error}
        </p>
      )}

      {/* Title */}
      <div className="flex flex-col gap-1.5">
        <label className={labelClass}>Title *</label>
        <input
          required
          type="text"
          value={form.title}
          onChange={(e) => handleTitleChange(e.target.value)}
          className={inputClass}
        />
      </div>

      {/* Slug */}
      <div className="flex flex-col gap-1.5">
        <label className={labelClass}>Slug *</label>
        <input
          required
          type="text"
          value={form.slug}
          onChange={(e) => set('slug', e.target.value)}
          className={inputClass}
        />
      </div>

      {/* Type + Status */}
      <div className="grid grid-cols-2 gap-4">
        <div className="flex flex-col gap-1.5">
          <label className={labelClass}>Type *</label>
          <select
            required
            value={form.type}
            onChange={(e) => set('type', e.target.value)}
            className={`${inputClass} appearance-none cursor-pointer`}
          >
            {JOURNAL_TYPES.map((t) => (
              <option key={t} value={t}>{t}</option>
            ))}
          </select>
        </div>
        <div className="flex flex-col gap-1.5">
          <label className={labelClass}>Status</label>
          <select
            value={form.status}
            onChange={(e) => set('status', e.target.value)}
            className={`${inputClass} appearance-none cursor-pointer`}
          >
            <option value="draft">Draft</option>
            <option value="published">Published</option>
          </select>
        </div>
      </div>

      {/* Tags */}
      <div className="flex flex-col gap-1.5">
        <label className={labelClass}>Tags <span className="text-stone/40 normal-case tracking-normal">(comma separated)</span></label>
        <input
          type="text"
          value={form.tags}
          onChange={(e) => set('tags', e.target.value)}
          className={inputClass}
          placeholder="server, proxmox, homelab"
        />
      </div>

      {/* Hero image upload */}
      <div className="flex flex-col gap-2">
        <label className={labelClass}>Hero Image</label>
        <input
          type="file"
          accept="image/jpeg,image/png,image/webp,image/gif"
          onChange={handleImageUpload}
          className="font-body text-xs text-stone file:mr-4 file:border file:border-slate file:bg-graphite file:text-stone file:px-4 file:py-2 file:text-xs file:uppercase file:tracking-wide2 hover:file:bg-slate/40 file:cursor-pointer"
        />
        {uploading && <span className="font-body text-xs text-ash animate-pulse">Uploading…</span>}
        {form.hero_image_url && (
          <div className="flex items-center gap-3">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={form.hero_image_url} alt="" className="w-16 h-10 object-cover border border-slate" />
            <span className="font-body text-[10px] text-stone/60 break-all">{form.hero_image_url}</span>
          </div>
        )}
      </div>

      {/* Body */}
      <div className="flex flex-col gap-1.5">
        <label className={labelClass}>
          Body (HTML) <span className="text-stone/40 normal-case tracking-normal">— upgrade to rich editor later</span>
        </label>
        <textarea
          rows={16}
          value={form.body}
          onChange={(e) => set('body', e.target.value)}
          className={`${inputClass} font-mono resize-y`}
          placeholder="<p>Start writing...</p>"
        />
      </div>

      {/* Actions */}
      <div className="flex items-center gap-6 pt-2">
        <button
          type="submit"
          disabled={saving || uploading}
          className="font-body text-xs uppercase tracking-wide2 text-sand border border-stone/60 px-10 py-3 hover:bg-stone/15 transition-all disabled:opacity-40"
        >
          {saving ? 'Saving…' : isEdit ? 'Save Changes' : 'Create Entry'}
        </button>
        <button
          type="button"
          onClick={() => router.push('/dashboard/journal')}
          className="font-body text-xs uppercase tracking-wide2 text-stone hover:text-ash transition-colors"
        >
          Cancel
        </button>
      </div>
    </form>
  )
}