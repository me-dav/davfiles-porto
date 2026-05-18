// src/app/journal/[slug]/page.tsx
import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import Image from 'next/image'
import Link from 'next/link'
import Layout from '@/components/layout/Layout'
import { apiFetch } from '@/lib/api'
import type { JournalEntry } from '@/types/journal'

export const revalidate = 60

interface Props {
  params: { slug: string }
}

async function getEntry(slug: string): Promise<JournalEntry | null> {
  try {
    const data = await apiFetch<{ entry: JournalEntry }>(`/journal/${slug}`)
    return data.entry
  } catch {
    return null
  }
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const entry = await getEntry(params.slug)
  if (!entry) return { title: 'Not Found' }
  return {
    title: entry.title,
    openGraph: {
      images: entry.hero_image_url ? [{ url: entry.hero_image_url }] : [],
    },
  }
}

export default async function JournalEntryPage({ params }: Props) {
  const entry = await getEntry(params.slug)
  if (!entry) notFound()

  const date = entry.published_at
    ? new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })
        .format(new Date(entry.published_at))
    : null

  return (
    <Layout heroPage>
      {/* ── Hero ── */}
      <section className="relative h-[55vh] min-h-[400px] flex items-end pb-16 px-8 md:px-16 overflow-hidden">
        {entry.hero_image_url ? (
          <Image
            src={entry.hero_image_url}
            alt={entry.title}
            fill
            priority
            sizes="100vw"
            className="object-cover"
            style={{ filter: 'brightness(0.3) saturate(0.7)' }}
          />
        ) : (
          <div className="absolute inset-0 bg-graphite" />
        )}
        <div className="absolute inset-0 bg-vignette-bottom" aria-hidden="true" />

        <div className="relative z-10 max-w-3xl">
          <Link
            href="/journal"
            className="font-body text-[10px] uppercase tracking-wide2 text-sand hover:text-stone transition-colors inline-flex items-center gap-2"
          >
            ← Journal
          </Link>

          <div className="mt-4 flex items-center gap-4 mb-4">
            <span className="font-body text-[10px] uppercase tracking-wide2 text-sand border border-stone/40 pr-3 py-1 ">
              {entry.type}
            </span>
            {date && (
              <span className="font-body text-[10px] text-sand/60">{date}</span>
            )}
          </div>

          <h1 className="font-display font-black text-display-md uppercase text-sand leading-tight">
            {entry.title}
          </h1>

          {entry.tags?.length > 0 && (
            <div className="mt-4 flex flex-wrap gap-2">
              {entry.tags.map((tag) => (
                <span key={tag} className="font-body text-[10px] text-sand/60">#{tag}</span>
              ))}
            </div>
          )}
        </div>
      </section>

      {/* ── Body ── */}
      <section className="px-8 md:px-16 py-20 max-w-3xl">
        {entry.body ? (
          <div
            className="prose prose-invert max-w-none"
            dangerouslySetInnerHTML={{ __html: entry.body }}
          />
        ) : (
          <p className="font-body text-stone italic text-sm">Content coming soon.</p>
        )}
      </section>
    </Layout>
  )
}