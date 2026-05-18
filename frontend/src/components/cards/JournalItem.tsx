// src/components/cards/JournalItem.tsx
import Image from 'next/image'
import Link from 'next/link'
import type { JournalEntry } from '@/types/journal'

const TYPE_LABELS: Record<string, string> = {
  'exploration':  'Exploration',
  'server-notes': 'Server Notes',
  'hiking':       'Hiking',
  'ui-thoughts':  'UI Thoughts',
  'learning-log': 'Learning Log',
}

interface JournalItemProps {
  entry: JournalEntry
}

export default function JournalItem({ entry }: JournalItemProps) {
  const isVisual = entry.type === 'exploration' || entry.type === 'hiking'
  const label    = TYPE_LABELS[entry.type] ?? entry.type

  const date = entry.published_at
    ? new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
        .format(new Date(entry.published_at))
    : null

  return (
    <Link
      href={`/journal/${entry.slug}`}
      className="group flex flex-col no-underline"
      aria-label={entry.title}
    >
      {/* Image — visual types only */}
      {isVisual && (
        <div className="relative overflow-hidden mb-4" style={{ height: '200px' }}>
          {entry.hero_image_url ? (
            <Image
              src={entry.hero_image_url}
              alt={entry.title}
              fill
              sizes="(max-width:768px) 100vw, 33vw"
              className="object-cover transition-transform duration-500 ease-editorial group-hover:scale-[1.04]"
              style={{ filter: 'brightness(0.7) saturate(0.8)' }}
            />
          ) : (
            <div className="absolute inset-0 bg-graphite" />
          )}
        </div>
      )}

      {/* Text block */}
      <div className={isVisual ? '' : 'border-t border-slate pt-5'}>
        {/* Type + date */}
        <div className="flex items-center gap-3 mb-2">
          <span className="font-body text-[10px] uppercase tracking-wide2 text-ash">
            {label}
          </span>
          {date && (
            <>
              <span className="text-ash" aria-hidden="true">·</span>
              <span className="font-body text-[10px] text-ash">{date}</span>
            </>
          )}
        </div>

        {/* Title */}
        <h3
          className="font-display font-black text-xl uppercase leading-tight text-sand
                     group-hover:text-white transition-colors duration-300"
        >
          {entry.title}
        </h3>

        {/* Tags */}
        {entry.tags?.length > 0 && (
          <div className="mt-3 flex flex-wrap gap-2">
            {entry.tags.map((tag) => (
              <span key={tag} className="font-body text-[10px] text-stone">
                #{tag}
              </span>
            ))}
          </div>
        )}
      </div>
    </Link>
  )
}