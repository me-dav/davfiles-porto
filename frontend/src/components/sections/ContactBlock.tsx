"use client";
// src/components/sections/ContactBlock.tsx

import { useState, useRef, useEffect } from "react";
import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import api from "@/lib/api";
import Link from "next/link";

gsap.registerPlugin(ScrollTrigger);

type Status = "idle" | "sending" | "sent" | "error";

const NAV = [
  { label: 'Work',    href: '/work' },
  { label: 'Journal', href: '/journal' },
  { label: 'About',   href: '/about' },
]

const SOCIALS = [
  { label: "GitHub", href: "https://github.com/me-dav" },
  {
    label: "LinkedIn",
    href: "https://linkedin.com/in/mohammad-david-nur-syahfrudin-87ab3b321",
  },
  { label: "Instagram", href: "https://instagram.com/davv_id._" },
  { label: "TikTok", href: "https://tiktok.com/@iam.davv" },
];

export default function ContactBlock() {
  const sectionRef = useRef<HTMLElement>(null);
  const [form, setForm] = useState({ name: "", email: "", message: "" });
  const [status, setStatus] = useState<Status>("idle");
  const [error, setError] = useState("");

  useEffect(() => {
    const ctx = gsap.context(() => {
      gsap.from(".contact-animate", {
        y: 36,
        opacity: 0,
        duration: 0.8,
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

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setStatus("sending");
    setError("");
    try {
      await api.post("/contact", form);
      setStatus("sent");
      setForm({ name: "", email: "", message: "" });
    } catch (err: any) {
      setStatus("error");
      setError(
        err.message ?? "Something went wrong. Try emailing me directly.",
      );
    }
  };

  return (
    <section
      ref={sectionRef}
      id="contact"
      className="py-24 px-8 md:px-16 border-t border-slate mb-0"
    >
      <div className="grid grid-cols-1 lg:grid-cols-3 place-content-around md:gap-16 w-full">
        {/* Left — heading + socials */}
        <div className="">
          <p className="contact-animate font-body text-[10px] uppercase tracking-wide2 text-sand mb-0">
            Get in touch
          </p>
          <h2 className="contact-animate font-display font-black text-display-md uppercase text-sand leading-0 mb-8 mt-4">
            Let's
            <br />
            Connect.
          </h2>
        </div>
        <div className="contact-animate flex flex-col gap-3">
          <a
            href="https://mail.google.com/mail/u/0/#inbox?compose=DmwnWtVbDkxgRcdJFbdwhFjkXMmQkqcbgxWQbCGdGsRtkVTZJlZhgLPvkVssGGJKPhmvktFPTwfQ"
            target="_blank"
            className="font-body text-sm text-ash hover:text-sand transition-colors group flex items-center gap-2 no-underline"
          >
            <span className="text-stone text-xs">→</span>
            davidmohammadns@gmail.com
          </a>
          {SOCIALS.map((s) => (
            <a
              key={s.label}
              href={s.href}
              target="_blank"
              rel="noopener noreferrer"
              className="font-body text-sm text-stone hover:text-sand transition-colors group flex items-center gap-2 no-underline"
            >
              <span className="text-stone/40 text-xs">→</span>
              {s.label}
            </a>
          ))}
        </div>
        <nav className="hidden contact-animate md:flex flex-col gap-6" aria-label="Footer navigation">
          {NAV.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className="font-body text-[10px] uppercase tracking-wide2 text-stone hover:text-sand transition-colors underline-offset-4"
            >
              {item.label}
            </Link>
          ))}
        </nav>
      </div>
    </section>
  );
}
