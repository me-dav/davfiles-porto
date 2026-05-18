'use client'
// src/components/sections/ToolboxLoop.tsx

import { useEffect, useRef } from 'react'
import { gsap } from '@/lib/gsap'

const TOOLS = [
  'React', 'Next.js', 'TypeScript', 'Laravel', 'Python',
  'C++', 'Docker', 'Tailwind CSS', 'Vercel', 'GitHub',
  'MySQL', 'Linux',
]

export default function ToolboxLoop() {
  const trackRef = useRef<HTMLDivElement>(null)
  const tweenRef = useRef<gsap.core.Tween | null>(null)

  useEffect(() => {
    const track = trackRef.current
    if (!track) return

    // Total width is half (because we duplicate the list)
    const totalWidth = track.scrollWidth / 2

    tweenRef.current = gsap.to(track, {
      x: -totalWidth,
      duration: 28,
      ease: 'none',
      repeat: -1,
      // Seamless: on each repeat, snap back to 0 instantly
      modifiers: {
        x: gsap.utils.unitize((x: number) => parseFloat(x) % totalWidth),
      },
    })

    return () => {
      tweenRef.current?.kill()
    }
  }, [])

  const pause  = () => tweenRef.current?.pause()
  const resume = () => tweenRef.current?.resume()

  // Duplicate list for seamless loop
  const doubled = [...TOOLS, ...TOOLS]

  return (
    <section
      className="py-16 border-y border-slate overflow-hidden"
      aria-label="Technology stack"
    >
      {/* Eyebrow */}
      <p className="font-body text-[16px] uppercase tracking-wide2 text-sand/80 text-center mb-10">
        Stack &amp; Toolbox
      </p>

      <div
        className="overflow-hidden"
        onMouseEnter={pause}
        onMouseLeave={resume}
        onFocus={pause}
        onBlur={resume}
      >
        <div
          ref={trackRef}
          className="flex gap-12 whitespace-nowrap w-max"
          aria-hidden="true"  // decorative; screen readers get the section label above
        >
          {doubled.map((tool, i) => (
            <span
              key={`${tool}-${i}`}
              className="
                font-display font-black text-2xl uppercase tracking-tight
                text-stone hover:text-sand focus:text-sand
                transition-colors duration-200 cursor-default
              "
              // Make individual items keyboard-focusable for accessibility
              tabIndex={i < TOOLS.length ? 0 : -1}
              aria-label={i < TOOLS.length ? tool : undefined}
            >
              {tool}
              <span className="ml-12 text-slate" aria-hidden="true">·</span>
            </span>
          ))}
        </div>
      </div>
    </section>
  )
}