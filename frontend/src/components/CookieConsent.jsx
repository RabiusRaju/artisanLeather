import { useEffect, useState } from 'react'
import { HiX } from 'react-icons/hi'

const CONSENT_KEY = 'al_cookie_consent'

export default function CookieConsent() {
  const [visible, setVisible] = useState(false)

  useEffect(() => {
    if (typeof window === 'undefined') return
    setVisible(!localStorage.getItem(CONSENT_KEY))
  }, [])

  const choose = (value) => {
    localStorage.setItem(CONSENT_KEY, value)
    window.dispatchEvent(new Event('al-cookie-consent'))
    setVisible(false)
  }

  if (!visible) return null

  return (
    <div className="fixed inset-x-4 bottom-4 z-[120] sm:inset-x-auto sm:right-5 sm:bottom-5 sm:w-[420px]">
      <div className="relative border border-gold/25 bg-dark-200/95 shadow-2xl backdrop-blur-md p-5">
        <button
          type="button"
          onClick={() => choose('rejected')}
          className="absolute right-3 top-3 text-white/45 hover:text-white transition-colors duration-200"
          aria-label="Close cookie notice"
        >
          <HiX size={20} />
        </button>

        <p className="text-[10px] tracking-[0.3em] uppercase text-gold/70 mb-2">
          Cookies
        </p>
        <h2 className="font-serif text-xl text-white font-light pr-8">
          A smoother Artisan Leather experience
        </h2>
        <p className="mt-2 text-sm text-white/45 leading-relaxed">
          We use analytics cookies to understand visits and improve our store. You can accept or continue with essential cookies only.
        </p>

        <div className="mt-4 flex flex-col sm:flex-row gap-2">
          <button
            type="button"
            onClick={() => choose('accepted')}
            className="flex-1 bg-gold text-dark px-4 py-3 text-xs font-semibold tracking-[0.18em] uppercase hover:bg-gold-300 transition-colors duration-200"
          >
            Accept
          </button>
          <button
            type="button"
            onClick={() => choose('rejected')}
            className="flex-1 border border-white/10 text-white/55 px-4 py-3 text-xs font-semibold tracking-[0.18em] uppercase hover:border-gold/30 hover:text-gold transition-colors duration-200"
          >
            Essential Only
          </button>
        </div>
      </div>
    </div>
  )
}
