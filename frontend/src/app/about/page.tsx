// src/app/about/page.tsx
import type { Metadata } from "next";
import Layout from "@/components/layout/Layout";
import ContactBlock from "@/components/sections/ContactBlock";

export const metadata: Metadata = {
  title: "About",
  description:
    "Mohammad David Nur Syahfrudin — Full-Stack Developer, IoT Engineer, Editorial Designer.",
};

export default function AboutPage() {
  const cvInUrl = process.env.NEXT_PUBLIC_CV_In_URL ?? "#";
  const cvEnUrl = process.env.NEXT_PUBLIC_CV_En_URL ?? "#";

  return (
    <Layout>
      {/* ── Header ── */}
      <section className="px-8 md:px-16 pt-16 pb-20 border-b border-slate">
        <p className="font-body text-[10px] uppercase tracking-wide2 text-ash mb-6">
          Profile
        </p>
        <h1 className="font-display font-black text-display-lg uppercase text-sand leading-none mb-12">
          Mohammad
          <br />
          <span className="text-ash">David Nur</span>
          <br />
          <span className="text-stone">Syahfrudin</span>
        </h1>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-4xl">
          <div className="space-y-5">
            <p className="font-body text-base text-ash leading-relaxed text-justify">
              Full-Stack Developer and IoT enthusiast based in Malang,
              Indonesia. I build systems that sit at the intersection of clean
              engineering and thoughtful user experience.
            </p>
            <p className="font-body text-base text-ash leading-relaxed text-justify">
              My work spans web development (Next.js, Laravel), foundational
              backend systems, and early explorations in embedded IoT. I’m
              focused on strengthening my fundamentals while building real
              projects that connect code with practical use.
            </p>
            <p className="font-body text-base text-ash leading-relaxed text-justify">
              I care about the gap between how things are built and how they
              feel to use. Good tools don’t try to be noticed — they just work
              and get out of the way.
            </p>
          </div>

          {/* Quick facts */}
          <div className="space-y-6">
            {[
              { label: "Location", value: "Malang, East Java, Indonesia" },
              { label: "Focus", value: "Web · IoT · Dev-Ops" },
              { label: "Stack", value: "Next.js · Laravel · Python · C++" },
              { label: "Available", value: "Open for freelance & Work" },
            ].map(({ label, value }) => (
              <div key={label} className="border-b border-slate pb-4">
                <p className="font-body text-[10px] uppercase tracking-wide2 text-stone mb-1">
                  {label}
                </p>
                <p className="font-body text-sm text-ash">{value}</p>
              </div>
            ))}

            {/* CV download */}
              <a
                href={cvInUrl}
                target="_blank"
                rel="noopener noreferrer"
                download = "mohammad-David-Resume.pdf"
                className="inline-flex items-center gap-3 font-body text-xs uppercase tracking-wide2 text-sand border border-stone/60 px-4 py-3 hover:bg-stone/15 transition-all"
              >
                CV IND Version <span aria-hidden="true">↓</span>
              </a>
              <a
                href={cvEnUrl}
                target="_blank"
                rel="noopener noreferrer"
                download = "mohammad-David-Resume.pdf"
                className="inline-flex items-center gap-3 font-body text-xs uppercase tracking-wide2 text-sand border border-stone/60 px-4 py-3 hover:bg-stone/15 transition-all"
              >
                CV English Version <span aria-hidden="true">↓</span>
              </a>

          </div>
        </div>
      </section>

      {/* ── Experience ── */}
      <section className="px-8 md:px-16 py-20 border-b border-slate">
        <h2 className="font-display font-black text-display-md uppercase text-sand mb-12">
          Experience
        </h2>
        <div className="space-y-10 max-w-2xl">
          {[
            {
              role: "Full-Stack Developer",
              company: "Personal Projects",
              period: "2026",
              summary:
                "Designed and built web platforms, IoT dashboards, and editorial tools for clients across Indonesia.",
            },
            {
              role: "IoT Engineer",
              company: "Personal Projects",
              period: "2026",
              summary:
                "Developed MQTT-based real-time control systems, embedded firmware in C++, and data pipelines in Python.",
            },
          ].map(({ role, company, period, summary }) => (
            <div key={role} className="border-l border-slate pl-8 relative">
              <div className="absolute -left-[3px] top-1 w-[5px] h-[5px] rounded-full bg-stone" />
              <p className="font-body text-[10px] uppercase tracking-wide2 text-stone mb-1">
                {period}
              </p>
              <h3 className="font-display font-black text-xl uppercase text-sand">
                {role}
              </h3>
              <p className="font-body text-xs uppercase tracking-editorial text-stone mb-3">
                {company}
              </p>
              <p className="font-body text-sm text-ash leading-relaxed">
                {summary}
              </p>
            </div>
          ))}
        </div>
      </section>
    </Layout>
  );
}
