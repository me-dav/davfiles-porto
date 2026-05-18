// src/components/cards/ProjectCard.tsx
import Image from 'next/image'
import Link from 'next/link'
import type { Project } from '@/types/project'

interface ProjectCardProps {
  project: Project
  /** featured = large format; grid = standard height */
  variant?: 'featured' | 'grid'
  /** Index used for stagger animation classnames */
  index?: number
}

export default function ProjectCard({
  project,
  variant = 'grid',
  index = 0,
}: ProjectCardProps) {
  const isFeatured = variant === 'featured'

  return (
    <Link
      href={`/work/${project.slug}`}
      className={`
        group relative flex flex-col overflow-hidden block
        ${isFeatured ? 'h-[65vh] min-h-[400px]' : 'h-[50vh] min-h-[320px]'}
      `}
      aria-label={`${project.name} — ${project.short_description}`}
    >
      {/* Image */}
      <div className="absolute inset-0">
        {project.preview_image_url ? (
          <Image
            src={project.preview_image_url}
            alt={project.name}
            fill
            sizes={isFeatured ? '(max-width:768px) 100vw, 66vw' : '(max-width:768px) 100vw, 33vw'}
            className="object-cover transition-transform duration-700 ease-editorial group-hover:scale-[1.04]"
            style={{ filter: 'brightness(0.45) saturate(0.75)' }}
          />
        ) : (
          // Placeholder when no image
          <div className="absolute inset-0 bg-graphite" />
        )}
      </div>

      {/* Gradient overlay */}
      <div
        className="absolute inset-0 bg-vignette-bottom transition-opacity duration-500 group-hover:opacity-80"
        aria-hidden="true"
      />

      {/* Content */}
      <div className="absolute inset-0 flex flex-col justify-end p-7 md:p-9">
        {/* Meta row */}
        <div className="flex items-center gap-3 mb-3">
          <span className="font-body text-[10px] uppercase tracking-wide2 text-sand/80">
            {project.role}
          </span>
          <span className="text-sand/40" aria-hidden="true">·</span>
          <span className="font-body text-[10px] uppercase tracking-wide2 text-sand/80">
            {project.year}
          </span>
        </div>

        {/* Title */}
        <h2
          className={`
            font-display font-black uppercase leading-none tracking-tight text-sand
            transition-colors duration-300 group-hover:text-white
            ${isFeatured ? 'text-display-md md:text-display-md' : 'text-[2rem] md:text-[2.5rem]'}
          `}
        >
          {project.name}
        </h2>

        {/* Short description — only on featured */}
        {isFeatured && (
          <p className="mt-3 font-body text-sm text-ash/80 max-w-sm line-clamp-2 leading-relaxed">
            {project.short_description}
          </p>
        )}

        {/* Stack tags */}
        <div className="mt-4 flex flex-wrap gap-2">
          {project.stack.slice(0, 4).map((tech) => (
            <span
              key={tech}
              className="font-body text-[10px] uppercase tracking-editorial border border-stone/30 px-2.5 py-0.5 text-stone group-hover:border-stone/50 transition-colors"
            >
              {tech}
            </span>
          ))}
        </div>

        {/* Arrow — appears on hover */}
        <div className="mt-4 overflow-hidden h-4 ml-2">
          <span
            className="block font-body text-xs uppercase tracking-wide2 text-ash/60
                       translate-y-4 group-hover:translate-y-0 transition-transform duration-300 ease-editorial"
          >
            View Project →
          </span>
        </div>
      </div>
    </Link>
  )
}