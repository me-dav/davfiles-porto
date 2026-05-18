// src/lib/auth.ts
import api from './api'

export const AUTH_KEY = 'auth_token'

export function getToken(): string | null {
  if (typeof window === 'undefined') return null
  return localStorage.getItem(AUTH_KEY)
}

export function setToken(token: string) {
  localStorage.setItem(AUTH_KEY, token)
}

export function clearToken() {
  localStorage.removeItem(AUTH_KEY)
}

export function isAuthenticated(): boolean {
  return !!getToken()
}

export async function login(email: string, password: string) {
  const res = await api.post('/auth/login', { email, password })
  const { token } = res.data.data
  setToken(token)
  return token
}

export async function logout() {
  try {
    await api.post('/auth/logout')
  } catch {
    // ignore — still clear token locally
  } finally {
    clearToken()
  }
}