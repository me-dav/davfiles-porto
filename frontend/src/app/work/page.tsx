// src/app/work/page.tsx
import type { Metadata } from 'next'
import Layout from '@/components/layout/Layout'
import ProjectsGrid from '@/components/sections/ProjectsGrid'
import { apiFetch } from '@/lib/api'
import type { ProjectsResponse } from '@/types/project'

export const revalidate = 60

export const metadata: Metadata = {
  title: 'Work',
  description: 'Full portfolio of projects — web platforms, IoT systems, and editorial tools.',
}

async function getAllProjects() {
  try {
    const data = await apiFetch<ProjectsResponse>('/projects?per=50')
    return data.projects
  } catch {
    return []
  }
}

export default async function WorkPage() {
  const projects = await getAllProjects()

  return (
    <Layout>
      <div className="px-8 md:px-16 pt-12 pb-4">
        <p className="font-body text-[10px] uppercase tracking-wide2 text-ash mb-4">
          Portfolio
        </p>
        <h1 className="font-display font-black text-display-lg uppercase text-sand leading-none">
          Work
        </h1>
      </div>

      <ProjectsGrid projects={projects} variant="grid" />
    </Layout>
  )
}