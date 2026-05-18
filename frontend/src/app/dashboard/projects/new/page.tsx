// src/app/dashboard/projects/new/page.tsx
import ProjectForm from '@/components/dashboard/ProjectForm'

export default function NewProjectPage() {
  return (
    <div className="p-8 md:p-12">
      <h1 className="font-display font-black text-3xl uppercase text-sand mb-2">New Project</h1>
      <p className="font-body text-xs text-stone uppercase tracking-wide2 mb-10">Fill in the details below</p>
      <ProjectForm />
    </div>
  )
}