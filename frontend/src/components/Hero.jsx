import { motion } from 'framer-motion'
import { Link } from 'react-router-dom'
import { useSetting } from '../hooks/useSettings'

function HeroLink({ to, className, children }) {
  const isExternal = /^https?:\/\//i.test(to)

  if (isExternal) {
    return (
      <a href={to} className={className} target="_blank" rel="noopener noreferrer">
        {children}
      </a>
    )
  }

  return (
    <Link to={to} className={className}>
      {children}
    </Link>
  )
}

export default function Hero() {
  const eyebrow        = useSetting('hero.eyebrow',         'Muscat · Sultanate of Oman')
  const headline       = useSetting('hero.headline',         'Where Leather')
  const headlineAccent = useSetting('hero.headline_accent',  'Becomes Legacy')
  const subtitle       = useSetting('hero.subtitle',         'Handcrafted premium leather goods for those who appreciate the art of timeless elegance.')
  const ctaPrimary     = useSetting('hero.cta_primary',      'Explore Collection')
  const ctaPrimaryUrl  = useSetting('hero.cta_primary_url',  '/collections')
  const ctaSecondary   = useSetting('hero.cta_secondary',    'Our Story')
  const ctaSecondaryUrl = useSetting('hero.cta_secondary_url', '/about')

  return (
    <section className="relative h-screen min-h-[700px] flex items-center justify-center overflow-hidden">
      {/* Background layers */}
      <div className="absolute inset-0 bg-[#120D05]" />
      <div
        className="absolute inset-0 opacity-[0.07]"
        style={{
          backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E")`,
          backgroundRepeat: 'repeat',
          backgroundSize: '200px',
        }}
      />

      {/* Diagonal gold accent lines */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        <div className="absolute top-0 left-1/4 w-px h-full bg-gradient-to-b from-transparent via-gold/10 to-transparent" />
        <div className="absolute top-0 right-1/4 w-px h-full bg-gradient-to-b from-transparent via-gold/10 to-transparent" />
        <div className="absolute top-1/3 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gold/15 to-transparent" />
        <div className="absolute top-2/3 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gold/8 to-transparent" />
      </div>

      {/* Main content */}
      <div className="relative z-10 text-center px-6 max-w-5xl mx-auto">
        <motion.p
          initial={{ opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8 }}
          className="text-gold/80 tracking-[0.6em] uppercase text-xs mb-10"
        >
          {eyebrow}
        </motion.p>

        <motion.h1
          initial={{ opacity: 0, y: 36 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 1, delay: 0.2 }}
          className="font-serif text-6xl md:text-8xl lg:text-[7rem] font-light text-white leading-[1.05] mb-8"
        >
          {headline}
          <br />
          <span className="text-gradient-gold italic">{headlineAccent}</span>
        </motion.h1>

        <motion.div
          initial={{ scaleX: 0 }}
          animate={{ scaleX: 1 }}
          transition={{ duration: 0.7, delay: 0.55 }}
          className="w-20 h-px bg-gold mx-auto mb-10 origin-center"
        />

        <motion.p
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ duration: 0.9, delay: 0.7 }}
          className="text-white/50 text-lg md:text-xl font-light tracking-wide max-w-2xl mx-auto mb-14 leading-relaxed"
        >
          {subtitle}
        </motion.p>

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.9 }}
          className="flex flex-col sm:flex-row gap-4 justify-center"
        >
          <HeroLink
            to={ctaPrimaryUrl || '/collections'}
            className="px-12 py-4 bg-gold text-dark text-xs tracking-[0.3em] uppercase font-semibold hover:bg-gold-300 transition-all duration-300"
          >
            {ctaPrimary}
          </HeroLink>
          <HeroLink
            to={ctaSecondaryUrl || '/about'}
            className="px-12 py-4 border border-white/25 text-white text-xs tracking-[0.3em] uppercase hover:border-gold hover:text-gold transition-all duration-300"
          >
            {ctaSecondary}
          </HeroLink>
        </motion.div>
      </div>

      {/* Scroll indicator */}
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ delay: 1.8 }}
        className="absolute bottom-10 left-1/2 -translate-x-1/2 flex flex-col items-center gap-3 text-white/25"
      >
        <span className="text-[10px] tracking-[0.4em] uppercase">Scroll</span>
        <motion.div
          animate={{ y: [0, 7, 0] }}
          transition={{ repeat: Infinity, duration: 1.6, ease: 'easeInOut' }}
          className="w-px h-10 bg-gradient-to-b from-gold/40 to-transparent"
        />
      </motion.div>
    </section>
  )
}
