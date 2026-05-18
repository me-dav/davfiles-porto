// src/app/journal/page.tsx
import type { Metadata } from 'next'
import Layout from '@/components/layout/Layout'
import JournalList from '@/components/sections/JournalList'
import { apiFetch } from '@/lib/api'
import type { JournalResponse } from '@/types/journal'
import type { JournalType } from '@/types/journal'

export const revalidate = 60

export const metadata: Metadata = {
  title: 'Journal',
  description: 'Explorations, server notes, hiking logs, UI thoughts, and learning journals.',
}

const FILTERS: { label: string; type?: JournalType }[] = [
  { label: 'All' },
  { label: 'Exploration',  type: 'exploration' },
  { label: 'Server Notes', type: 'server-notes' },
  { label: 'Hiking',       type: 'hiking' },
  { label: 'UI Thoughts',  type: 'ui-thoughts' },
  { label: 'Learning Log', type: 'learning-log' },
]

async function getEntries(type?: string) {
  try {
    const q = type ? `?type=${type}` : ''
    const data = await apiFetch<JournalResponse>(`/journal${q}`)
    return data.entries
  } catch {
    return []
  }
}

interface Props {
  searchParams: { type?: string }
}

export default async function JournalPage({ searchParams }: Props) {
  const activeType = searchParams.type
  const entries    = await getEntries(activeType)

  return (
    <Layout>
      <div className="px-8 md:px-16 pt-12 pb-0">
        <p className="font-body text-[10px] uppercase tracking-wide2 text-ash mb-4">
          Writing &amp; Notes
        </p>
        <h1 className="font-display font-black text-display-lg uppercase text-sand leading-none mb-10">
          Journal
        </h1>

        {/* Filter pills */}
        <div className="flex flex-wrap gap-3 pb-10 border-b border-slate">
          {FILTERS.map((f) => {
            const active = f.type ? activeType === f.type : !activeType
            return (
              <a
                key={f.label}
                href={f.type ? `/journal?type=${f.type}` : '/journal'}
                className={`
                  font-body text-[10px] uppercase tracking-wide2 pr-4 md:px-4 py-2 border transition-colors underline-offset-2
                  ${active
                    ? 'border-ash text-stone md:bg-slate/40'
                    : 'border-slate text-ash hover:border-stone hover:text-sand'
                  }
                `}
              >
                {f.label}
              </a>
            )
          })}
        </div>
      </div>

      <JournalList entries={entries} />
    </Layout>
  )
}