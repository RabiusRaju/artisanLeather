import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import SEO from '../components/SEO'
import { useCurrency } from '../context/CurrencyContext'
import { fetchSharedProducts } from '../services/api'

function CatalogueRow({ product, index }) {
  const { format } = useCurrency()
  const { i18n } = useTranslation()
  const isAr = i18n.language?.startsWith('ar')
  const name = isAr && product.name_ar ? product.name_ar : product.name
  const firstImage = product.images?.[0]?.url
  const tiers = product.bulk_pricing || []

  const specs = [
    { label: 'Product Code', value: product.sku },
    { label: 'Category', value: product.category?.name },
    { label: 'Material', value: product.material },
    { label: 'Size', value: product.dimensions },
  ].filter((s) => s.value)

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, delay: (index % 8) * 0.06 }}
      className="grid grid-cols-1 sm:grid-cols-[200px_1fr] gap-6 sm:gap-10 py-10 border-b border-white/10 print:py-6 print:border-gold/20"
    >
      <Link to={`/product/${product.slug}`} className="block">
        <div className="relative overflow-hidden bg-dark-100 w-full sm:w-[200px]" style={{ aspectRatio: '3/4' }}>
          {firstImage && (
            <img
              src={firstImage}
              alt={name}
              className="absolute inset-0 w-full h-full object-cover"
            />
          )}
        </div>
      </Link>

      <div className="flex flex-col">
        <p className="text-gold/50 text-[10px] tracking-[0.3em] uppercase mb-1">
          {String(index + 1).padStart(2, '0')}
        </p>
        <Link to={`/product/${product.slug}`}>
          <h3 className="font-serif text-2xl text-white hover:text-gold transition-colors duration-300">
            {name}
          </h3>
        </Link>
        {product.tagline && (
          <p className="text-white/40 text-sm italic mt-1 mb-5">{product.tagline}</p>
        )}

        {specs.length > 0 && (
          <dl className="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-3 mb-6">
            {specs.map((s) => (
              <div key={s.label}>
                <dt className="text-[9px] tracking-[0.25em] uppercase text-white/30 mb-1">{s.label}</dt>
                <dd className="text-white/70 text-sm">{s.value}</dd>
              </div>
            ))}
          </dl>
        )}

        <p className="font-serif text-2xl text-gold font-light mb-0">{format(product.price)}</p>

        {tiers.length > 0 && (
          <div className="mt-5 border border-gold/15 divide-y divide-gold/10">
            <p className="text-[9px] tracking-[0.25em] uppercase text-white/30 px-4 pt-3 pb-2">
              Bulk / Wholesale Pricing
            </p>
            {tiers.map((tier, i) => (
              <div key={i} className="flex items-center justify-between px-4 py-2.5 text-sm">
                <span className="text-white/55">{tier.label}</span>
                <span className="text-gold/90">{tier.price}</span>
              </div>
            ))}
          </div>
        )}
      </div>
    </motion.div>
  )
}

export default function SharedProductsPage() {
  const { token } = useParams()
  const { t } = useTranslation()
  const [data, setData] = useState(null)
  const [error, setError] = useState(false)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchSharedProducts(token)
      .then((res) => setData(res.data.data))
      .catch(() => setError(true))
      .finally(() => setLoading(false))
  }, [token])

  if (loading) {
    return <div className="min-h-screen bg-dark" />
  }

  if (error || !data || data.products.length === 0) {
    return (
      <div className="min-h-screen bg-dark flex items-center justify-center">
        <div className="text-center px-6">
          <p className="text-5xl mb-6">🔗</p>
          <p className="font-serif text-2xl text-white/40 font-light mb-6">This link is invalid or has expired.</p>
          <Link to="/" className="text-gold text-sm tracking-widest uppercase">← artisanleatherom.com</Link>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-dark pb-24 print:bg-white">
      <SEO title={data.name || 'Shared Products'} description="A curated selection of products from Artisan Leather." noIndex />

      <section className="pt-36 pb-12 px-6 lg:px-12 border-b border-gold/10 bg-dark-100 print:pt-6 print:bg-white print:border-b-2 print:border-gold">
        <div className="max-w-4xl mx-auto flex items-end justify-between gap-6 flex-wrap">
          <div>
            <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-3">Artisan Leather — Oman</p>
            <h1 className="font-serif text-4xl md:text-5xl text-white font-light print:text-dark">
              {data.name || t('common.curatedForYou', 'A Curated Selection')}
            </h1>
            <p className="text-white/30 text-xs tracking-wide mt-3 print:text-dark/50">
              {data.products.length} {data.products.length === 1 ? 'item' : 'items'} · Prices in OMR unless otherwise noted
            </p>
          </div>
          <button
            onClick={() => window.print()}
            className="print:hidden text-[10px] tracking-[0.3em] uppercase text-gold border border-gold/40 px-5 py-3 hover:bg-gold hover:text-dark transition-colors duration-300"
          >
            Print / Save PDF
          </button>
        </div>
      </section>

      <div className="max-w-4xl mx-auto px-6 lg:px-12 pt-4">
        {data.products.map((product, i) => (
          <CatalogueRow key={product.id} product={product} index={i} />
        ))}
      </div>
    </div>
  )
}
