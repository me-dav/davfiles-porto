'use client'
// src/components/dashboard/ProjectForm.tsx

import { useState } from 'react'
import { useRouter } from 'next/navigation'
import api from '@/lib/api'
import type { Project } from '@/types/project'

interface ProjectFormProps {
  initial?: Partial<Project>
  projectId?: number
}

const EMPTY = {
  name: '', slug: '', short_description: '', role: '',
  stack: '', year: new Date().getFullYear(),
  preview_image_url: '', live_url: '', github_url: '',
  body: '', featured: false,
}

function slugify(s: string) {
  return s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')
}

export default function ProjectForm({ initial, projectId }: ProjectFormProps) {
  const router = useRouter()
  const isEdit = !!projectId

  const [form, setForm] = useState({
    ...EMPTY,
    ...initial,
    stack: Array.isArray(initial?.stack) ? initial.stack.join(', ') : (initial?.stack ?? ''),
    featured: initial?.featured ?? false,
  })
  const [uploading, setUploading] = useState(false)
  const [saving,    setSaving]    = useState(false)
  const [error,     setError]     = useState('')

  const set = (key: string, value: any) => setForm((f) => ({ ...f, [key]: value }))

  const handleNameChange = (v: string) => {
    setForm((f) => ({ ...f, name: v, slug: slugify(v) }))
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
      set('preview_image_url', res.data.data.url)
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
      stack: form.stack.split(',').map((s: string) => s.trim()).filter(Boolean),
      year:  Number(form.year),
    }
    try {
      if (isEdit) {
        await api.put(`/admin/projects/${projectId}`, payload)
      } else {
        await api.post('/admin/projects', payload)
      }
      router.push('/dashboard/projects')
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

      {/* Name */}
      <div className="flex flex-col gap-1.5">
        <label className={labelClass}>Name *</label>
        <input
          required
          type="text"
          value={form.name}
          onChange={(e) => handleNameChange(e.target.value)}
          className={inputClass}
        />
      </div>

      {/* Slug */}
      <div className="flex flex-col gap-1.5">
        <label className={labelClass}>Slug * <span className="text-stone/40 normal-case tracking-normal">(auto-generated, editable)</span></label>
        <input
          required
          type="text"
          value={form.slug}
          onChange={(e) => set('slug', e.target.value)}
          className={inputClass}
        />
      </div>

      {/* Role + Year side by side */}
      <div className="grid grid-cols-2 gap-4">
        <div className="flex flex-col gap-1.5">
          <label className={labelClass}>Role</label>
          <input
            type="text"
            value={form.role}
            onChange={(e) => set('role', e.target.value)}
            className={inputClass}
            placeholder="Full-Stack Developer"
          />
        </div>
        <div className="flex flex-col gap-1.5">
          <label className={labelClass}>Year *</label>
          <input
            required
            type="number"
            min="2000"
            max="2100"
            value={form.year}
            onChange={(e) => set('year', e.target.value)}
            className={inputClass}
          />
        </div>
      </div>

      {/* Short description */}
      <div className="flex flex-col gap-1.5">
        <label className={labelClass}>Short Description</label>
        <input
          type="text"
          value={form.short_description}
          onChange={(e) => set('short_description', e.target.value)}
          className={inputClass}
          placeholder="One-liner shown on cards"
        />
      </div>

      {/* Stack */}
      <div className="flex flex-col gap-1.5">
        <label className={labelClass}>Stack <span className="text-stone/40 normal-case tracking-normal">(comma separated)</span></label>
        <input
          type="text"
          value={form.stack}
          onChange={(e) => set('stack', e.target.value)}
          className={inputClass}
          placeholder="Next.js, Laravel, MySQL"
        />
      </div>

      {/* Image upload */}
      <div className="flex flex-col gap-2">
        <label className={labelClass}>Preview Image</label>
        <input
          type="file"
          accept="image/jpeg,image/png,image/webp,image/gif"
          onChange={handleImageUpload}
          className="font-body text-xs text-stone file:mr-4 file:border file:border-slate file:bg-graphite file:text-stone file:px-4 file:py-2 file:text-xs file:uppercase file:tracking-wide2 hover:file:bg-slate/40 file:cursor-pointer"
        />
        {uploading && <span className="font-body text-xs text-ash animate-pulse">Uploading…</span>}
        {form.preview_image_url && (
          <div className="flex items-center gap-3">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={form.preview_image_url} alt="" className="w-16 h-10 object-cover border border-slate" />
            <span className="font-body text-[10px] text-stone/60 break-all">{form.preview_image_url}</span>
          </div>
        )}
      </div>

      {/* URLs */}
      <div className="grid grid-cols-2 gap-4">
        <div className="flex flex-col gap-1.5">
          <label className={labelClass}>Live URL</label>
          <input type="url" value={form.live_url} onChange={(e) => set('live_url', e.target.value)} className={inputClass} placeholder="https://" />
        </div>
        <div className="flex flex-col gap-1.5">
          <label className={labelClass}>GitHub URL</label>
          <input type="url" value={form.github_url} onChange={(e) => set('github_url', e.target.value)} className={inputClass} placeholder="https://github.com/" />
        </div>
      </div>

      {/* Body */}
      <div className="flex flex-col gap-1.5">
        <label className={labelClass}>
          Body (HTML) <span className="text-stone/40 normal-case tracking-normal">— upgrade to rich editor later</span>
        </label>
        <textarea
          rows={14}
          value={form.body}
          onChange={(e) => set('body', e.target.value)}
          className={`${inputClass} font-mono resize-y`}
          placeholder={'<h2>Challenge</h2>\n<p>...</p>\n<h2>Approach</h2>\n<p>...</p>\n<h2>Result</h2>\n<p>...</p>'}
        />
      </div>

      {/* Featured */}
      <label className="flex items-center gap-3 cursor-pointer select-none">
        <input
          type="checkbox"
          checked={form.featured}
          onChange={(e) => set('featured', e.target.checked)}
          className="w-4 h-4 accent-ash"
        />
        <span className="font-body text-sm text-ash">Featured on home page</span>
      </label>

      {/* Actions */}
      <div className="flex items-center gap-6 pt-2">
        <button
          type="submit"
          disabled={saving || uploading}
          className="font-body text-xs uppercase tracking-wide2 text-sand border border-stone/60 px-10 py-3 hover:bg-stone/15 transition-all disabled:opacity-40"
        >
          {saving ? 'Saving…' : isEdit ? 'Save Changes' : 'Create Project'}
        </button>
        <button
          type="button"
          onClick={() => router.push('/dashboard/projects')}
          className="font-body text-xs uppercase tracking-wide2 text-stone hover:text-ash transition-colors"
        >
          Cancel
        </button>
      </div>
    </form>
  )
}