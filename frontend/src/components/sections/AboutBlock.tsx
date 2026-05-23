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
              <p className="font-body text-base text-ash leading-relaxed text-justify">
                I’m Mohammad David — a technology enthusiast focused on web
                development and IoT systems, based in Malang, Indonesia. I build
                digital products that prioritize clarity, performance, and
                real-world usability.
              </p>

              <p className="font-body text-base text-ash leading-relaxed text-justify">
                My work lives across frontend interfaces, backend logic, and
                connected devices — from responsive web apps to experimental IoT
                projects. I’m currently exploring modern web stacks like React
                and Next.js, while strengthening my fundamentals in system
                design and scalable architecture.
              </p>
              <p className="font-body text-base text-ash leading-relaxed text-justify">
                What drives me is not just building things that work, but
                building things that make sense — for users, for developers, and
                for the systems behind them.
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
                href={process.env.NEXT_PUBLIC_CV_In_URL ?? "#"}
                target="_blank"
                rel="noopener noreferrer"
                download = "Mohammad-David-resume.pdf"
                className="p-2 underline-offset-4 font-body text-xs uppercase tracking-wide2 text-sand hover:text-ash transition-colors duration-300 flex items-center gap-2"
              >
                CV IND Version <span aria-hidden="true">↓</span>
              </a>
              <a
                href={process.env.NEXT_PUBLIC_CV_En_URL ?? "#"}
                target="_blank"
                rel="noopener noreferrer"
                download = "Mohammad-David-resume.pdf"
                className="p-2 underline-offset-4 font-body text-xs uppercase tracking-wide2 text-sand hover:text-ash transition-colors duration-300 flex items-center gap-2"
              >
                CV English Version <span aria-hidden="true">↓</span>
              </a>
            </div>
          </div>

          <div className="relative h-60 md:h-[450px] w-full md:w-[600px] overflow-hidden ">
            <Image
              src="/images/bg-mt.jpeg"
              alt=""
              fill
              priority
              sizes="(max-width: 768px) 100vw, 400px"
              className="object-cover"
              style={{ filter: "brightness(0.6) saturate(0.8)" }}
            />
          </div>
        </div>
      </div>
    </section>
  );
}
