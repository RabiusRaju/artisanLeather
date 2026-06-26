import { useState, useRef, useEffect } from 'react'
import { useParams, useSearchParams, Link } from 'react-router-dom'
import SEO from '../components/SEO'
import { motion, AnimatePresence, useInView } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import { useCurrency } from '../context/CurrencyContext'
import { useWishlist } from '../context/WishlistContext'
import { HiHeart } from 'react-icons/hi'
import { PiWalletThin, PiHandbagThin, PiBeltThin, PiSparkleThin } from 'react-icons/pi'
import { useProducts }  from '../hooks/useProducts'
import { useBrands }   from '../hooks/useBrands'
import { useCategories } from '../hooks/useCategories'

// Fallback icons for known categories when no image is set
const CATEGORY_ICONS = {
  wallets:     PiWalletThin,
  bags:        PiHandbagThin,
  belts:       PiBeltThin,
  accessories: PiSparkleThin,
}

// ── Skeleton card ───────────────────────────────────────────────────────────
function SkeletonCard() {
  return (
    <div className="animate-pulse">
      <div className="bg-dark-100 rounded-none" style={{ aspectRatio: '3/4' }} />
      <div className="mt-4 space-y-2">
        <div className="h-2 bg-dark-50 w-1/3 rounded" />
        <div className="h-4 bg-dark-50 w-2/3 rounded" />
        <div className="h-3 bg-dark-50 w-1/4 rounded" />
      </div>
    </div>
  )
}

// ── Product card ────────────────────────────────────────────────────────────
function ProductCard({ product, index }) {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, margin: '-60px' })
  const { format }   = useCurrency()
  const { t, i18n }  = useTranslation()
  const { toggle: toggleWishlist, isInWishlist } = useWishlist()
  const isAr         = i18n.language === 'ar'
  const name         = isAr && product.name_ar ? product.name_ar : product.name
  const firstImage   = product.images?.[0]?.url

  const handleWishlistClick = (e) => {
    e.preventDefault()
    e.stopPropagation()
    toggleWishlist(product.id, product)
  }

  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, y: 36 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.6, delay: (index % 6) * 0.08 }}
      className="group"
    >
      <Link to={`/product/${product.slug}`}>
        {/* Image */}
        <div
          className="relative overflow-hidden bg-dark-100"
          style={{ aspectRatio: '3/4' }}
        >
          {firstImage && (
            <img
              src={firstImage}
              alt={name}
              className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
            />
          )}

          {product.badge && (
            <div className="absolute top-4 left-4 z-10 bg-gold text-dark text-[9px] tracking-[0.25em] uppercase px-3 py-1 font-semibold">
              {product.badge === 'bestseller' ? t('common.bestseller') : t('common.new')}
            </div>
          )}
          {product.brand && (
            <div className="absolute top-4 right-4 z-10 bg-dark/80 border border-gold/30 text-gold/70 text-[8px] tracking-[0.2em] uppercase px-2.5 py-1 backdrop-blur-sm">
              {product.brand.name}
            </div>
          )}

          <button
            onClick={handleWishlistClick}
            aria-label="Toggle wishlist"
            className="absolute bottom-4 right-4 z-10 w-8 h-8 flex items-center justify-center bg-dark/70 border border-white/10 backdrop-blur-sm hover:border-gold/40 transition-colors duration-300"
          >
            <HiHeart size={14} className={isInWishlist(product.id) ? 'text-gold' : 'text-white/50'} />
          </button>

          <div className="absolute inset-5 border border-dashed border-white/[0.04] pointer-events-none" />

          <div className="absolute top-3 left-3 w-5 h-5 border-t border-l border-gold/20 group-hover:border-gold/60 transition-colors duration-500" />
          <div className="absolute top-3 right-3 w-5 h-5 border-t border-r border-gold/20 group-hover:border-gold/60 transition-colors duration-500" />
          <div className="absolute bottom-3 left-3 w-5 h-5 border-b border-l border-gold/20 group-hover:border-gold/60 transition-colors duration-500" />
          <div className="absolute bottom-3 right-3 w-5 h-5 border-b border-r border-gold/20 group-hover:border-gold/60 transition-colors duration-500" />

          <div className="absolute inset-0 bg-dark/50 flex flex-col items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-400">
            <span className="border border-gold text-gold px-6 py-2 text-[10px] tracking-[0.3em] uppercase">
              {t('product.viewDetails')}
            </span>
            <div className="flex gap-2">
              {product.colors?.slice(0, 3).map((c) => (
                <span key={c.hex} className="w-3 h-3 rounded-full border border-white/30"
                  style={{ backgroundColor: c.hex }} title={c.name} />
              ))}
            </div>
          </div>
        </div>

        {/* Info */}
        <div className="mt-4 px-0.5">
          <p className="text-white/30 text-[9px] tracking-[0.35em] uppercase mb-1.5">
            {product.material}
          </p>
          <h3 className="font-serif text-lg leading-tight text-white group-hover:text-gold transition-colors duration-300">
            {name}
          </h3>
          <div className="flex items-center justify-between mt-2">
            <p className="text-gold text-sm">{format(product.price)}</p>
            <span className="text-gold/0 group-hover:text-gold/60 text-lg transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">→</span>
          </div>
        </div>
      </Link>
    </motion.div>
  )
}

