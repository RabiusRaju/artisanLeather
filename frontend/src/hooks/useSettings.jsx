import { useState, useEffect, useMemo, createContext, useContext } from 'react'
import { useTranslation } from 'react-i18next'
import { fetchSettings } from '../services/api'

const SettingsContext = createContext({})

export function SettingsProvider({ children }) {
  const [settings, setSettings] = useState(() => {
    // Load from localStorage cache for instant render
    try {
      const cached = localStorage.getItem('al_settings')
      return cached ? JSON.parse(cached) : {}
    } catch { return {} }
  })

  useEffect(() => {
    fetchSettings()
      .then(res => {
        const data = res.data.data
        setSettings(data)
        localStorage.setItem('al_settings', JSON.stringify(data))
      })
      .catch(() => {}) // fail silently — use cached/defaults
  }, [])

  return <SettingsContext.Provider value={settings}>{children}</SettingsContext.Provider>
}

// Returns the settings map, with `${key}_ar` values swapped into `key`
// when the active language is Arabic (and a non-empty translation exists).
export function useSettings() {
  const settings = useContext(SettingsContext)
  const { i18n } = useTranslation()
  const isAr = i18n.language?.startsWith('ar')

  return useMemo(() => {
    if (!isAr) return settings

    const localized = { ...settings }
    for (const key of Object.keys(settings)) {
      if (key.endsWith('_ar')) continue
      const arValue = settings[`${key}_ar`]
      if (arValue) localized[key] = arValue
    }
    return localized
  }, [settings, isAr])
}

// Convenience helper — get a single (already-localized) setting with fallback.
export function useSetting(key, fallback = '') {
  const settings = useSettings()
  return settings[key] || fallback
}
