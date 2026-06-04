import { useSetting } from '../hooks/useSettings'
import { useState, useEffect, useRef } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import { HiMenu, HiX, HiShoppingBag, HiChevronDown, HiUser } from 'react-icons/hi'
import { FaWhatsapp } from 'react-icons/fa'
import { useCart }     from '../context/CartContext'
import { useCurrency } from '../context/CurrencyContext'
import { useAuth }     from '../context/AuthContext'
import { useTheme }    from '../context/ThemeContext'

export default function Navbar() {
  const [scrolled,    setScrolled]    = useState(false)
  const [menuOpen,    setMenuOpen]    = useState(false)
  const [cartBump,    setCartBump]    = useState(false)
  const [langOpen,    setLangOpen]    = useState(false)
  const [currOpen,    setCurrOpen]    = useState(false)
  const location = useLocation()
  const { t, i18n } = useTranslation()
  const { totalItems }              = useCart()
  const { currency, currencies, setCurrency } = useCurrency()
  const { user }                    = useAuth()
  const { theme }                   = useTheme()
  const isLight                     = theme?.isLight
  const langRef = useRef(null)
  const currRef = useRef(null)

  const isAr = i18n.language === 'ar'
  const waNumber = useSetting('business.whatsapp', '96812345678').replace(/[^0-9]/g, '')

  const navLinks = [
    { label: t('nav.collections'),                   to: '/collections' },
    { label: isAr ? 'المجلة الجلدية' : 'Journal',    to: '/blog' },
    { label: t('nav.story'),                          to: '/about' },
    { label: isAr ? 'تتبع الطلب' : 'Track Order',    to: '/track' },
    { label: t('nav.contact'),                        to: '/contact' },
  ]

  // Scroll handler
  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 60)
    window.addEventListener('scroll', onScroll)
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  // Close menu on route change
  useEffect(() => {
    setMenuOpen(false)
    window.scrollTo(0, 0)
  }, [location.pathname])

  // Cart bump animation
  useEffect(() => {
    if (totalItems === 0) return
    setCartBump(true)
    const t = setTimeout(() => setCartBump(false), 400)
    return () => clearTimeout(t)
  }, [totalItems])

  // Close dropdowns on outside click
  useEffect(() => {
    const handler = (e) => {
      if (langRef.current && !langRef.current.contains(e.target)) setLangOpen(false)
      if (currRef.current && !currRef.current.contains(e.target)) setCurrOpen(false)
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [])

  const toggleLang = () => {
    const next = isAr ? 'en' : 'ar'
    i18n.changeLanguage(next)
    setLangOpen(false)
  }

  return (
    <>
      <nav
        className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${
          scrolled
            ? `backdrop-blur-md border-b border-gold/10 py-3 ${isLight ? 'navbar-scrolled-bg' : 'bg-dark/95'}`
            : 'bg-transparent py-5'
        }`}
      >
        <div className="max-w-7xl mx-auto px-6 lg:px-12 flex items-center justify-between">

          {/* Logo */}
          <Link to="/" className="flex-shrink-0 flex items-center gap-3">
            <img src="/logo-icon.png" alt="Artisan Leather" className="h-11 w-11 object-contain" />
            <span className="hidden lg:block font-serif text-lg tracking-widest text-gold/90 uppercase">
              Artisan Leather
            </span>
          </Link>

          {/* Desktop nav */}
          <div className="hidden md:flex items-center gap-10">
            {navLinks.map((link) => (
              <Link key={link.label} to={link.to}
                className="text-xs tracking-[0.25em] uppercase text-white/60 hover:text-gold transition-colors duration-300">
                {link.label}
              </Link>
            ))}
          </div>

          {/* Right side */}
          <div className="flex items-center gap-3 md:gap-4">

            {/* ── Language switcher ── */}
            <div ref={langRef} className="hidden md:block relative">
              <button
                onClick={() => { setLangOpen(!langOpen); setCurrOpen(false) }}
                className="flex items-center gap-1.5 text-xs tracking-[0.15em] uppercase text-white/40 hover:text-gold transition-colors px-2 py-1"
              >
                <span>{isAr ? 'عربي' : 'EN'}</span>
                <HiChevronDown size={11} className={`transition-transform duration-300 ${langOpen ? 'rotate-180' : ''}`} />
              </button>
              <AnimatePresence>
                {langOpen && (
                  <motion.div
                    initial={{ opacity: 0, y: -6 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: -6 }}
                    transition={{ duration: 0.18 }}
                    className="absolute top-full mt-2 left-0 bg-dark-100 border border-gold/15 min-w-[120px] z-50"
                  >
                    {[
                      { code: 'en', label: 'English', native: 'English' },
                      { code: 'ar', label: 'Arabic',  native: 'عربي' },
                    ].map((lang) => (
                      <button
                        key={lang.code}
                        onClick={() => { i18n.changeLanguage(lang.code); setLangOpen(false) }}
                        className={`w-full flex items-center justify-between px-4 py-2.5 text-xs hover:text-gold transition-colors ${
                          i18n.language === lang.code ? 'text-gold' : 'text-white/45'
                        }`}
                      >
                        <span>{lang.label}</span>
                        <span className="text-white/30">{lang.native}</span>
                      </button>
                    ))}
                  </motion.div>
                )}
              </AnimatePresence>
            </div>

            {/* ── Currency selector ── */}
            <div ref={currRef} className="hidden md:block relative">
              <button
                onClick={() => { setCurrOpen(!currOpen); setLangOpen(false) }}
                className="flex items-center gap-1.5 text-xs tracking-[0.15em] text-white/40 hover:text-gold transition-colors px-2 py-1"
              >
                <span className="font-medium">{currency.code}</span>
                <HiChevronDown size={11} className={`transition-transform duration-300 ${currOpen ? 'rotate-180' : ''}`} />
              </button>
              <AnimatePresence>
                {currOpen && (
                  <motion.div
                    initial={{ opacity: 0, y: -6 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: -6 }}
                    transition={{ duration: 0.18 }}
                    className="absolute top-full mt-2 right-0 bg-dark-100 border border-gold/15 min-w-[170px] z-50 max-h-72 overflow-y-auto"
                  >
                    {currencies.map((c) => (
                      <button
                        key={c.code}
                        onClick={() => { setCurrency(c.code); setCurrOpen(false) }}
                        className={`w-full flex items-center justify-between px-4 py-2.5 text-xs hover:text-gold transition-colors ${
                          currency.code === c.code ? 'text-gold bg-gold/5' : 'text-white/45'
                        }`}
                      >
                        <span className="font-medium">{c.code}</span>
                        <span className="text-white/30">{isAr ? c.nameAr : c.name}</span>
                      </button>
                    ))}
                  </motion.div>
                )}
              </AnimatePresence>
            </div>

            {/* WhatsApp */}
            <a href={`https://wa.me/${waNumber}`} target="_blank" rel="noopener noreferrer"
              className="hidden md:flex items-center gap-2 border border-gold/40 text-gold px-4 py-2 text-xs tracking-widest uppercase hover:bg-gold hover:text-dark transition-all duration-300">
              <FaWhatsapp size={13} /> {t('nav.whatsapp')}
            </a>

            {/* Account */}
            <Link to={user ? '/account' : '/login'}
              className="hidden md:block text-white/50 hover:text-gold transition-colors duration-300 p-1"
              title={user ? user.name : 'Sign In'}>
              <HiUser size={20} />
            </Link>

            {/* Cart */}
            <Link to="/cart" className="relative text-white/70 hover:text-gold transition-colors duration-300 p-1">
              <motion.div animate={cartBump ? { scale: [1, 1.3, 1] } : { scale: 1 }} transition={{ duration: 0.35 }}>
                <HiShoppingBag size={22} />
              </motion.div>
              <AnimatePresence>
                {totalItems > 0 && (
                  <motion.span
                    key="badge"
                    initial={{ scale: 0 }} animate={{ scale: 1 }} exit={{ scale: 0 }}
                    className="absolute -top-1 -right-1 bg-gold text-dark text-[8px] font-bold w-4 h-4 rounded-full flex items-center justify-center"
                  >
                    {totalItems > 9 ? '9+' : totalItems}
                  </motion.span>
                )}
              </AnimatePresence>
            </Link>

            {/* Mobile menu toggle */}
            <button className="md:hidden text-white/80 hover:text-gold transition-colors p-1"
              onClick={() => setMenuOpen(!menuOpen)}>
              {menuOpen ? <HiX size={22} /> : <HiMenu size={22} />}
            </button>
          </div>
        </div>
      </nav>

      {/* Mobile fullscreen menu */}
      <AnimatePresence>
        {menuOpen && (
          <motion.div
            initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
            transition={{ duration: 0.3 }}
            className="fixed inset-0 z-40 bg-dark flex flex-col justify-center px-10"
          >
            <div className="flex flex-col gap-8">
              {navLinks.map((link, i) => (
                <motion.div key={link.label}
                  initial={{ opacity: 0, x: isAr ? 24 : -24 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: i * 0.08 }}>
                  <Link to={link.to}
                    className="font-serif text-4xl font-light text-white hover:text-gold transition-colors duration-300">
                    {link.label}
                  </Link>
                </motion.div>
              ))}

              <motion.div initial={{ opacity: 0, x: isAr ? 24 : -24 }} animate={{ opacity: 1, x: 0 }}
                transition={{ delay: navLinks.length * 0.08 }}>
                <Link to="/cart" className="font-serif text-4xl font-light text-white hover:text-gold transition-colors duration-300 flex items-center gap-4">
                  {t('nav.cart')}
                  {totalItems > 0 && (
                    <span className="bg-gold text-dark text-sm font-bold px-2.5 py-0.5 rounded-full">{totalItems}</span>
                  )}
                </Link>
              </motion.div>

              {/* Mobile: language + currency */}
              <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.4 }}
                className="pt-6 border-t border-white/10 flex flex-wrap gap-3">
                <button onClick={toggleLang}
                  className="border border-gold/30 text-gold px-4 py-2 text-xs tracking-widest uppercase hover:bg-gold hover:text-dark transition-all duration-300">
                  {isAr ? 'English' : 'عربي'}
                </button>
                {currencies.map((c) => (
                  <button key={c.code}
                    onClick={() => { setCurrency(c.code); setMenuOpen(false) }}
                    className={`border px-3 py-2 text-xs tracking-wider transition-all duration-300 ${
                      currency.code === c.code
                        ? 'border-gold bg-gold text-dark font-semibold'
                        : 'border-white/15 text-white/40 hover:border-gold/40 hover:text-gold'
                    }`}>
                    {c.code}
                  </button>
                ))}
              </motion.div>

              <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.45 }}
                className="border-t border-white/10 pt-4">
                <a href={`https://wa.me/${waNumber}`} target="_blank" rel="noopener noreferrer"
                  className="inline-flex items-center gap-3 text-gold text-sm tracking-widest uppercase">
                  <FaWhatsapp size={18} /> {t('nav.whatsapp')}
                </a>
              </motion.div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  )
}
