'use client'
// src/components/sections/ProjectsGrid.tsx

import { useEffect, useRef } from 'react'
import { gsap, ScrollTrigger } from '@/lib/gsap'
import Link from 'next/link'
import ProjectCard from '@/components/cards/ProjectCard'
import type { Project } from '@/types/project'


interface ProjectsGridProps {
  projects: Project[]
  /** 'featured' = home page layout (first card spans 2 cols) */
  variant?: 'featured' | 'grid'
  /** Show "View all work" link below */
  showViewAll?: boolean
}

export default function ProjectsGrid({
  projects,
  variant = 'grid',
  showViewAll = false,
}: ProjectsGridProps) {
  const sectionRef = useRef<HTMLElement>(null)

  useEffect(() => {
    const ctx = gsap.context(() => {
      gsap.from('.project-card-wrap', {
        y: 48,
        opacity: 0,
        duration: 0.8,
        stagger: 0.12,
        ease: 'power3.out',
        scrollTrigger: {
          trigger: sectionRef.current,
          start: 'top 80%',
        },
      })
    }, sectionRef)
    return () => ctx.revert()
  }, [projects])

  if (!projects.length) {
    return (
      <section className="py-24 px-8 md:px-16">
        <p className="font-body text-stone text-sm">No projects found.</p>
      </section>
    )
  }

  return (
    <section
      ref={sectionRef}
      id="projects"
      className="py-20 px-8 md:px-16"
    >
      {/* Section header */}
      <div className="flex items-end justify-between mb-10">
        <h2 className="font-display font-black text-2xl md:text-display-md uppercase text-sand">
          {variant === 'featured' ? 'Selected Work' : 'All Work'}
        </h2>
        {showViewAll && (
          <Link
            href="/work"
            className="font-body mb-6 md:mb-12 text-xs uppercase tracking-wide2 text-sand hover:text-ash transition-colors underline-offset-4"
          >
            View All
          </Link>
        )}
      </div>

      {/* Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {projects.map((project, i) => {
          const isBig = variant === 'featured' && i === 0

          return (
            <div
              key={project.id}
              className={`project-card-wrap ${isBig ? 'md:col-span-2' : ''}`}
            >
              <ProjectCard
                project={project}
                variant={isBig ? 'featured' : 'grid'}
                index={i}
              />
            </div>
          )
        })}
      </div>
    </section>
  )
}