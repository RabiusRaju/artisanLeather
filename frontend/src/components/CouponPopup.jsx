import { useEffect, useState, useCallback } from 'react'
import { Link } from 'react-router-dom'
import { AnimatePresence, motion } from 'framer-motion'
import { HiX, HiOutlineClipboard, HiOutlineClipboardCheck } from 'react-icons/hi'
import { fetchFeaturedCoupon } from '../services/api'

const SESSION_KEY = 'al_coupon_popup_shown'

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
  const [coupon, setCoupon] = useState(null)
  const [visible, setVisible] = useState(false)
  const [remaining, setRemaining] = useState(null)
  const [copied, setCopied] = useState(false)

  useEffect(() => {
    if (sessionStorage.getItem(SESSION_KEY)) return

    fetchFeaturedCoupon()
      .then(({ data }) => {
        const featured = data?.data
        if (!featured || !featured.expires_at) return
        if (!getRemaining(featured.expires_at)) return
        setCoupon(featured)
        setVisible(true)
      })
      .catch(() => {})
  }, [])

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
    sessionStorage.setItem(SESSION_KEY, '1')
  }, [])

  const copyCode = useCallback(() => {
    if (!coupon) return
    navigator.clipboard?.writeText(coupon.code)
    setCopied(true)
    setTimeout(() => setCopied(false), 2000)
  }, [coupon])

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
                Shop Now
              </Link>
            </div>
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  )
}
