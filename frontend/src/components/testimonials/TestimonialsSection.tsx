import type { Testimonial, TestimonialSummary } from "@/types/testimonial";
import TestimonialCard from "./TestimonialCard";

function Row({
  items,
  reverse,
  showProject,
}: {
  items: Testimonial[];
  reverse?: boolean;
  showProject: boolean;
}) {
  const dur = Math.max(40, items.length * 9); // makin banyak kartu, makin lama satu putaran
  return (
    <div className="t-marquee">
      <div
        className={`t-track ${reverse ? "t-reverse" : ""}`}
        style={{ "--t-dur": `${dur}s` } as React.CSSProperties}
      >
        {items.map((t) => (
          <div key={t.id} className="t-item">
            <TestimonialCard t={t} showProject={showProject} />
          </div>
        ))}
        {items.map((t) => (
          <div key={`dup-${t.id}`} className="t-item t-dup" aria-hidden="true">
            <TestimonialCard t={t} showProject={showProject} decorative />
          </div>
        ))}
      </div>
    </div>
  );
}

export default function TestimonialsSection({
  items,
  summary,
  showProject = true,
  forceStatic = false,
  title = "What clients say",
}: {
  items: Testimonial[];
  summary: TestimonialSummary;
  showProject?: boolean;
  forceStatic?: boolean;
  title?: string;
}) {
  const n = items.length;
  if (n === 0) return null; // belum ada testimoni: seksi tidak muncul

  const mode = forceStatic || n <= 5 ? "static" : n <= 11 ? "one" : "two";
  const avg =
    summary.average !== null
      ? summary.average.toLocaleString("id-ID", {
          minimumFractionDigits: 1,
          maximumFractionDigits: 1,
        })
      : null;

  return (
    <section aria-labelledby="testimonials-title" className="py-20">
      <header className="mb-10 flex flex-wrap items-end justify-between gap-6 px-8 md:px-16">
        <div>
          <p className="font-body text-[10px] uppercase tracking-wide2 text-stone">
            Testimonials
          </p>
          <h2
            id="testimonials-title"
            className="font-display mt-3 text-4xl font-black uppercase leading-none text-sand md:text-6xl"
          >
            {title}
          </h2>
        </div>
        {avg && (
          <p className="font-display leading-none text-sand">
            <span className="text-5xl">{avg}</span>
            <span className="ml-1 text-sm text-stone">/5</span>
            <span className="font-body mt-2 block text-[10px] uppercase tracking-wide2 text-stone">
              from {summary.count} {summary.count === 1 ? "client" : "clients"}
            </span>
          </p>
        )}
      </header>

      {mode === "static" && (
        <div className="px-8 md:px-16">
          <ul
            className={
              n === 1
                ? "max-w-xl"
                : "-mx-8 flex snap-x snap-mandatory gap-4 overflow-x-auto px-8 pb-2 md:mx-0 md:grid md:grid-cols-2 md:gap-6 md:overflow-visible md:px-0 md:pb-0 lg:grid-cols-3"
            }
          >
            {items.map((t) => (
              <li
                key={t.id}
                className={
                  n === 1
                    ? "flex"
                    : "flex w-[78%] shrink-0 snap-start sm:w-[60%] md:w-auto"
                }
              >
                <TestimonialCard t={t} showProject={showProject} />
              </li>
            ))}
          </ul>
        </div>
      )}

      {mode === "one" && <Row items={items} showProject={showProject} />}

      {mode === "two" && (
        <div className="space-y-6">
          {/* bagi selang-seling supaya kedua baris sama banyak */}
          <Row
            items={items.filter((_, i) => i % 2 === 0)}
            showProject={showProject}
          />
          <Row
            items={items.filter((_, i) => i % 2 === 1)}
            showProject={showProject}
            reverse
          />
        </div>
      )}
    </section>
  );
}
