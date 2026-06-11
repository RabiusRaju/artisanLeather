import { createContext, useContext, useState, useEffect } from 'react'
import { useSettings } from '../hooks/useSettings'

// ── Theme definitions ──────────────────────────────────────────────────────
// isLight: true → CSS class "theme-light" is added to <html>, which flips
//   text-white → dark brown and border-white → dark border everywhere.
//   Sections that must stay dark (footer, etc.) get class "dark-section".

// eslint-disable-next-line react-refresh/only-export-components -- theme data colocated with provider
export const themes = [
  {
    id:      'warm-leather',
    name:    'Warm Leather',
    nameAr:  'جلد دافئ',
    emoji:   '🟤',
    preview: '#120D05',
    accent:  '#C9A84C',
    description:    'Rich warm brown — the colour of aged leather',
    descriptionAr:  'بني دافئ غني — لون الجلد العتيق',
    vars: {
      '--theme-bg':           '#120D05',
      '--theme-bg-secondary': '#1E1508',
      '--theme-bg-card':      '#1A1208',
      '--theme-bg-deep':      '#150F06',
      '--theme-bg-hover':     '#3A2E1E',
    },
  },
  {
    id:      'classic-black',
    name:    'Classic Black',
    nameAr:  'الأسود الكلاسيكي',
    emoji:   '⬛',
    preview: '#0A0A0A',
    accent:  '#C9A84C',
    description:    'Pure black — timeless, bold, dramatic',
    descriptionAr:  'الأسود الخالص — خالد وجريء',
    vars: {
      '--theme-bg':           '#0A0A0A',
      '--theme-bg-secondary': '#1A1A1A',
      '--theme-bg-card':      '#141414',
      '--theme-bg-deep':      '#0D0D0D',
      '--theme-bg-hover':     '#2A2A2A',
    },
  },
  {
    id:      'forest-atelier',
    name:    'Forest Atelier',
    nameAr:  'أتيليه الغابة',
    emoji:   '🌲',
    preview: '#050F08',
    accent:  '#C9A84C',
    description:    'Dark forest green — nature meets luxury',
    descriptionAr:  'أخضر الغابة الداكن — الطبيعة تلتقي بالفخامة',
    vars: {
      '--theme-bg':           '#050F08',
      '--theme-bg-secondary': '#0C1A10',
      '--theme-bg-card':      '#0A1510',
      '--theme-bg-deep':      '#060F09',
      '--theme-bg-hover':     '#1A2E20',
    },
  },
  {
    id:      'royal-burgundy',
    name:    'Royal Burgundy',
    nameAr:  'البرغوندي الملكي',
    emoji:   '🍷',
    preview: '#0F0508',
    accent:  '#C9A84C',
    description:    'Deep burgundy wine — royalty and refinement',
    descriptionAr:  'نبيذ البرغوندي العميق — النبالة والرقي',
    vars: {
      '--theme-bg':           '#0F0508',
      '--theme-bg-secondary': '#1A0810',
      '--theme-bg-card':      '#15060D',
      '--theme-bg-deep':      '#110609',
      '--theme-bg-hover':     '#2E1020',
    },
  },
  {
    id:      'midnight-navy',
    name:    'Midnight Navy',
    nameAr:  'الكحلي الليلي',
    emoji:   '🌊',
    preview: '#05080F',
    accent:  '#C9A84C',
    description:    'Deep midnight blue — prestigious and modern',
    descriptionAr:  'الأزرق الليلي العميق — مرموق وعصري',
    vars: {
      '--theme-bg':           '#05080F',
      '--theme-bg-secondary': '#0A1020',
      '--theme-bg-card':      '#080D1A',
      '--theme-bg-deep':      '#060A14',
      '--theme-bg-hover':     '#101828',
    },
  },
  {
    id:      'desert-dusk',
    name:    'Desert Dusk',
    nameAr:  'غسق الصحراء',
    emoji:   '🏜️',
    preview: '#1A1005',
    accent:  '#C9A84C',
    description:    'Warm desert sand at dusk — pure Oman',
    descriptionAr:  'رمال الصحراء الدافئة عند الغسق — عُمان خالصة',
    vars: {
      '--theme-bg':           '#1A1005',
      '--theme-bg-secondary': '#261806',
      '--theme-bg-card':      '#201407',
      '--theme-bg-deep':      '#180E04',
      '--theme-bg-hover':     '#3C2810',
    },
  },
  {
    id:      'daylight-white',
    name:    'Daylight White',
    nameAr:  'النهار الأبيض',
    emoji:   '☀️',
    preview: '#FAF7F2',
    accent:  '#C9A84C',
    description:    'Warm ivory — light luxury, dark footer',
    descriptionAr:  'عاجي دافئ — فخامة فاتحة، تذييل داكن',
    isLight: true,
    vars: {
      '--theme-bg':           '#FAF7F2',
      '--theme-bg-secondary': '#F0EAE0',
      '--theme-bg-card':      '#FFFFFF',
      '--theme-bg-deep':      '#EAE0D5',
      '--theme-bg-hover':     '#E0D4C4',
    },
  },
]

const STORAGE_KEY = 'al_theme'
const ThemeContext = createContext(null)

export function ThemeProvider({ children }) {
  const settings = useSettings()
  const isLocked = settings['theme.lock_theme'] === '1' || settings['theme.lock_theme'] === true
  const hasUserOverride = !!localStorage.getItem(STORAGE_KEY)

  const [theme, setThemeState] = useState(() => {
    const saved = localStorage.getItem(STORAGE_KEY)
    return themes.find(t => t.id === saved) || themes[0]
  })

  // The theme actually shown: the admin-configured default overrides the
  // visitor's own pick when the theme is locked, or before they've chosen one.
  const backendDefault = themes.find(t => t.id === settings['theme.default'])
  const activeTheme = (isLocked || !hasUserOverride) && backendDefault ? backendDefault : theme

  const applyTheme = (t) => {
    const root = document.documentElement
    Object.entries(t.vars).forEach(([prop, value]) => {
      root.style.setProperty(prop, value)
    })
    root.setAttribute('data-theme', t.id)
    // Toggle light-mode class — CSS uses this to flip text/border colours
    if (t.isLight) {
      root.classList.add('theme-light')
    } else {
      root.classList.remove('theme-light')
    }
  }

  // Apply CSS variables to :root whenever the active theme changes
  useEffect(() => { applyTheme(activeTheme) }, [activeTheme])

  const setTheme = (id) => {
    if (isLocked) return
    const found = themes.find(t => t.id === id)
    if (found) {
      setThemeState(found)
      localStorage.setItem(STORAGE_KEY, id)
    }
  }

  return (
    <ThemeContext.Provider value={{ theme: activeTheme, themes, setTheme, isLocked }}>
      {children}
    </ThemeContext.Provider>
  )
}

// eslint-disable-next-line react-refresh/only-export-components -- hook colocated with its provider
export const useTheme = () => useContext(ThemeContext)
