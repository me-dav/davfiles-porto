// src/app/page.tsx
import Layout from '@/components/layout/Layout'
import Hero from '@/components/sections/Hero'
import ProjectsGrid from '@/components/sections/ProjectsGrid'
import AboutBlock from '@/components/sections/AboutBlock'
import JournalList from '@/components/sections/JournalList'
import ToolboxLoop from '@/components/sections/ToolboxLoop'
import SoftSkills from '@/components/sections/SoftSkills'
import ContactBlock from '@/components/sections/ContactBlock'
import { apiFetch } from '@/lib/api'
import type { ProjectsResponse } from '@/types/project'
import type { JournalResponse } from '@/types/journal'

// ISR — revalidate every 60 seconds
export const revalidate = 60

async function getFeaturedProjects() {
  try {
    const data = await apiFetch<ProjectsResponse>('/projects/featured?limit=5')
    return data.projects
  } catch {
    return []
  }
}

async function getLatestJournal() {
  try {
    const data = await apiFetch<JournalResponse>('/journal?page=1&per=3')
    return data.entries
  } catch {
    return []
  }
}

export default async function HomePage() {
  const [projects, entries] = await Promise.all([
    getFeaturedProjects(),
    getLatestJournal(),
  ])

  return (
    <Layout heroPage>
      {/* 01 Hero */}
      <Hero />

      {/* 02 Featured Projects */}
      <ProjectsGrid
        projects={projects}
        variant="featured"
        showViewAll
        limitmobile = {true}
      />

      {/* 03 About */}
      <AboutBlock />

      {/* 04 Journal preview */}
      <JournalList entries={entries} limit={3} showViewAll />

      {/* 05 Toolbox */}
      <ToolboxLoop />

      {/* 06 Soft Skills */}
      <SoftSkills />

    </Layout>
  )
}