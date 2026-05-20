// src/lib/api.ts
import axios from 'axios'

const api = axios.create({
  baseURL: (process.env.NEXT_PUBLIC_API_URL ?? 'https://api.davfiles.my.id') + '/api/v1',
  headers: { 'Content-Type': 'application/json' },
  timeout: 10000,
})

// Attach auth token on every request (client-side only)
api.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    const token = localStorage.getItem('auth_token')
    if (token) config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Unwrap the {success, data, message} envelope
// Returns res.data.data so callers get the inner payload directly
api.interceptors.response.use(
  (res) => res,
  (err) => {
    const message =
      err.response?.data?.error ??
      err.response?.data?.message ??
      'Something went wrong'
    return Promise.reject(new Error(message))
  }
)

export default api

// ─── Typed fetchers (used in server components / ISR) ────────────────────────

const BASE = (process.env.NEXT_PUBLIC_API_URL ?? 'https://api.davfiles.my.id') + '/api/v1'


async function apiFetch<T>(path: string): Promise<T> {
  const res = await fetch(`${BASE}${path}`, { next: { revalidate: 60 } })
  if (!res.ok) throw new Error(`API error: ${res.status}`)
  const json = await res.json()
  if (!json.success) throw new Error(json.error ?? 'API error')
  return json.data as T

}

export { apiFetch }