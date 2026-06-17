import { useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import { useTheme } from '../context/ThemeContext'
import { HiColorSwatch, HiX } from 'react-icons/hi'

export default function ThemeSelector() {
  const [open, setOpen] = useState(false)
  const { theme, themes, setTheme, isLocked } = useTheme()
  const { i18n } = useTranslation()
  const isAr = i18n.language === 'ar'

  if (isLocked) return null

  return (
    <>
      {/* Toggle button — bottom left */}
      <motion.button
        onClick={() => setOpen(!open)}
        initial={{ scale: 0, opacity: 0 }}
        animate={{ scale: 1, opacity: 1 }}
        transition={{ delay: 3, type: 'spring', stiffness: 200 }}
        whileHover={{ scale: 1.08 }}
        whileTap={{ scale: 0.95 }}
        className="dark-section fixed bottom-8 left-8 z-50 w-12 h-12 flex items-center justify-center border border-gold/30 text-gold/70 hover:text-gold hover:border-gold backdrop-blur-sm transition-all duration-300"
        style={{ backgroundColor: '#1E1508' }}
        aria-label="Choose theme"
        title="Choose theme"
      >
        {open ? <HiX size={18} /> : <HiColorSwatch size={18} />}
      </motion.button>

      {/* Theme panel */}
      <AnimatePresence>
        {open && (
          <motion.div
            initial={{ opacity: 0, y: 20, scale: 0.95 }}
            animate={{ opacity: 1, y: 0,  scale: 1 }}
            exit={{ opacity: 0, y: 20, scale: 0.95 }}
            transition={{ duration: 0.25, ease: 'easeOut' }}
            className="dark-section fixed bottom-24 left-8 z-50 w-72 border border-gold/20 shadow-2xl shadow-black/60 overflow-hidden"
            style={{ backgroundColor: '#1E1508' }}
          >
            {/* Header */}
            <div className="px-5 py-4 border-b border-gold/10">
              <p className="text-[9px] tracking-[0.4em] uppercase text-gold/50 mb-0.5">
                {isAr ? 'التخصيص' : 'Personalise'}
              </p>
              <h3 className="font-serif text-lg text-white/90 font-light">
                {isAr ? 'اختر مظهرك' : 'Choose Your Theme'}
              </h3>
            </div>

            {/* Theme grid */}
            <div className="p-4 space-y-2">
              {themes.map((t, i) => {
                const isActive = theme.id === t.id
                return (
                  <motion.button
                    key={t.id}
                    initial={{ opacity: 0, x: -12 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: i * 0.04 }}
                    onClick={() => { setTheme(t.id); setOpen(false) }}
                    className={`w-full flex items-center gap-4 px-4 py-3 text-left transition-all duration-300 group ${
                      isActive
                        ? 'border border-gold/40 bg-gold/5'
                        : 'border border-transparent hover:border-white/10 hover:bg-white/5'
                    }`}
                  >
                    {/* Color swatch */}
                    <div className="relative flex-shrink-0">
                      <div
                        className="w-9 h-9 rounded-full border-2 transition-all duration-300"
                        style={{
                          backgroundColor: t.preview,
                          borderColor: isActive ? '#C9A84C' : 'rgba(255,255,255,0.15)',
                          boxShadow: isActive ? '0 0 0 2px rgba(201,168,76,0.2)' : 'none',
                        }}
                      />
                      {/* Gold dot on active */}
                      {isActive && (
                        <motion.div
                          layoutId="active-dot"
                          className="absolute -top-0.5 -right-0.5 w-3 h-3 bg-gold rounded-full border-2"
                          style={{ borderColor: 'var(--theme-bg-secondary, #1E1508)' }}
                        />
                      )}
                    </div>

                    {/* Label */}
                    <div className="flex-1 min-w-0">
                      <p className={`text-sm font-medium leading-tight transition-colors duration-300 ${
                        isActive ? 'text-gold' : 'text-white/70 group-hover:text-white/90'
                      }`}>
                        {isAr ? t.nameAr : t.name}
                      </p>
                      <p className="text-[10px] text-white/30 leading-tight mt-0.5 truncate">
                        {isAr ? t.descriptionAr : t.description}
                      </p>
                    </div>

                    {/* Active check */}
                    {isActive && (
                      <span className="text-gold text-xs flex-shrink-0">✓</span>
                    )}
                  </motion.button>
                )
              })}
            </div>

            {/* Footer */}
            <div className="px-5 py-3 border-t border-gold/10">
              <p className="text-[9px] text-white/20 tracking-wider">
                {isAr
                  ? 'يُحفظ تلقائيًا · ذهبي دائمًا'
                  : 'Auto-saved · Gold accent always'}
              </p>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Click-outside backdrop */}
      {open && (
        <div
          className="fixed inset-0 z-40"
          onClick={() => setOpen(false)}
        />
      )}
    </>
  )
}
