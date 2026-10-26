import { apiFetch } from "@/lib/api";
import type { TestimonialsPayload } from "@/types/testimonial";

const EMPTY: TestimonialsPayload = {
  testimonials: [],
  summary: { count: 0, average: null },
};

// tag 'testimonials' dibersihkan oleh webhook Laravel saat testimoni diaktifkan atau dihapus
export async function getTestimonials(): Promise<TestimonialsPayload> {
  try {
    return await apiFetch<TestimonialsPayload>("/testimonials?per=50", [
      "testimonials",
    ]);
  } catch (e) {
    console.error("[testimonials] gagal:", e);
    return EMPTY;
  }
}

export async function getProjectTestimonials(
  slug: string,
): Promise<TestimonialsPayload> {
  try {
    return await apiFetch<TestimonialsPayload>(
      `/projects/${encodeURIComponent(slug)}/testimonials?per=24`,
      ["testimonials"],
    );
  } catch (e) {
    console.error("[testimonials] gagal:", e);
    return EMPTY;
  }
}
