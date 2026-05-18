// src/types/journal.ts
export type JournalType =
  | 'exploration'
  | 'server-notes'
  | 'hiking'
  | 'ui-thoughts'
  | 'learning-log'

export interface JournalEntry {
  id: number
  title: string
  slug: string
  type: JournalType
  hero_image_url: string
  body?: string
  tags: string[]
  status: 'draft' | 'published'
  published_at: string
}

export interface JournalResponse {
  entries: JournalEntry[]
  meta: { total: number; per_page: number; current_page: number }
}