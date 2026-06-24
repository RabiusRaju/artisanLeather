import { useState, useRef, useEffect } from 'react'
import { AnimatePresence, motion } from 'framer-motion'
import { HiShare, HiOutlineClipboard, HiOutlineClipboardCheck } from 'react-icons/hi'
import { FaWhatsapp, FaFacebook, FaLinkedin } from 'react-icons/fa'
import { FaXTwitter } from 'react-icons/fa6'
import { useToast } from '../context/ToastContext'

export default function ShareButton({ url, title, className = '' }) {
  const [open, setOpen] = useState(false)
  const [copied, setCopied] = useState(false)
  const ref = useRef(null)
  const toast = useToast()

  useEffect(() => {
    if (!open) return
    const close = (e) => { if (ref.current && !ref.current.contains(e.target)) setOpen(false) }
    document.addEventListener('mousedown', close)
    return () => document.removeEventListener('mousedown', close)
  }, [open])

  const encodedUrl   = encodeURIComponent(url)
  const encodedTitle = encodeURIComponent(title)

  const links = [
    { label: 'WhatsApp', icon: FaWhatsapp, color: '#25D366', href: `https://wa.me/?text=${encodedTitle}%20${encodedUrl}` },
    { label: 'Facebook', icon: FaFacebook, color: '#1877F2', href: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}` },
    { label: 'X',        icon: FaXTwitter, color: '#ffffff', href: `https://twitter.com/intent/tweet?text=${encodedTitle}&url=${encodedUrl}` },
    { label: 'LinkedIn', icon: FaLinkedin, color: '#0A66C2', href: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}` },
  ]

  const copyLink = () => {
    navigator.clipboard?.writeText(url)
    setCopied(true)
    toast?.toast('Link copied to clipboard', { type: 'success' })
    setTimeout(() => setCopied(false), 2000)
    setOpen(false)
  }

  return (
    <div className="relative" ref={ref}>
      <button
        onClick={() => setOpen((o) => !o)}
        aria-label="Share"
        className={className || 'flex-shrink-0 w-10 h-10 flex items-center justify-center border border-white/10 hover:border-gold/40 transition-colors duration-300'}
      >
        <HiShare size={17} className="text-white/40" />
      </button>

      <AnimatePresence>
        {open && (
          <motion.div
            initial={{ opacity: 0, y: -6, scale: 0.96 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: -6, scale: 0.96 }}
            transition={{ duration: 0.15 }}
            className="absolute right-0 mt-2 w-48 bg-dark-200 border border-gold/20 shadow-xl z-20 py-2"
          >
            {links.map(({ label, icon: Icon, color, href }) => (
              <a
                key={label}
                href={href}
                target="_blank"
                rel="noopener noreferrer"
                onClick={() => setOpen(false)}
                className="flex items-center gap-3 px-4 py-2.5 text-sm text-white/70 hover:bg-white/5 hover:text-white transition-colors duration-200"
              >
                <Icon size={16} style={{ color }} />
                {label}
              </a>
            ))}
            <button
              onClick={copyLink}
              className="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-white/70 hover:bg-white/5 hover:text-white transition-colors duration-200"
            >
              {copied ? <HiOutlineClipboardCheck size={16} className="text-emerald-400" /> : <HiOutlineClipboard size={16} />}
              {copied ? 'Copied!' : 'Copy Link'}
            </button>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  )
}
