'use client'
// src/app/dashboard/projects/[id]/edit/page.tsx

import { useEffect, useState } from 'react'
import { useParams } from 'next/navigation'
import api from '@/lib/api'
import ProjectForm from '@/components/dashboard/ProjectForm'
import type { Project } from '@/types/project'

export default function EditProjectPage() {
  const { id }                    = useParams<{ id: string }>()
  const [project, setProject]     = useState<Project | null>(null)
  const [loading, setLoading]     = useState(true)
  const [error,   setError]       = useState('')

  useEffect(() => {
    api.get(`/projects?per=50`)
      .then((res) => {
        const found = (res.data.data.projects as Project[]).find((p) => p.id === Number(id))
        if (found) setProject(found)
        else setError('Project not found')
      })
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }, [id])

  if (loading) return <div className="p-12"><p className="font-body text-xs text-stone animate-pulse">Loading…</p></div>
  if (error)   return <div className="p-12"><p className="font-body text-xs text-red-400">{error}</p></div>
  if (!project) return null

  return (
    <div className="p-8 md:p-12">
      <h1 className="font-display font-black text-3xl uppercase text-sand mb-2">Edit Project</h1>
      <p className="font-body text-xs text-stone uppercase tracking-wide2 mb-10">{project.name}</p>
      <ProjectForm initial={project} projectId={project.id} />
    </div>
  )
}