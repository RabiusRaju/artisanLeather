import { useEffect, useState, useCallback } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { HiX, HiOutlineClipboard, HiOutlineClipboardCheck } from 'react-icons/hi'
import { fetchFeaturedCoupon, subscribeNewsletter } from '../services/api'
import { getUtmParams } from '../lib/utm'
import { trackLead } from '../lib/tracking'

const DISMISSED_KEY = 'al_coupon_popup_dismissed_until'
const SUBSCRIBED_KEY = 'al_coupon_popup_subscribed'
const DISMISS_DAYS = 14
const DELAY_MS = 12000
const SCROLL_THRESHOLD = 0.5
const EXCLUDED_PATHS = ['/cart', '/checkout', '/login', '/register', '/order-confirmation']

function getRemaining(expiresAt) {
  const diff = new Date(expiresAt).getTime() - Date.now()
  if (diff <= 0) return null
  return {
    days:    Math.floor(diff / 86400000),
    hours:   Math.floor((diff % 86400000) / 3600000),
    minutes: Math.floor((diff % 3600000) / 60000),
    seconds: Math.floor((diff % 60000) / 1000),
  }
}

function CountdownUnit({ value, label }) {
  return (
    <div className="flex flex-col items-center min-w-[3.25rem]">
      <span className="font-serif text-2xl text-gold tabular-nums">{String(value).padStart(2, '0')}</span>
      <span className="text-[10px] uppercase tracking-wider text-ivory-faint">{label}</span>
    </div>
  )
}

