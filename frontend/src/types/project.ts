// src/types/project.ts
export interface Project {
  id: number
  name: string
  slug: string
  short_description: string
  role: string
  stack: string[]
  year: number
  preview_image_url: string
  live_url?: string | null
  github_url?: string | null
  body?: string
  featured: boolean
  created_at?: string
}

export interface ProjectsResponse {
  projects: Project[]
  meta: { total: number; per_page: number; current_page: number }
}