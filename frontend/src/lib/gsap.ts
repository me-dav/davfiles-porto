'use client'
// src/lib/gsap.ts
// ─────────────────────────────────────────────────────────────────
// Import GSAP dan register plugin di satu tempat.
// Import dari file ini, bukan langsung dari 'gsap' di komponen —
// supaya ScrollTrigger hanya di-register sekali dan tidak crash
// di server-side rendering.
// ─────────────────────────────────────────────────────────────────

import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

// Register plugin — aman dipanggil berkali-kali (GSAP handle duplikat)
gsap.registerPlugin(ScrollTrigger)

export { gsap, ScrollTrigger }