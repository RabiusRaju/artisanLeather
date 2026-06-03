import { createContext, useContext, useState, useEffect } from 'react'
import { fetchCurrencies } from '../services/api'

// Fallback rates used until API responds
const FALLBACK = [
  { code: 'OMR', symbol: 'OMR', name: 'Omani Rial',    nameAr: 'ريال عُماني',    rate: 1,     decimals: 3 },
  { code: 'AED', symbol: 'AED', name: 'UAE Dirham',     nameAr: 'درهم إماراتي',   rate: 9.64,  decimals: 2 },
  { code: 'SAR', symbol: 'SAR', name: 'Saudi Riyal',    nameAr: 'ريال سعودي',    rate: 9.64,  decimals: 2 },
  { code: 'KWD', symbol: 'KWD', name: 'Kuwaiti Dinar',  nameAr: 'دينار كويتي',   rate: 0.80,  decimals: 3 },
  { code: 'USD', symbol: '$',   name: 'US Dollar',       nameAr: 'دولار أمريكي',  rate: 2.60,  decimals: 2 },
  { code: 'GBP', symbol: '£',   name: 'British Pound',  nameAr: 'جنيه إسترليني', rate: 2.05,  decimals: 2 },
  { code: 'EUR', symbol: '€',   name: 'Euro',            nameAr: 'يورو',           rate: 2.38,  decimals: 2 },
]

const STORAGE_KEY = 'al_currency'
const CurrencyContext = createContext(null)

export function CurrencyProvider({ children }) {
  const [currencies, setCurrencies] = useState(FALLBACK)
  const [currency, setCurrencyState] = useState(() => {
    const saved = localStorage.getItem(STORAGE_KEY)
    return FALLBACK.find((c) => c.code === saved) || FALLBACK[0]
  })

  // Fetch live rates from backend on mount
  useEffect(() => {
    fetchCurrencies()
      .then(res => {
        const live = res.data.data.map(c => ({
          code:     c.code,
          symbol:   c.symbol,
          name:     c.name,
          nameAr:   c.name_ar,
          rate:     parseFloat(c.rate),
          decimals: c.decimals,
        }))
        setCurrencies(live)

        // Sync selected currency with fresh rate from backend
        setCurrencyState(prev => {
          const updated = live.find(c => c.code === prev.code)
          return updated || live[0]
        })
      })
      .catch(() => {}) // silently fall back to hardcoded values
  }, [])

  const setCurrency = (code) => {
    const found = currencies.find((c) => c.code === code)
    if (found) {
      setCurrencyState(found)
      localStorage.setItem(STORAGE_KEY, code)
    }
  }

  const convert = (omrPrice) => parseFloat(omrPrice) * currency.rate

  const format = (omrPrice) => {
    const converted = convert(omrPrice)
    return `${currency.symbol} ${converted.toFixed(currency.decimals)}`
  }

  return (
    <CurrencyContext.Provider value={{ currency, currencies, setCurrency, convert, format }}>
      {children}
    </CurrencyContext.Provider>
  )
}

export const useCurrency = () => useContext(CurrencyContext)
