"use client";
// src/components/sections/Hero.tsx

import { useEffect, useRef } from "react";
import Image from "next/image";
import { gsap, ScrollTrigger } from "@/lib/gsap";
import { sub } from "framer-motion/client";

export default function Hero() {
  const sectionRef = useRef<HTMLElement>(null);
  const bgRef = useRef<HTMLDivElement>(null);
  const headRef = useRef<HTMLHeadingElement>(null);
  const subRef = useRef<HTMLParagraphElement>(null);
  const subheadRef = useRef<HTMLParagraphElement>(null);
  const ctaRef = useRef<HTMLDivElement>(null);
  const lineRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const ctx = gsap.context(() => {
      // Entrance timeline
      const tl = gsap.timeline({ delay: 0.2 });
      tl.from(headRef.current, {
        y: 90,
        opacity: 0,
        duration: 1.2,
        ease: "power4.out",
      })
        .from(
          subRef.current,
          {
            y: 20,
            opacity: 0,
            duration: 0.8,
            ease: "power3.out",
          },
          "-=0.6",
        )
        .from(
          subheadRef.current,
          {
            y: 20,
            opacity: 0,
            duration: 0.8,
            ease: "power3.out",
          },
          "-=0.6",
        )
        .from(
          lineRef.current,
          {
            scaleX: 0,
            duration: 0.6,
            ease: "power3.inOut",
            transformOrigin: "left",
          },
          "-=0.5",
        )
        .from(
          ctaRef.current,
          {
            y: 10,
            opacity: 0,
            duration: 0.5,
            ease: "power3.out",
          },
          "-=0.1",
        );

      // Subtle parallax on bg
      if (bgRef.current) {
        gsap.to(bgRef.current, {
          y: "20%",
          ease: "none",
          scrollTrigger: {
            trigger: sectionRef.current,
            start: "top top",
            end: "bottom top",
            scrub: true,
          },
        });
      }
    }, sectionRef);

    return () => ctx.revert();
  }, []);

  return (
    <section
      ref={sectionRef}
      className="relative flex md:h-screen min-h-[300px] items-end pb-16 md:pb-24 px-6 sm:px-8 md:px-16 overflow-hidden"
    >
      {/* ── Background photo ── */}
      {/* Place your photo at: public/images/hero-bg.jpg */}
      {/* Recommended: 2560×1440, dark/moody — architecture, nature, city */}
      <div ref={bgRef} className="absolute inset-0 md:scale-110">
        <Image
          src="/images/hero-bg.jpg"
          alt=""
          fill
          priority
          sizes="md:100vw"
          className="object-contain md:object-cover md:px-4 md:py-2"
          style={{ filter: "brightness(0.28) saturate(0.7)" }}
        />
      </div>

      {/* ── Vignette ── */}
      <div className="absolute inset-0 bg-vignette-bottom" aria-hidden="true" />
      {/* Side vignette */}
      <div
        className="absolute inset-0"
        style={{
          background:
            "linear-gradient(to right, rgba(14,14,15,0.4) 0%, transparent 40%, transparent 70%, rgba(14,14,15,0.3) 100%)",
        }}
        aria-hidden="true"
      />

      {/* ── Content ── */}
      <div className="relative z-10 max-w-6xl w-full">
        {/* Headline */}
        <h1
          ref={headRef}
          className="font-display font-black text-4xl md:text-display-xl uppercase text-sand leading-none mb-0"
        >
          Designing
          <br />
          <span className="text-ash">Systems.</span>
        </h1>

        {/* Eyebrow */}
        <p ref={subRef} className="font-body font-semibold px-2 text-sand">
          Intesert About
        </p>
        <p
          ref={subheadRef}
          className="font-body text-[10px] sm:text-xs uppercase tracking-wide3 text-ash px-2 mb-4 md:mb-6 flex flex-wrap gap-x-2"
        >
          <span>Full-Stack Developer</span>
          <span className="text-stone">·</span>
          <span>IoT</span>
          <span className="text-stone">·</span>
          <span>Dev-ops</span>
        </p>

        {/* Divider line */}
        <div
          ref={lineRef}
          className="mt-6 ml-2 md:mt-8 mb-4 md:mb-6 w-20 h-px bg-stone"
          aria-hidden="true"
        />
      </div>

      {/* Scroll indicator — hidden on mobile */}
      <div
        ref={ctaRef}
        className="hidden md:flex md:justify-between md:w-full absolute bottom-8 right-16 md:right-0 md:px-10 items-center md:ml-10"
        aria-hidden="true"
      >
        <a
          href="#projects"
          className="
            inline-flex items-center gap-3
            font-body text-xs uppercase tracking-wide2 text-sand
            border border-stone/60 px-6 md:px-8 py-3
            hover:bg-stone/15 hover:border-ash/60
            transition-all duration-300 ease-editorial underline-offset-4
          "
        >
          View Work
          <span aria-hidden="true" className="text-stone"></span>
        </a>

        <span className="font-body text-[10px] uppercase tracking-wide3 text-sand/60 rotate-90 origin-center translate-y-2">
          scroll
        </span>
      </div>
    </section>
  );
}
