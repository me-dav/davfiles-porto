'use client'
// src/components/sections/JournalList.tsx

import { useEffect, useRef } from 'react'
import Link from 'next/link'
import { gsap, ScrollTrigger } from '@/lib/gsap'

import JournalItem from '@/components/cards/JournalItem'
import type { JournalEntry } from '@/types/journal'


interface JournalListProps {
  entries: JournalEntry[]
  /** Limit shown (home preview) */
  limit?: number
  showViewAll?: boolean
}

export default function JournalList({
  entries,
  limit,
  showViewAll = false,
}: JournalListProps) {
  const sectionRef = useRef<HTMLElement>(null)
  const items = limit ? entries.slice(0, limit) : entries

  useEffect(() => {
    const ctx = gsap.context(() => {
      gsap.from('.journal-card', {
        y: 32,
        opacity: 0,
        duration: 0.7,
        stagger: 0.1,
        ease: 'power3.out',
        scrollTrigger: {
          trigger: sectionRef.current,
          start: 'top 80%',
        },
      })
    }, sectionRef)
    return () => ctx.revert()
  }, [items])

  return (
    <section
      ref={sectionRef}
      className="py-20 px-8 md:px-16 border-t border-slate"
    >
      {/* Header */}
      <div className="flex items-end justify-between mb-10">
        <h2 className="font-display font-black text-2xl md:text-display-md uppercase text-sand">
          Explorations
        </h2>
        {showViewAll && (
          <Link
            href="/journal"
            className="font-body text-xs uppercase tracking-wide2 text-sand hover:text-stone transition-colors underline-offset-4 mb-6 md:mb-12"
          >
            All Entries →
          </Link>
        )}
      </div>

      {items.length === 0 ? (
        <p className="font-body text-stone text-sm">No entries yet.</p>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {items.map((entry) => (
            <div key={entry.id} className="journal-card">
              <JournalItem entry={entry} />
            </div>
          ))}
        </div>
      )}
    </section>
  )
}