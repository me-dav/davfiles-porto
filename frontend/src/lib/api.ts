// src/lib/api.ts
import axios from "axios";

const api = axios.create({
  baseURL:
    (process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000") + "/api/v1",
  headers: { "Content-Type": "application/json" },
  timeout: 10000,
});

// Attach auth token on every request (client-side only)
api.interceptors.request.use((config) => {
  if (typeof window !== "undefined") {
    const token = localStorage.getItem("auth_token");
    if (token) config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Unwrap the {success, data, message} envelope
// Returns res.data.data so callers get the inner payload directly
api.interceptors.response.use(
  (res) => res,
  (err) => {
    const message =
      err.response?.data?.error ??
      err.response?.data?.message ??
      "Something went wrong";
    return Promise.reject(new Error(message));
  },
);

export default api;

// ─── Typed fetchers (used in server components / ISR) ────────────────────────

const PUBLIC_API = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000";
// di server (container web) pakai alamat internal Docker, di browser pakai URL publik
const ROOT =
  typeof window === "undefined"
    ? (process.env.API_INTERNAL_URL ?? PUBLIC_API)
    : PUBLIC_API;
const BASE = `${ROOT}/api/v1`;

export async function apiFetch<T>(
  path: string,
  tags: string[] = [],
): Promise<T> {
  const res = await fetch(`${BASE}${path}`, {
    headers: { Accept: "application/json" },
    next: { revalidate: 60, tags },
    signal: AbortSignal.timeout(10000),
  });
  const json = await res.json().catch(() => null);
  if (!res.ok || !json?.success) {
    throw new Error(json?.error ?? `API error ${res.status} on ${path}`);
  }
  return json.data as T;
}
