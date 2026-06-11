import { useState, useEffect, useRef } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import { HiMenu, HiX, HiShoppingBag, HiChevronDown, HiUser, HiHeart } from 'react-icons/hi'
import { useCart }     from '../context/CartContext'
import { useCurrency } from '../context/CurrencyContext'
import { useAuth }     from '../context/AuthContext'
import { useTheme }    from '../context/ThemeContext'
import { useWishlist } from '../context/WishlistContext'

export default function Navbar() {
  const [scrolled,    setScrolled]    = useState(false)
  const [menuOpen,    setMenuOpen]    = useState(false)
  const [cartBump,    setCartBump]    = useState(false)
  const [prefsOpen,   setPrefsOpen]   = useState(false)
  const location = useLocation()
  const { t, i18n } = useTranslation()
  const { totalItems }              = useCart()
  const { currency, currencies, setCurrency } = useCurrency()
  const { user }                    = useAuth()
  const { productIds: wishlistIds } = useWishlist()
  const { theme }                   = useTheme()
  const isLight                     = theme?.isLight
  const prefsRef = useRef(null)

  const isAr = i18n.language === 'ar'

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

  // Close dropdown on outside click
  useEffect(() => {
    const handler = (e) => {
      if (prefsRef.current && !prefsRef.current.contains(e.target)) setPrefsOpen(false)
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [])

  const toggleLang = () => {
    const next = isAr ? 'en' : 'ar'
    i18n.changeLanguage(next)
    setPrefsOpen(false)
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
            <img src="/logo-icon-transparent.png" alt="Artisan Leather" className="h-14 w-14 lg:h-16 lg:w-16 object-contain" />
            <span className="hidden lg:block font-serif leading-none">
              <span className="block text-lg tracking-[0.2em] text-gold/90 uppercase">Artisan</span>
              <span className="block text-xs tracking-[0.35em] text-gold/60 uppercase mt-1">Leather</span>
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

            {/* ── Language + currency preferences ── */}
            <div ref={prefsRef} className="hidden md:block relative">
              <button
                onClick={() => setPrefsOpen(!prefsOpen)}
                className="flex items-center gap-1.5 text-xs tracking-[0.15em] uppercase text-white/40 hover:text-gold transition-colors px-2 py-1"
              >
                <span>{isAr ? 'عربي' : 'EN'}</span>
                <span className="text-white/20">·</span>
                <span className="font-medium">{currency.code}</span>
                <HiChevronDown size={11} className={`transition-transform duration-300 ${prefsOpen ? 'rotate-180' : ''}`} />
              </button>
              <AnimatePresence>
                {prefsOpen && (
                  <motion.div
                    initial={{ opacity: 0, y: -6 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: -6 }}
                    transition={{ duration: 0.18 }}
                    className="absolute top-full mt-2 right-0 bg-dark-100 border border-gold/15 min-w-[180px] z-50 max-h-96 overflow-y-auto"
                  >
                    {/* Language */}
                    <div className="px-4 pt-3 pb-1 text-[9px] tracking-[0.3em] uppercase text-white/25">
                      {isAr ? 'اللغة' : 'Language'}
                    </div>
                    {[
                      { code: 'en', label: 'English', native: 'English' },
                      { code: 'ar', label: 'Arabic',  native: 'عربي' },
                    ].map((lang) => (
                      <button
                        key={lang.code}
                        onClick={() => { i18n.changeLanguage(lang.code); setPrefsOpen(false) }}
                        className={`w-full flex items-center justify-between px-4 py-2 text-xs hover:text-gold transition-colors ${
                          i18n.language === lang.code ? 'text-gold' : 'text-white/45'
                        }`}
                      >
                        <span>{lang.label}</span>
                        <span className="text-white/30">{lang.native}</span>
                      </button>
                    ))}

                    {/* Currency */}
                    <div className="px-4 pt-3 pb-1 mt-1 border-t border-white/5 text-[9px] tracking-[0.3em] uppercase text-white/25">
                      {isAr ? 'العملة' : 'Currency'}
                    </div>
                    {currencies.map((c) => (
                      <button
                        key={c.code}
                        onClick={() => { setCurrency(c.code); setPrefsOpen(false) }}
                        className={`w-full flex items-center justify-between px-4 py-2 text-xs hover:text-gold transition-colors ${
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

            {/* Wishlist */}
            <Link to="/wishlist" className="relative text-white/50 hover:text-gold transition-colors duration-300 p-1" title={t('nav.wishlist')}>
              <HiHeart size={20} />
              {wishlistIds.length > 0 && (
                <span className="absolute -top-1 -right-1 bg-gold text-dark text-[8px] font-bold w-4 h-4 rounded-full flex items-center justify-center">
                  {wishlistIds.length > 9 ? '9+' : wishlistIds.length}
                </span>
              )}
            </Link>

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
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  )
}
