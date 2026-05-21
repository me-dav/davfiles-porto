// src/app/work/[slug]/page.tsx
import type { Metadata } from 'next'
import { notFound } from 'next/navigation'
import Image from 'next/image'
import Link from 'next/link'
import Layout from '@/components/layout/Layout'
import { apiFetch } from '@/lib/api'
import type { Project } from '@/types/project'

export const revalidate = 60

interface Props {
  params: { slug: string }
}

async function getProject(slug: string): Promise<Project | null> {
  try {
    const data = await apiFetch<{ project: Project }>(`/projects/${slug}`)
    return data.project
  } catch {
    return null
  }
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const project = await getProject(params.slug)
  if (!project) return { title: 'Project Not Found' }
  return {
    title: project.name,
    description: project.short_description,
    openGraph: {
      images: project.preview_image_url ? [{ url: project.preview_image_url }] : [],
    },
  }
}

export default async function ProjectDetailPage({ params }: Props) {
  const project = await getProject(params.slug)
  if (!project) notFound()

  return (
    <Layout heroPage>
      {/* ── Full-bleed hero ── */}
      <section className="relative h-[70vh] min-h-[500px] flex items-end pb-16 px-8 md:px-16 overflow-hidden">
        {project.preview_image_url ? (
          <Image
            src={project.preview_image_url}
            alt={project.name}
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

        <div className="relative z-10 max-w-4xl">
          {/* Back link */}
          <Link
            href="/work"
            className=" font-body text-[10px] uppercase tracking-wide2 text-sand hover:text-stone transition-colors mb-0 md:mb-6 inline-flex items-center gap-2"
          >
            <span>←</span> All Work
          </Link>

          <h1 className="font-display font-black text-display-lg uppercase text-sand leading-none mt-4">
            {project.name}
          </h1>

          {/* Meta */}
          <div className="mt-5 flex flex-wrap gap-6">
            {[
              { label: 'Role',  value: project.role },
              { label: 'Year',  value: String(project.year) },
              { label: 'Stack', value: project.stack.join(', ') },
            ].map(({ label, value }) => value ? (
              <div key={label}>
                <p className="font-body text-[9px] uppercase tracking-wide2 text-sand mb-1">{label}</p>
                <p className="font-body text-sm text-ash">{value}</p>
              </div>
            ) : null)}
          </div>

          {/* External links */}
          <div className="mt-6 flex flex-wrap gap-4">
            {project.live_url && (
              <a
                href={project.live_url}
                target="_blank"
                rel="noopener noreferrer"
                className="font-body text-xs uppercase tracking-wide2 text-sand border border-stone/60 px-6 py-2 hover:bg-stone/15 transition-all"
              >
                Live Site ↗
              </a>
            )}
            {project.github_url && (
              <a
                href={project.github_url}
                target="_blank"
                rel="noopener noreferrer"
                className="font-body text-xs uppercase tracking-wide2 text-stone hover:text-sand transition-colors flex items-center gap-2"
              >
                GitHub ↗
              </a>
            )}
          </div>
        </div>
      </section>

      {/* ── Body / Case Study ── */}
      <section className="px-8 md:px-16 py-20 max-w-4xl">
        <p className="font-body text-base text-ash leading-relaxed mb-8 text-justify">
          {project.short_description}
        </p>

        {project.body ? (
          <div
            className="prose prose-invert max-w-none text-justify"
            dangerouslySetInnerHTML={{ __html: project.body }}
          />
        ) : (
          <p className="font-body text-sm text-stone italic">Case study coming soon.</p>
        )}
      </section>
    </Layout>
  )
}