// ── Page ────────────────────────────────────────────────────────────────────
export default function CollectionsPage() {
  const { category }  = useParams()
  const [searchParams, setSearchParams] = useSearchParams()
  const [sortBy,      setSortBy]      = useState('default')
  const [sortOpen,    setSortOpen]    = useState(false)
  const [brandOpen,   setBrandOpen]   = useState(false)
  const [brandFilter, setBrandFilter] = useState(() => searchParams.get('brand') || '')
  const sortRef  = useRef(null)
  const brandRef = useRef(null)
  const { t } = useTranslation()
  const { brands } = useBrands()
  const { categories } = useCategories()

  // Close dropdowns on outside click
  useEffect(() => {
    const handler = (e) => {
      if (sortRef.current && !sortRef.current.contains(e.target)) setSortOpen(false)
      if (brandRef.current && !brandRef.current.contains(e.target)) setBrandOpen(false)
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [])

  const selectBrand = (slug) => {
    setBrandFilter(slug)
    setBrandOpen(false)
    setSearchParams(slug ? { brand: slug } : {}, { replace: true })
  }

  const sortOptions = [
    { id: 'default',    label: t('collections.featured') },
    { id: 'price_asc',  label: t('collections.priceLowHigh') },
    { id: 'price_desc', label: t('collections.priceHighLow') },
    { id: 'name_asc',   label: t('collections.nameAZ') },
  ]

  // Build API params
  const apiParams = {}
  if (category)               apiParams.category = category
  if (sortBy !== 'default')   apiParams.sort     = sortBy
  if (brandFilter)            apiParams.brand    = brandFilter

  const { products, loading } = useProducts(apiParams)

  const activeCategory = categories.find(c => c.slug === category)
  const categoryLabel = category
    ? (activeCategory?.name || t(`collections.${category}`, { defaultValue: category.charAt(0).toUpperCase() + category.slice(1) }))
    : t('collections.allPieces')
  const activeBrand = brands.find(b => b.slug === brandFilter)

  const seoTitle = activeBrand
    ? `${activeBrand.name} — Handcrafted Leather`
    : category
      ? `${categoryLabel} — Handcrafted Leather Goods`
      : 'All Collections — Handcrafted Leather'
  const seoDesc = activeBrand
    ? `Shop ${activeBrand.name} — handcrafted leather goods made by artisans in Muscat, Oman. Free delivery across Oman and GCC.`
    : category
      ? `Browse our handcrafted leather ${category} collection. Premium quality, made by artisans in Muscat, Oman. Free delivery across Oman and GCC.`
      : 'Explore the full Artisan Leather collection — wallets, bags, belts and accessories. All handcrafted in Muscat, Oman. Free delivery across Oman and GCC.'
  const seoUrl = (category ? `/collections/${category}` : '/collections') + (brandFilter ? `?brand=${brandFilter}` : '')

  return (
    <div className="min-h-screen bg-dark">
      <SEO
        title={seoTitle}
        description={seoDesc}
        url={seoUrl}
      />
      {/* Page Hero */}
      <section className="relative pt-40 pb-20 px-6 lg:px-12 border-b border-gold/10 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-b from-dark-100 to-dark" />
        <div className="absolute left-1/3 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-gold/8 to-transparent pointer-events-none" />
        <div className="absolute right-1/3 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-gold/8 to-transparent pointer-events-none" />

        <div className="relative max-w-7xl mx-auto text-center">
          <motion.p initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }}
            className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-5">
            {t('contact.eyebrow')}
          </motion.p>
          <motion.h1 initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
            className="font-serif text-5xl md:text-6xl text-white font-light">
            {categoryLabel}
          </motion.h1>
          <motion.div initial={{ scaleX: 0 }} animate={{ scaleX: 1 }}
            transition={{ delay: 0.3, duration: 0.6 }}
            className="w-16 h-px bg-gold mx-auto mt-6 mb-8 origin-center" />
          <motion.p initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.4 }}
            className="text-white/35 text-sm tracking-wide">
            {loading ? '—' : `${products.length} ${products.length === 1 ? t('collections.piece') : t('collections.pieces')}`}
          </motion.p>
        </div>
      </section>

      <div className="max-w-7xl mx-auto px-6 lg:px-12 py-12">
        {/* Category highlights */}
        <div className="flex gap-6 overflow-x-auto pb-2 mb-10 -mx-6 px-6 lg:mx-0 lg:px-0 scrollbar-hide snap-x snap-mandatory justify-center">
          {[
            { id: 'all', label: t('collections.allPieces'), path: '/collections', image: null },
            ...categories.map(c => ({ id: c.slug, label: c.name, path: `/collections/${c.slug}`, image: c.image })),
          ].map((cat) => {
            const active = (category || 'all') === cat.id
            const Icon = CATEGORY_ICONS[cat.id]
            return (
              <Link key={cat.id} to={cat.path}
                className="flex flex-col items-center gap-2.5 flex-shrink-0 snap-start group">
                <span className={`relative w-16 h-16 lg:w-20 lg:h-20 rounded-full p-[3px] transition-colors duration-300 ${
                  active ? 'bg-gold' : 'bg-white/10 group-hover:bg-gold/40'
                }`}>
                  <span className="flex items-center justify-center w-full h-full rounded-full bg-dark overflow-hidden border-2 border-dark">
                    {cat.id === 'all' ? (
                      <img src="/logo-icon-transparent.png" alt="" className="w-2/3 h-2/3 object-contain" />
                    ) : cat.image ? (
                      <img src={cat.image} alt="" className="w-full h-full object-cover" />
                    ) : Icon ? (
                      <Icon className="w-2/5 h-2/5 text-gold/70" />
                    ) : (
                      <span className="font-serif text-xl text-gold/70">{cat.label.charAt(0)}</span>
                    )}
                  </span>
                </span>
                <span className={`text-[9px] tracking-[0.2em] uppercase transition-colors duration-300 whitespace-nowrap ${
                  active ? 'text-gold' : 'text-white/45 group-hover:text-gold'
                }`}>
                  {cat.label}
                </span>
              </Link>
            )
          })}
        </div>

        {/* Brand filter + Sort bar */}
        <div className="flex items-center justify-between gap-4 mb-14 pb-6 border-b border-white/5">
          {/* Brand / Collection filter (only shows when brands exist) */}
          {brands.length > 0 ? (
            <div ref={brandRef} className="relative">
              <button onClick={() => setBrandOpen(!brandOpen)}
                className="flex items-center gap-3 border border-white/15 hover:border-gold/30 text-white/50 hover:text-gold px-5 py-2 text-[10px] tracking-[0.25em] uppercase transition-all duration-300">
                {brandFilter ? brands.find(b => b.slug === brandFilter)?.name : t('collections.allBrands')}
                <span className={`transition-transform duration-300 ${brandOpen ? 'rotate-180' : ''}`}>▾</span>
              </button>
              <AnimatePresence>
                {brandOpen && (
                  <motion.div initial={{ opacity: 0, y: -8 }} animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: -8 }} transition={{ duration: 0.2 }}
                    className="absolute left-0 top-full mt-2 w-60 bg-dark-100 border border-gold/15 z-30 max-h-80 overflow-y-auto">
                    <button onClick={() => selectBrand('')}
                      className={`w-full text-left px-5 py-3 text-[10px] tracking-[0.25em] uppercase transition-colors duration-200 ${
                        !brandFilter ? 'text-gold bg-gold/5' : 'text-white/40 hover:text-gold hover:bg-white/5'
                      }`}>
                      {t('collections.allBrands')}
                    </button>
                    {brands.map(b => (
                      <button key={b.id}
                        onClick={() => selectBrand(b.slug)}
                        className={`w-full flex items-center gap-2.5 text-left px-5 py-3 text-[10px] tracking-[0.25em] uppercase transition-colors duration-200 ${
                          brandFilter === b.slug ? 'text-gold bg-gold/5' : 'text-white/40 hover:text-gold hover:bg-white/5'
                        }`}>
                        {b.logo && <img src={b.logo} alt="" className="w-4 h-4 rounded-full object-cover flex-shrink-0" />}
                        <span className="truncate">{b.name}</span>
                      </button>
                    ))}
                  </motion.div>
                )}
              </AnimatePresence>
            </div>
          ) : <div />}

          {/* Sort */}
          <div ref={sortRef} className="relative">
            <button onClick={() => setSortOpen(!sortOpen)}
              className="flex items-center gap-3 border border-white/15 hover:border-gold/30 text-white/50 hover:text-gold px-5 py-2 text-[10px] tracking-[0.25em] uppercase transition-all duration-300">
              {sortOptions.find(s => s.id === sortBy)?.label}
              <span className={`transition-transform duration-300 ${sortOpen ? 'rotate-180' : ''}`}>▾</span>
            </button>
            <AnimatePresence>
              {sortOpen && (
                <motion.div initial={{ opacity: 0, y: -8 }} animate={{ opacity: 1, y: 0 }}
                  exit={{ opacity: 0, y: -8 }} transition={{ duration: 0.2 }}
                  className="absolute right-0 top-full mt-2 w-52 bg-dark-100 border border-gold/15 z-30">
                  {sortOptions.map((opt) => (
                    <button key={opt.id} onClick={() => { setSortBy(opt.id); setSortOpen(false) }}
                      className={`w-full text-left px-5 py-3 text-[10px] tracking-[0.25em] uppercase transition-colors duration-200 ${
                        sortBy === opt.id ? 'text-gold bg-gold/5' : 'text-white/40 hover:text-gold hover:bg-white/5'
                      }`}>
                      {opt.label}
                    </button>
                  ))}
                </motion.div>
              )}
            </AnimatePresence>
          </div>
        </div>

        {/* Product grid */}
        {loading ? (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-14">
            {Array.from({ length: 8 }).map((_, i) => <SkeletonCard key={i} />)}
          </div>
        ) : products.length === 0 ? (
          <div className="py-32 text-center">
            <p className="text-white/25 text-sm tracking-widest uppercase">{t('collections.noPiecesFound')}</p>
          </div>
        ) : (
          <motion.div key={category + sortBy} initial={{ opacity: 0 }} animate={{ opacity: 1 }}
            transition={{ duration: 0.3 }}
            className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-14">
            {products.map((p, i) => <ProductCard key={p.id} product={p} index={i} />)}
          </motion.div>
        )}
      </div>
    </div>
  )
}
