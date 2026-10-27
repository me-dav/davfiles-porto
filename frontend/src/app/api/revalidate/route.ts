import { revalidateTag } from "next/cache";
import { NextRequest, NextResponse } from "next/server";
import { timingSafeEqual } from "crypto";

const ALLOWED = ["projects", "journal", "testimonials"];

export async function POST(req: NextRequest) {
  const secret = process.env.REVALIDATE_SECRET ?? "";
  const got = req.headers.get("x-revalidate-secret") ?? "";
  const ok =
    secret.length > 0 &&
    got.length === secret.length &&
    timingSafeEqual(Buffer.from(got), Buffer.from(secret));

  if (!ok) return NextResponse.json({ ok: false }, { status: 401 });

  const body = await req.json().catch(() => ({}));
  const raw: unknown[] = Array.isArray(body?.tags) ? body.tags : [];
  const tags = raw.filter(
    (t): t is string => typeof t === "string" && ALLOWED.includes(t),
  );

  // Next 16 butuh argumen kedua. Next 15 ke bawah mengabaikannya.
  const revalidate = revalidateTag as unknown as (
    tag: string,
    profile?: { expire?: number },
  ) => void;

  for (const t of tags) {
    revalidate(t, { expire: 0 });
  }

  return NextResponse.json({ ok: true, tags });
}
