import { useRef } from 'react'
import { Link } from 'react-router-dom'
import { motion, useInView } from 'framer-motion'
import { useCategories } from '../hooks/useCategories'
import { useSettings } from '../hooks/useSettings'

function CollectionCard({ item, index, cardCta }) {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, margin: '-80px' })

  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, y: 40 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.7, delay: index * 0.12 }}
      className="group cursor-pointer"
    >
      <Link to={`/collections/${item.slug}`}>
        <div className="relative overflow-hidden bg-dark-100" style={{ aspectRatio: '3/4' }}>
          {item.image && (
            <img
              src={item.image}
              alt={item.image_alt || item.name}
              loading="lazy"
              decoding="async"
              className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
            />
          )}
          <div className="absolute inset-0 bg-dark/40 group-hover:bg-dark/25 transition-colors duration-500" />

          <div className="absolute top-3 left-3 w-5 h-5 border-t border-l border-gold/20 group-hover:border-gold/60 transition-colors duration-500" />
          <div className="absolute top-3 right-3 w-5 h-5 border-t border-r border-gold/20 group-hover:border-gold/60 transition-colors duration-500" />
          <div className="absolute bottom-3 left-3 w-5 h-5 border-b border-l border-gold/20 group-hover:border-gold/60 transition-colors duration-500" />
          <div className="absolute bottom-3 right-3 w-5 h-5 border-b border-r border-gold/20 group-hover:border-gold/60 transition-colors duration-500" />

          {cardCta && (
            <div className="absolute inset-0 bg-gold/[0.06] opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
              <span className="border border-gold text-gold px-6 py-2 text-[10px] tracking-[0.3em] uppercase opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-100">
                {cardCta}
              </span>
            </div>
          )}
        </div>

        <div className="mt-5 px-1 flex items-end justify-between">
          <h3 className="font-serif text-xl text-white group-hover:text-gold transition-colors duration-300">
            {item.name}
          </h3>
          <span className="text-gold/0 group-hover:text-gold text-xl transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
            →
          </span>
        </div>
      </Link>
    </motion.div>
  )
}

export default function Collections() {
  const s = useSettings()
  const { categories, loading } = useCategories()
  const headerRef = useRef(null)
  const headerInView = useInView(headerRef, { once: true })

  const eyebrow = s['home.collections.eyebrow'] || ''
  const title = s['home.collections.title'] || ''
  const cardCta = s['home.collections.card_cta'] || ''

  if (!loading && categories.length === 0) return null

  return (
    <section className="py-24 px-6 lg:px-12 max-w-7xl mx-auto">
      {(eyebrow || title) && (
        <motion.div
          ref={headerRef}
          initial={{ opacity: 0, y: 24 }}
          animate={headerInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.7 }}
          className="text-center mb-16"
        >
          {eyebrow && <p className="text-gold/70 tracking-[0.5em] uppercase text-[10px] mb-4">{eyebrow}</p>}
          {title && <h2 className="font-serif text-4xl md:text-5xl text-white font-light">{title}</h2>}
          <div className="w-16 h-px bg-gold mx-auto mt-6" />
        </motion.div>
      )}

      {loading ? (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-5 md:gap-8">
          {Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="animate-pulse bg-dark-100" style={{ aspectRatio: '3/4' }} />
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-5 md:gap-8">
          {categories.slice(0, 4).map((item, i) => (
            <CollectionCard key={item.id} item={item} index={i} cardCta={cardCta} />
          ))}
        </div>
      )}
    </section>
  )
}
