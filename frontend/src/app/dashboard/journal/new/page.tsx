// src/app/dashboard/journal/new/page.tsx
import JournalForm from '@/components/dashboard/JournalForm'

export default function NewJournalPage() {
  return (
    <div className="p-8 md:p-12">
      <h1 className="font-display font-black text-3xl uppercase text-sand mb-2">New Entry</h1>
      <p className="font-body text-xs text-stone uppercase tracking-wide2 mb-10">Write something worth keeping</p>
      <JournalForm />
    </div>
  )
}