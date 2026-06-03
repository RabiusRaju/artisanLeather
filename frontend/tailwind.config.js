/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx,ts,tsx}'],
  theme: {
    extend: {
      colors: {
        gold: {
          DEFAULT: '#C9A84C',
          50:  '#FAF5E4',
          100: '#F2E5B8',
          200: '#E8D08A',
          300: '#D4AF37',
          400: '#C9A84C',
          500: '#A67C33',
          600: '#7D5A1F',
          700: '#54380F',
        },
        // All dark shades now reference CSS variables —
        // changing --theme-bg* in JS instantly re-themes the whole site
        dark: {
          DEFAULT: 'var(--theme-bg,          #120D05)',
          50:      'var(--theme-bg-hover,     #3A2E1E)',
          100:     'var(--theme-bg-secondary, #1E1508)',
          200:     'var(--theme-bg-card,      #1A1208)',
          300:     'var(--theme-bg-deep,      #150F06)',
        },
        ivory: {
          DEFAULT: '#F5EDD8',
          dim:     '#E8DCC4',
          faint:   '#C8BAA0',
        },
      },
      fontFamily: {
        serif: ['"Cormorant Garamond"', 'Georgia', 'serif'],
        sans:  ['Inter', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
