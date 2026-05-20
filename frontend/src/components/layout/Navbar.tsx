"use client";
// src/components/layout/Navbar.tsx

import { useState, useEffect, useRef, useCallback } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { gsap } from "@/lib/gsap";

const NAV_ITEMS = [
  { label: "Home", href: "/" },
  { label: "Work", href: "/work" },
  { label: "Journal", href: "/journal" },
  { label: "About", href: "/about" },
];

interface NavbarProps {
  /** When true, navbar sits fixed over a full-bleed hero */
  overlaid?: boolean;
}

export default function Navbar({ overlaid = false }: NavbarProps) {
  const [open, setOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const panelRef = useRef<HTMLDivElement>(null);
  const overlayRef = useRef<HTMLDivElement>(null);
  const pathname = usePathname();

  // Close panel on route change
  useEffect(() => {
    setOpen(false);
  }, [pathname]);

  // Scroll shadow
  useEffect(() => {
    const handler = () => setScrolled(window.scrollY > 40);
    window.addEventListener("scroll", handler, { passive: true });
    return () => window.removeEventListener("scroll", handler);
  }, []);

  // GSAP panel animation
  useEffect(() => {
    const panel = panelRef.current;
    const overlay = overlayRef.current;
    if (!panel || !overlay) return;

    if (open) {
      gsap.set(panel, { x: "-100%" });
      gsap.set(overlay, { opacity: 0, display: "block" });
      gsap.to(panel, { x: "0%", duration: 0.4, ease: "power3.out" });
      gsap.to(overlay, { opacity: 1, duration: 0.3 });

      // Stagger nav items
      gsap.from(".nav-panel-item", {
        x: -24,
        opacity: 0,
        duration: 0.35,
        stagger: 0.06,
        ease: "power3.out",
        delay: 0.15,
      });
    } else {
      gsap.to(panel, { x: "-100%", duration: 0.3, ease: "power3.in" });
      gsap.to(overlay, {
        opacity: 0,
        duration: 0.25,
        onComplete: () => gsap.set(overlay, { display: "none" }),
      });
    }
  }, [open]);

  // Focus trap
  const handleKeyDown = useCallback(
    (e: KeyboardEvent) => {
      if (!open) return;
      if (e.key === "Escape") setOpen(false);
    },
    [open],
  );

  useEffect(() => {
    document.addEventListener("keydown", handleKeyDown);
    return () => document.removeEventListener("keydown", handleKeyDown);
  }, [handleKeyDown]);

  // Lock body scroll when panel open
  useEffect(() => {
    document.body.style.overflow = open ? "hidden" : "";
    return () => {
      document.body.style.overflow = "";
    };
  }, [open]);

  return (
    <>
      {/* ── Top bar ── */}
      <nav
        className={`
          fixed top-0 left-0 right-0 z-40
          flex items-center justify-between
          px-8 md:px-12 py-5
          transition-all duration-300
          ${scrolled && !overlaid ? "bg-ink/90 backdrop-blur-md border-b border-slate/40" : ""}
        `}
        aria-label="Main navigation"
      >
        <Link
          href="/"
          className="font-display font-black text-sm tracking-wide3 text-sand uppercase hover:text-ash transition-colors"
        >
          Dav-Files
        </Link>

        <button
          onClick={() => setOpen(true)}
          aria-label="Open navigation menu"
          aria-expanded={open}
          aria-controls="nav-panel"
          className="flex flex-col gap-[5px] group p-2 -mr-2 bg-transparent border-none"
        >
          <span className="block w-8 h-px bg-sand group-hover:w-6 transition-all duration-300 ease-editorial" />
          <span className="block w-6 h-px bg-sand group-hover:w-8 transition-all duration-300 ease-editorial" />
        </button>
      </nav>

      {/* ── Backdrop overlay ── */}
      <div
        ref={overlayRef}
        onClick={() => setOpen(false)}
        className="fixed inset-0 z-40 bg-ink/70 backdrop-blur-sm hidden"
        aria-hidden="true"
      />

      {/* ── Side panel ── */}
      <div
        ref={panelRef}
        id="nav-panel"
        role="dialog"
        aria-modal="true"
        aria-label="Navigation"
        className="fixed left-0 top-0 z-50 h-full w-80 bg-graphite border-r border-slate flex flex-col justify-between p-10"
        style={{ transform: "translateX(-100%)" }}
      >
        {/* Close */}
        <button
          onClick={() => setOpen(false)}
          aria-label="Close navigation"
          className="self-end text-stone hover:text-sand hover:bg-stone transition-colors text-lg leading-none py-1 px-2 outline-none border-none"
        >
          ✕
        </button>

        {/* Nav items */}
        <nav className="flex flex-col gap-6 mt-8" aria-label="Site pages">
          {NAV_ITEMS.map((item) => {
            const active = pathname.startsWith(item.href);
            return (
              <Link
                key={item.href}
                href={item.href}
                className={`
                  nav-panel-item group relative inline-block w-fit
                  font-display font-black text-[3.5rem] uppercase
                  leading-none tracking-tight transition-colors duration-200
                  no-underline
                  ${active ? "text-sand" : "text-stone hover:text-sand"}
                `}
              >
                {item.label}

                <span
                  className="
                    absolute left-0 top-1/2 h-[8px] w-0 bg-red-500
      transition-all duration-300 ease-out
      group-hover:w-full
                  "
                />
              </Link>
            );
          })}
        </nav>

        {/* Footer of panel */}
        <div className="flex flex-col gap-3">
          <div className="h-px bg-slate" />
          <p className="font-body text-xs text-stone tracking-editorial">
            Mohammad David Nur Syahfrudin
          </p>
          <p className="font-body text-xs text-stone/60">
            © {new Date().getFullYear()}
          </p>
        </div>
      </div>
    </>
  );
}
