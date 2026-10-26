export interface Testimonial {
  id: number;
  name: string;
  rating: number;
  body: string;
  created_at: string;
  email_hint: { initial: string; domain: string };
  project?: { name: string; slug: string };
}

export interface TestimonialSummary {
  count: number;
  average: number | null;
}

export interface TestimonialsPayload {
  testimonials: Testimonial[];
  summary: TestimonialSummary;
}
