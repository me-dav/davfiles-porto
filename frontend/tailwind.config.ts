import type { Config } from 'tailwindcss'

const config: Config = {
  content: ['./src/**/*.{ts,tsx}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        ink:      '#0E0E0F',
        graphite: '#1B1B1C',
        slate:    '#2E2E30',
        stone:    '#5C5957',
        ash:      '#BFBAB6',
        sand:     '#F7F5F3',
      },
      fontFamily: {
        display: ['"Cabibet Grotesk"', 'system-ui', 'sans-serif'],
        body:    ['"satoshi"', 'system-ui', 'sans-serif'],
      },
      fontSize: {
        'display-xl': ['clamp(4.5rem,13vw,11rem)', { lineHeight: '0.9',  letterSpacing: '-0.03em' }],
        'display-lg': ['clamp(3rem,8vw,7rem)',      { lineHeight: '0.95', letterSpacing: '-0.02em' }],
        'display-md': ['clamp(1.8rem,4vw,3.5rem)',  { lineHeight: '1',    letterSpacing: '-0.01em' }],
      },
      letterSpacing: {
        editorial: '0.04em',
        wide2:     '0.15em',
        wide3:     '0.2em',
      },
      backgroundImage: {
        'vignette-bottom':
          'linear-gradient(to top, #0E0E0F 0%, rgba(14,14,15,0.55) 55%, transparent 100%)',
        'vignette-full':
          'radial-gradient(ellipse at center, transparent 35%, rgba(14,14,15,0.8) 100%)',
      },
      transitionTimingFunction: {
        editorial: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
    // line-clamp built-in di Tailwind v3.3+ — tidak perlu plugin lagi
    // kalau masih error: npm install @tailwindcss/line-clamp lalu uncomment:
    // require('@tailwindcss/line-clamp'),
  ],
}

export default config