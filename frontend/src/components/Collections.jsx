import { useRef } from 'react'
import { Link } from 'react-router-dom'
import { motion, useInView } from 'framer-motion'
import { useTranslation } from 'react-i18next'

const B = 'https://images.unsplash.com/photo-'
const Q = '?w=600&q=80&fit=crop'

const collections = [
  {
    id: 'wallets',
    titleKey: 'collections.wallets',
    subtitleKey: 'collections.subtitleWallets',
    image: `${B}1627123424574-724758594e93${Q}`,
    gradient: 'linear-gradient(160deg, #3D2B1F, #241608, #180E06)',
  },
  {
    id: 'bags',
    titleKey: 'collections.bags',
    subtitleKey: 'collections.subtitleBags',
    image: `${B}1598532163257-ae3c6b2524b6${Q}`,
    gradient: 'linear-gradient(160deg, #2B1E10, #1A1008, #100A03)',
  },
  {
    id: 'belts',
    titleKey: 'collections.belts',
    subtitleKey: 'collections.subtitleBelts',
    image: `${B}1664286074176-5206ee5dc878${Q}`,
    gradient: 'linear-gradient(160deg, #1C2B1A, #101A0F, #080E07)',
  },
  {
    id: 'accessories',
    titleKey: 'collections.accessories',
    subtitleKey: 'collections.subtitleAccessories',
    image: `${B}1611937685025-8d1df67a80b6${Q}`,
    gradient: 'linear-gradient(160deg, #1A1C2B, #0E1018, #070810)',
  },
]

function CollectionCard({ item, index }) {
  const { t } = useTranslation()
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
      <Link to={`/collections/${item.id}`}>
        {/* Card image area */}
        <div className="relative overflow-hidden" style={{ aspectRatio: '3/4', background: item.gradient }}>
          {/* Category image */}
          {item.image && (
            <img
              src={item.image}
              alt={t(item.titleKey)}
              loading="lazy"
              decoding="async"
              className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
            />
          )}
          {/* Dark overlay so text stays readable */}
          <div className="absolute inset-0 bg-dark/40 group-hover:bg-dark/25 transition-colors duration-500" />

          {/* Corner accents */}
          <div className="absolute top-3 left-3 w-5 h-5 border-t border-l border-gold/20 group-hover:border-gold/60 transition-colors duration-500" />
          <div className="absolute top-3 right-3 w-5 h-5 border-t border-r border-gold/20 group-hover:border-gold/60 transition-colors duration-500" />
          <div className="absolute bottom-3 left-3 w-5 h-5 border-b border-l border-gold/20 group-hover:border-gold/60 transition-colors duration-500" />
          <div className="absolute bottom-3 right-3 w-5 h-5 border-b border-r border-gold/20 group-hover:border-gold/60 transition-colors duration-500" />

          {/* Hover overlay with CTA */}
          <div className="absolute inset-0 bg-gold/[0.06] opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
            <span className="border border-gold text-gold px-6 py-2 text-[10px] tracking-[0.3em] uppercase opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-100">
              {t('collections.explore')}
            </span>
          </div>
        </div>

        {/* Text */}
        <div className="mt-5 px-1 flex items-end justify-between">
          <div>
            <h3 className="font-serif text-xl text-white group-hover:text-gold transition-colors duration-300">
              {t(item.titleKey)}
            </h3>
            <p className="text-white/35 text-xs tracking-wider mt-1">{t(item.subtitleKey)}</p>
          </div>
          <span className="text-gold/0 group-hover:text-gold text-xl transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
            →
          </span>
        </div>
      </Link>
    </motion.div>
  )
}

export default function Collections() {
  const { t } = useTranslation()
  const headerRef = useRef(null)
  const headerInView = useInView(headerRef, { once: true })

  return (
    <section className="py-24 px-6 lg:px-12 max-w-7xl mx-auto">
      <motion.div
        ref={headerRef}
        initial={{ opacity: 0, y: 24 }}
        animate={headerInView ? { opacity: 1, y: 0 } : {}}
        transition={{ duration: 0.7 }}
        className="text-center mb-16"
      >
        <p className="text-gold/70 tracking-[0.5em] uppercase text-[10px] mb-4">{t('collections.eyebrow')}</p>
        <h2 className="font-serif text-4xl md:text-5xl text-white font-light">{t('collections.title')}</h2>
        <div className="w-16 h-px bg-gold mx-auto mt-6" />
      </motion.div>

      <div className="grid grid-cols-2 md:grid-cols-4 gap-5 md:gap-8">
        {collections.map((item, i) => (
          <CollectionCard key={item.id} item={item} index={i} />
        ))}
      </div>
    </section>
  )
}
