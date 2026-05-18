"use client";
// src/components/sections/AboutBlock.tsx

import { useEffect, useRef } from "react";
import Link from "next/link";
import { gsap, ScrollTrigger } from "@/lib/gsap";
import Image from "next/image";

export default function AboutBlock() {
  const sectionRef = useRef<HTMLElement>(null);

  useEffect(() => {
    const ctx = gsap.context(() => {
      gsap.from(".about-animate", {
        y: 40,
        opacity: 0,
        duration: 0.9,
        stagger: 0.1,
        ease: "power3.out",
        scrollTrigger: {
          trigger: sectionRef.current,
          start: "top 75%",
        },
      });
    }, sectionRef);
    return () => ctx.revert();
  }, []);

  return (
    <section
      ref={sectionRef}
      className="py-24 px-8 md:px-16 border-t border-slate"
    >
      <div className="max-w-8xl">
        {/* Eyebrow */}
        <p className="about-animate font-body text-xs uppercase tracking-wide2 text-sand/80 mb-6">
          Who I am
        </p>

        {/* Headline */}
        <div className="flex flex-col md:flex-row items-center place-content-between gap-10">
          <div className="w-fit">
            <h2 className="w-fit about-animate font-display font-black text-display-md uppercase text-sand leading-tight mb-8">
              Let's talk
              <br />
              <span className="text-stone">about</span> building
              <br />
              things that work.
            </h2>

            <div className="about-animate max-w-xl space-y-4">
              <p className="font-body text-base text-ash leading-relaxed">
                I'm Mohammad David — a full-stack developer and IoT engineer
                based in Malang, Indonesia. I build systems that sit at the
                intersection of clean code and considered design.
              </p>

              <p className="font-body text-base text-ash leading-relaxed">
                My work spans web platforms, embedded systems, and the editorial
                layer that makes both legible to the people using them. I care
                about the gap between how things are built and how they feel to
                use.
              </p>
            </div>

            <div className="about-animate mt-10 flex flex-wrap gap-4">
              <Link
                href="/about"
                className="underline-offset-4 font-body text-xs uppercase tracking-wide2 text-sand border border-stone/60 px-8 py-3 hover:bg-stone/15 transition-all duration-300 ease-editorial"
              >
                Full Profile
              </Link>

              <a
                href={process.env.NEXT_PUBLIC_CV_URL ?? "#"}
                target="_blank"
                rel="noopener noreferrer"
                download
                className="underline-offset-4 font-body text-xs uppercase tracking-wide2 text-sand hover:text-ash transition-colors duration-300 flex items-center gap-2"
              >
                Download CV <span aria-hidden="true">↓</span>
              </a>
            </div>
          </div>

          <div className="relative h-60 md:h-[450px] w-full md:w-[600px] overflow-hidden ">
            <Image
              src="/images/hero-bg.jpg"
              alt=""
              fill
              priority
              sizes="(max-width: 768px) 100vw, 400px"
              className="object-cover"
              style={{ filter: "brightness(0.3) saturate(0.7)" }}
            />
          </div>
        </div>
      </div>
    </section>
  );
}
