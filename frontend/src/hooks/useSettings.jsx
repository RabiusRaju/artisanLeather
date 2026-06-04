import { useState, useEffect, createContext, useContext } from 'react'
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

export function useSettings() {
  return useContext(SettingsContext)
}

// Convenience helper — get a single setting with fallback
export function useSetting(key, fallback = '') {
  const settings = useSettings()
  return settings[key] || fallback
}
