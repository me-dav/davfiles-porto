"use client";

import { useEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";
import Link from "next/link";
import type { Testimonial } from "@/types/testimonial";
import RatingMeter from "./RatingMeter";
import EmailHint from "./EmailHint";

// zona waktu dikunci agar server dan browser menghasilkan tanggal yang sama
const fmt = new Intl.DateTimeFormat("en-GB", {
  day: "numeric",
  month: "short",
  year: "numeric",
  timeZone: "Asia/Jakarta",
});

function Body({
  t,
  clamped,
  pRef,
}: {
  t: Testimonial;
  clamped: boolean;
  pRef?: React.Ref<HTMLParagraphElement>;
}) {
  return (
    <>
      <div className="flex items-start justify-between gap-3">
        <span
          aria-hidden="true"
          className="font-display text-4xl leading-none text-white/30 md:text-5xl"
        >
          “
        </span>
        <RatingMeter value={t.rating} />
      </div>
      <p
        ref={pRef}
        className={`font-body mt-2 whitespace-pre-line text-sm leading-relaxed text-ash md:mt-3 md:text-base ${clamped ? "t-clamp" : ""}`}
      >
        {t.body}
      </p>
    </>
  );
}

function Footer({
  t,
  showProject,
  action,
}: {
  t: Testimonial;
  showProject: boolean;
  action?: React.ReactNode;
}) {
  return (
    <footer className="mt-4 flex items-end justify-between gap-4 border-t border-white/15 pt-3 md:mt-5 md:pt-4">
      {/* kiri: nama, email, project */}
      <div className="min-w-0 flex-1">
        <p className="font-display truncate text-lg uppercase leading-none text-sand md:text-xl">
          {t.name}
        </p>
        <div className="mt-0.5 leading-none">
          <EmailHint
            initial={t.email_hint.initial}
            domain={t.email_hint.domain}
          />
        </div>
        {showProject && t.project && (
          <Link
            href={`/work/${t.project.slug}`}
            className="mt-2 block truncate text-[10px] uppercase tracking-wide2 text-white no-underline underline-offset-4 transition-colors hover:text-white/70 hover:underline md:mt-3 md:text-xs"
          >
            {t.project.name}
          </Link>
        )}
      </div>

      {/* kanan: read more di atas tanggal */}
      <div className="flex shrink-0 flex-col items-end gap-1.5 text-[10px] uppercase tracking-wide2 text-stone md:gap-2 md:text-xs">
        {action}
        <time dateTime={t.created_at}>
          {fmt.format(new Date(t.created_at))}
        </time>
      </div>
    </footer>
  );
}

export default function TestimonialCard({
  t,
  showProject = true,
  decorative = false,
}: {
  t: Testimonial;
  showProject?: boolean;
  decorative?: boolean;
}) {
  const [open, setOpen] = useState(false);
  const [truncated, setTruncated] = useState(false);
  const bodyRef = useRef<HTMLParagraphElement>(null);
  const trigger = useRef<HTMLButtonElement>(null);
  const closeBtn = useRef<HTMLButtonElement>(null);

  // "Read more" hanya muncul kalau teks benar-benar terpotong
  useEffect(() => {
    const el = bodyRef.current;
    if (!el) return;
    const check = () => setTruncated(el.scrollHeight - el.clientHeight > 1);
    check();
    const ro = new ResizeObserver(check);
    ro.observe(el);
    document.fonts?.ready.then(check);
    return () => ro.disconnect();
  }, [t.body]);

  useEffect(() => {
    if (!open) return;
    const onKey = (e: KeyboardEvent) => e.key === "Escape" && setOpen(false);
    const prev = document.body.style.overflow;
    document.addEventListener("keydown", onKey);
    document.body.style.overflow = "hidden";
    closeBtn.current?.focus();
    const btn = trigger.current;
    return () => {
      document.removeEventListener("keydown", onKey);
      document.body.style.overflow = prev;
      btn?.focus();
    };
  }, [open]);

  const readMore = truncated ? (
    <button
      ref={trigger}
      type="button"
      onClick={() => setOpen(true)}
      tabIndex={decorative ? -1 : 0}
      className="cursor-pointer text-stone underline-offset-4 transition-colors hover:text-sand hover:underline"
    >
      Read more
    </button>
  ) : null;

  return (
    <>
      <article className="t-card flex h-full w-full flex-col justify-between overflow-hidden bg-neutral-900 border border-white/25 p-5 transition-colors hover:border-white/50 md:p-7">
        <div className="min-h-0">
          <Body t={t} clamped pRef={bodyRef} />
        </div>
        <Footer t={t} showProject={showProject} action={readMore} />
      </article>

      {open &&
        createPortal(
          <div
            onClick={() => setOpen(false)}
            style={{
              position: "fixed",
              inset: 0,
              zIndex: 9999,
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              padding: "1rem",
              background: "rgba(0,0,0,0.85)",
            }}
          >
            <div
              role="dialog"
              aria-modal="true"
              aria-label={`Testimonial from ${t.name}`}
              onClick={(e) => e.stopPropagation()}
              style={{
                width: "100%",
                maxWidth: "40rem",
                maxHeight: "85vh",
                overflowY: "auto",
                background: "#0E0E0F",
                border: "1px solid rgba(255,255,255,0.25)",
                padding: "1.75rem",
              }}
            >
              <Body t={t} clamped={false} />
              <Footer t={t} showProject={showProject} />
              <button
                ref={closeBtn}
                type="button"
                onClick={() => setOpen(false)}
                className="mt-5 cursor-pointer text-[10px] uppercase tracking-wide2 text-stone transition-colors hover:text-sand"
              >
                Close ✕
              </button>
            </div>
          </div>,
          document.body,
        )}
    </>
  );
}
