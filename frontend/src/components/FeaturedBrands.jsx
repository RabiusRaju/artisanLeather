import { useRef } from 'react'
import { Link } from 'react-router-dom'
import { motion, useInView } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import { useBrands } from '../hooks/useBrands'

function BrandCard({ brand, index }) {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, margin: '-60px' })
  const { i18n } = useTranslation()
  const isAr = i18n.language === 'ar'

  const name    = isAr && brand.name_ar    ? brand.name_ar    : brand.name
  const tagline = isAr && brand.tagline_ar ? brand.tagline_ar : brand.tagline

  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, y: 32 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.6, delay: index * 0.12 }}
      className="group flex-shrink-0 w-64 sm:w-auto"
    >
      <Link to={`/collections?brand=${brand.slug}`}>
        {/* Brand banner / card */}
        <div
          className="relative overflow-hidden"
          style={{ aspectRatio: '3/4' }}
        >
          {brand.banner ? (
            <img
              src={brand.banner}
              alt={name}
              className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
            />
          ) : (
            <div
              className="absolute inset-0"
              style={{ background: 'linear-gradient(160deg, #2C1E0A, #1A1208, #0F0A03)' }}
            />
          )}

          {/* Dark overlay for text readability */}
          <div className="absolute inset-0 bg-gradient-to-t from-dark/90 via-dark/30 to-transparent" />

          {/* Corner accents */}
          <div className="absolute top-4 left-4 w-6 h-6 border-t border-l border-gold/30 group-hover:border-gold/70 transition-colors duration-500" />
          <div className="absolute top-4 right-4 w-6 h-6 border-t border-r border-gold/30 group-hover:border-gold/70 transition-colors duration-500" />
          <div className="absolute bottom-4 left-4 w-6 h-6 border-b border-l border-gold/30 group-hover:border-gold/70 transition-colors duration-500" />
          <div className="absolute bottom-4 right-4 w-6 h-6 border-b border-r border-gold/30 group-hover:border-gold/70 transition-colors duration-500" />

          {/* Product count badge */}
          <div className="absolute top-5 right-5 bg-dark/80 border border-gold/30 text-gold/80 text-[8px] tracking-[0.25em] uppercase px-2.5 py-1 backdrop-blur-sm z-10">
            {brand.products_count} pieces
          </div>

          {/* Bottom text */}
          <div className="absolute bottom-0 left-0 right-0 p-5 z-10">
            <div className="w-8 h-px bg-gold mb-3" />
            <h3 className="font-serif text-xl text-white font-light leading-tight group-hover:text-gold transition-colors duration-300">
              {name}
            </h3>
            {tagline && (
              <p className="text-white/60 text-xs mt-1.5 font-light leading-relaxed line-clamp-2">
                {tagline}
              </p>
            )}
            <div className="flex items-center gap-2 mt-3 text-gold/70 text-[10px] tracking-[0.25em] uppercase group-hover:text-gold transition-colors duration-300">
              <span>Explore</span>
              <span className="group-hover:translate-x-1 transition-transform duration-300">→</span>
            </div>
          </div>
        </div>
      </Link>
    </motion.div>
  )
}

export default function FeaturedBrands() {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true })
  const { t, i18n } = useTranslation()
  const isAr = i18n.language === 'ar'
  const { brands, loading } = useBrands()

  const featured = brands.filter(b => b.is_featured)

  // Don't render if no featured brands
  if (!loading && featured.length === 0) return null

  return (
    <section className="py-24 overflow-hidden">
      {/* Section header */}
      <div className="px-6 lg:px-12 max-w-7xl mx-auto mb-14">
        <motion.div
          ref={ref}
          initial={{ opacity: 0, y: 20 }}
          animate={isInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.7 }}
          className="flex items-end justify-between"
        >
          <div>
            <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-4">
              {isAr ? 'مجموعاتنا' : 'Our Collections'}
            </p>
            <h2 className="font-serif text-4xl md:text-5xl text-white font-light">
              {isAr ? 'اكتشف خطوطنا' : 'Explore Our Lines'}
            </h2>
          </div>
          <Link
            to="/collections"
            className="hidden md:flex items-center gap-2 text-gold/60 text-[10px] tracking-[0.3em] uppercase hover:text-gold transition-colors group"
          >
            <span>{t('common.viewAll')}</span>
            <span className="group-hover:translate-x-1 transition-transform duration-300">→</span>
          </Link>
        </motion.div>
        <div className="w-16 h-px bg-gold mt-6" />
      </div>

      {/* Brands grid */}
      {loading ? (
        <div className="px-6 lg:px-12 max-w-7xl mx-auto">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-5">
            {[1,2,3,4].map(i => (
              <div key={i} className="animate-pulse" style={{ aspectRatio: '3/4', background: '#1E1508' }} />
            ))}
          </div>
        </div>
      ) : (
        <div className="px-6 lg:px-12 max-w-7xl mx-auto">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-5">
            {featured.map((brand, i) => (
              <BrandCard key={brand.id} brand={brand} index={i} />
            ))}
          </div>
        </div>
      )}
    </section>
  )
}
