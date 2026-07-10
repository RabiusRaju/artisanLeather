import { useRef } from 'react'
import { Link } from 'react-router-dom'
import { motion, useInView } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import { useCurrency }  from '../context/CurrencyContext'
import { useProducts }  from '../hooks/useProducts'
import { useSettings } from '../hooks/useSettings'

function ProductCard({ product, index, cardCta }) {
  const ref      = useRef(null)
  const isInView = useInView(ref, { once: true, margin: '-50px' })
  const { format }  = useCurrency()
  const { i18n }    = useTranslation()
  const isAr        = i18n.language === 'ar'
  const name        = isAr && product.name_ar ? product.name_ar : product.name
  const firstImage  = product.images?.[0]?.url
  const firstImageAlt = product.images?.[0]?.alt_text || name

  return (
    <motion.div ref={ref} initial={{ opacity: 0, y: 30 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.6, delay: index * 0.1 }}
      className="group cursor-pointer">
      <Link to={`/product/${product.slug}`}>
        <div className="aspect-square relative overflow-hidden bg-dark-100">
          {firstImage && (
            <img src={firstImage} alt={firstImageAlt}
              loading="lazy"
              decoding="async"
              className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
          )}
          {product.badge && (
            <div className="absolute top-3 left-3 z-10 bg-gold text-dark text-[8px] tracking-[0.2em] uppercase px-2.5 py-0.5 font-semibold">
              {product.badge}
            </div>
          )}
          <div className="absolute top-2.5 left-2.5 w-4 h-4 border-t border-l border-gold/25 group-hover:border-gold/70 transition-colors duration-400" />
          <div className="absolute bottom-2.5 right-2.5 w-4 h-4 border-b border-r border-gold/25 group-hover:border-gold/70 transition-colors duration-400" />
          {cardCta && (
            <div className="absolute inset-0 bg-dark/55 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-400">
              <span className="border border-gold text-gold px-5 py-2 text-[10px] tracking-[0.3em] uppercase">{cardCta}</span>
            </div>
          )}
        </div>
        <div className="mt-4 px-0.5">
          <p className="text-white/35 text-[10px] tracking-[0.3em] uppercase mb-1.5">{product.category?.name}</p>
          <h3 className="font-serif text-lg text-white group-hover:text-gold transition-colors duration-300 leading-tight">{name}</h3>
          <p className="text-gold text-sm mt-2">{format(product.price)}</p>
        </div>
      </Link>
    </motion.div>
  )
}

export default function BestSellers() {
  const ref      = useRef(null)
  const isInView = useInView(ref, { once: true })
  const s        = useSettings()

  const { products, loading } = useProducts({ featured: 1 })
  const eyebrow = s['home.products.eyebrow'] || ''
  const title = s['home.products.title'] || ''
  const viewAllLabel = s['home.products.view_all_label'] || ''
  const viewAllUrl = s['home.products.view_all_url'] || ''
  const cardCta = s['home.products.card_cta'] || ''

  if (!loading && products.length === 0) return null

  return (
    <section className="py-24 px-6 lg:px-12 max-w-7xl mx-auto">
      <motion.div ref={ref} initial={{ opacity: 0, y: 20 }}
        animate={isInView ? { opacity: 1, y: 0 } : {}}
        transition={{ duration: 0.7 }}
        className="flex items-end justify-between mb-16">
        <div>
          {eyebrow && <p className="text-gold/70 tracking-[0.5em] uppercase text-[10px] mb-4">{eyebrow}</p>}
          {title && <h2 className="font-serif text-4xl md:text-5xl text-white font-light">{title}</h2>}
        </div>
        {viewAllLabel && viewAllUrl && (
          <Link to={viewAllUrl}
            className="hidden md:flex items-center gap-3 text-gold text-[10px] tracking-[0.3em] uppercase group">
            <span>{viewAllLabel}</span>
            <span className="transition-all duration-300 group-hover:translate-x-1">→</span>
          </Link>
        )}
      </motion.div>

      {loading ? (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
          {Array.from({ length: 4 }).map((_, i) => (
            <div key={i} className="animate-pulse">
              <div className="aspect-square bg-dark-100" />
              <div className="mt-4 space-y-2">
                <div className="h-2 bg-dark-50 w-1/3" />
                <div className="h-4 bg-dark-50 w-2/3" />
              </div>
            </div>
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
          {products.slice(0, 4).map((p, i) => <ProductCard key={p.id} product={p} index={i} cardCta={cardCta} />)}
        </div>
      )}
    </section>
  )
}