export default function CouponPopup() {
  const { pathname } = useLocation()
  const [coupon, setCoupon] = useState(null)
  const [visible, setVisible] = useState(false)
  const [remaining, setRemaining] = useState(null)
  const [copied, setCopied] = useState(false)
  const [email, setEmail] = useState('')
  const [subscribed, setSubscribed] = useState(false)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState('')

  useEffect(() => {
    if (typeof window === 'undefined') return
    if (EXCLUDED_PATHS.includes(pathname)) return
    if (localStorage.getItem(SUBSCRIBED_KEY)) return

    const dismissedUntil = Number(localStorage.getItem(DISMISSED_KEY) || 0)
    if (dismissedUntil && dismissedUntil > Date.now()) return

    fetchFeaturedCoupon()
      .then(({ data }) => {
        const featured = data?.data
        if (!featured || !featured.expires_at) return
        if (!getRemaining(featured.expires_at)) return
        setCoupon(featured)
      })
      .catch(() => {})
  }, [pathname])

  useEffect(() => {
    if (EXCLUDED_PATHS.includes(pathname)) {
      setVisible(false)
    }
  }, [pathname])

  useEffect(() => {
    if (!coupon || visible) return
    if (EXCLUDED_PATHS.includes(pathname)) return
    if (localStorage.getItem(SUBSCRIBED_KEY)) return
    const dismissedUntil = Number(localStorage.getItem(DISMISSED_KEY) || 0)
    if (dismissedUntil && dismissedUntil > Date.now()) return

    let shown = false

    const show = () => {
      if (shown) return
      shown = true
      setVisible(true)
    }

    const timer = setTimeout(show, DELAY_MS)
    const onScroll = () => {
      const scrollable = document.documentElement.scrollHeight - window.innerHeight
      if (scrollable <= 0) return
      if (window.scrollY / scrollable >= SCROLL_THRESHOLD) show()
    }

    window.addEventListener('scroll', onScroll, { passive: true })
    onScroll()

    return () => {
      clearTimeout(timer)
      window.removeEventListener('scroll', onScroll)
    }
  }, [coupon, pathname, visible])

  useEffect(() => {
    if (!coupon) return
    const tick = () => {
      const r = getRemaining(coupon.expires_at)
      setRemaining(r)
      if (!r) setVisible(false)
    }
    tick()
    const interval = setInterval(tick, 1000)
    return () => clearInterval(interval)
  }, [coupon])

  const close = useCallback(() => {
    setVisible(false)
    localStorage.setItem(DISMISSED_KEY, String(Date.now() + DISMISS_DAYS * 86400000))
  }, [])

  const copyCode = useCallback(() => {
    if (!coupon) return
    navigator.clipboard?.writeText(coupon.code)
    setCopied(true)
    setTimeout(() => setCopied(false), 2000)
  }, [coupon])

  const submitEmail = useCallback(async (event) => {
    event.preventDefault()
    if (!coupon || submitting) return

    setError('')
    setSubmitting(true)
    try {
      await subscribeNewsletter({
        email,
        coupon_code: coupon.code,
        source: 'coupon_popup',
        utm: getUtmParams(),
      })
      localStorage.setItem(SUBSCRIBED_KEY, '1')
      setSubscribed(true)
      trackLead('coupon_popup')
      copyCode()
    } catch (err) {
      setError(err.response?.data?.message || 'Please enter a valid email address.')
    } finally {
      setSubmitting(false)
    }
  }, [coupon, copyCode, email, submitting])

  if (!coupon) return null

  const discountLabel = coupon.type === 'percentage'
    ? `${parseFloat(coupon.value)}% OFF`
    : `OMR ${parseFloat(coupon.value).toFixed(3)} OFF`

  return (
    <AnimatePresence>
      {visible && (
        <motion.div
          className="fixed inset-0 z-[110] flex items-center justify-center bg-black/70 backdrop-blur-sm px-4"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          onClick={close}
        >
          <motion.div
            className="relative w-full max-w-sm bg-dark-200 border border-gold/30 shadow-2xl overflow-hidden"
            initial={{ opacity: 0, scale: 0.9, y: 20 }}
            animate={{ opacity: 1, scale: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.9, y: 20 }}
            transition={{ duration: 0.25 }}
            onClick={(e) => e.stopPropagation()}
          >
            <button
              onClick={close}
              className="absolute top-3 right-3 z-10 text-white/50 hover:text-white transition-colors duration-200"
              aria-label="Close"
            >
              <HiX size={22} />
            </button>

            {coupon.image && (
              <img src={coupon.image} alt="" className="w-full h-40 object-cover" />
            )}

            <div className="p-6 text-center">
              <p className="text-gold font-serif text-3xl tracking-wide">{discountLabel}</p>

              {coupon.title && (
                <h3 className="mt-2 text-lg text-ivory font-serif">{coupon.title}</h3>
              )}
              {coupon.description && (
                <p className="mt-2 text-sm text-ivory-faint font-light leading-relaxed">{coupon.description}</p>
              )}

              {!subscribed ? (
                <form onSubmit={submitEmail} className="mt-5 space-y-3 text-left">
                  <label className="block text-[10px] uppercase tracking-[0.22em] text-ivory-faint">
                    Email Address
                  </label>
                  <div className="flex flex-col sm:flex-row gap-2">
                    <input
                      type="email"
                      value={email}
                      onChange={(event) => setEmail(event.target.value)}
                      required
                      placeholder="email@example.com"
                      className="min-w-0 flex-1 bg-dark border border-white/10 px-4 py-3 text-sm text-ivory placeholder:text-white/25 focus:outline-none focus:border-gold/60"
                    />
                    <button
                      type="submit"
                      disabled={submitting}
                      className="bg-gold text-dark font-medium px-5 py-3 hover:bg-gold-300 disabled:opacity-60 transition-colors duration-200"
                    >
                      {submitting ? 'Saving...' : 'Get My Code'}
                    </button>
                  </div>
                  {error && <p className="text-xs text-red-300">{error}</p>}
                  <p className="text-[11px] text-white/35 leading-relaxed">
                    Join for leather care notes, private offers, and collection previews. You can unsubscribe anytime.
                  </p>
                </form>
              ) : (
                <button
                  onClick={copyCode}
                  className="mt-5 w-full flex items-center justify-between gap-2 border border-dashed border-gold/40 px-4 py-3 hover:border-gold/70 transition-colors duration-200 group"
                >
                  <span className="font-mono text-gold tracking-widest text-lg">{coupon.code}</span>
                  {copied ? (
                    <span className="flex items-center gap-1 text-xs text-emerald-400"><HiOutlineClipboardCheck size={18} /> Copied</span>
                  ) : (
                    <span className="flex items-center gap-1 text-xs text-white/50 group-hover:text-white/80"><HiOutlineClipboard size={18} /> Copy</span>
                  )}
                </button>
              )}

              {remaining && (
                <div className="mt-5 flex items-center justify-center gap-3">
                  <CountdownUnit value={remaining.days} label="Days" />
                  <CountdownUnit value={remaining.hours} label="Hrs" />
                  <CountdownUnit value={remaining.minutes} label="Min" />
                  <CountdownUnit value={remaining.seconds} label="Sec" />
                </div>
              )}

              <Link
                to="/collections"
                onClick={close}
                className="mt-6 inline-block w-full bg-gold text-dark font-medium py-3 hover:bg-gold-300 transition-colors duration-200"
              >
                {subscribed ? 'Shop Now' : 'Browse Collection'}
              </Link>
            </div>
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  )
}
