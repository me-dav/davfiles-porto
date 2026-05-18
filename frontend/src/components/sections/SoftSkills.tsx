"use client";
// src/components/sections/SoftSkills.tsx

import { useState, useEffect, useRef } from "react";
import { gsap, ScrollTrigger } from "@/lib/gsap";

interface Skill {
  skill: string;
  example: string;
}

const SKILLS: Skill[] = [
  {
    skill: "Leadership",
    example:
      "Led a community organization with 50 active members and managed large-scale social programs involving volunteers, partnerships, and cross-division coordination.",
  },
  {
    skill: "Teamwork",
    example:
      "Collaborated in multiple team-based web and IoT development projects consisting of 2–4 developers using different technology stacks and responsibilities.",
  },
  {
    skill: "Communication",
    example:
      "Coordinated volunteers, organizational members, and external partners during social activities, project planning, and collaborative development processes.",
  },
  {
    skill: "Problem\nSolving",
    example:
      "Handled technical and operational challenges during web deployment, API integration, IoT system development, and event execution under limited resources and timelines.",
  },
  {
    skill: "Time\nManagement",
    example:
      "Balanced academic responsibilities, organizational leadership, and multiple development projects simultaneously while meeting project deadlines.",
  },
  {
    skill: "Adaptability",
    example:
      "Learned and implemented various technologies independently including Laravel, Next.js, Flutter, Docker, Linux VPS deployment, and IoT integration.",
  },
  {
    skill: "Responsibility",
    example:
      "Managed production deployment, VPS configuration, and backend systems independently for several real-world organizational platforms.",
  },
  {
    skill: "Critical\nThinking",
    example:
      "Analyzed system requirements and designed solutions for real-world cases such as smart locker monitoring, publication request workflows, and donation management systems.",
  },
];

function SoftSkillCard({ skill, example }: Skill) {
  return (
    <div
      className="group relative h-56 cursor-pointer select-none"
      style={{ perspective: '1200px' }}
    >
      <div
        className="absolute inset-0 transition-transform duration-500"
        style={{
          transformStyle: 'preserve-3d',
        }}
      >
        {/* Front */}
        <div
          className="
            absolute inset-0
            border border-slate bg-graphite
            flex flex-col items-center justify-center gap-4 p-6
            transition-all duration-500
            group-hover:[transform:rotateY(180deg)]
          "
          style={{ backfaceVisibility: 'hidden' }}
        >
          <h3 className="font-display font-black text-[2.2rem] uppercase leading-tight text-sand text-center tracking-tight whitespace-pre-line">
            {skill}
          </h3>

          <span className="font-body text-[10px] uppercase tracking-wide2 text-ash/80">
            hover to flip
          </span>
        </div>

        {/* Back */}
        <div
          className="
            absolute inset-0
            border border-ash/20 bg-slate
            flex items-center justify-center p-7
            [transform:rotateY(180deg)]
            group-hover:[transform:rotateY(360deg)]
            transition-all duration-500
          "
          style={{ backfaceVisibility: 'hidden' }}
        >
          <p className="font-body text-sm text-ash text-center leading-relaxed">
            {example}
          </p>
        </div>
      </div>
    </div>
  )
}

export default function SoftSkills() {
  const sectionRef = useRef<HTMLElement>(null);

  useEffect(() => {
    const ctx = gsap.context(() => {
      gsap.from(".skill-card", {
        y: 40,
        opacity: 0,
        duration: 0.7,
        stagger: 0.1,
        ease: "power3.out",
        scrollTrigger: {
          trigger: sectionRef.current,
          start: "top 80%",
        },
      });
    }, sectionRef);
    return () => ctx.revert();
  }, []);

  return (
    <section
      ref={sectionRef}
      className="py-20 px-8 md:px-16 border-t border-slate "
    >
      <div className="flex items-end justify-between mb-10">
        <h2 className="font-display font-black text-display-md uppercase text-sand">
          Soft Skills
        </h2>
        <p className="font-body text-xs text-sand/80 hidden md:block md:mb-12">
          Hover each card to reveal the story.
        </p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {SKILLS.map((s) => (
          <div key={s.skill} className="skill-card">
            <SoftSkillCard {...s} />
          </div>
        ))}
      </div>
    </section>
  );
}
