import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import SEO from '../components/SEO'
import { useCurrency } from '../context/CurrencyContext'
import { fetchSharedProducts } from '../services/api'

function ProductCard({ product, index }) {
  const { format } = useCurrency()
  const { i18n } = useTranslation()
  const isAr = i18n.language?.startsWith('ar')
  const name = isAr && product.name_ar ? product.name_ar : product.name
  const firstImage = product.images?.[0]?.url

  return (
    <motion.div
      initial={{ opacity: 0, y: 24 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, delay: (index % 8) * 0.06 }}
      className="group"
    >
      <Link to={`/product/${product.slug}`}>
        <div className="relative overflow-hidden bg-dark-100" style={{ aspectRatio: '3/4' }}>
          {firstImage && (
            <img
              src={firstImage}
              alt={name}
              className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
            />
          )}
        </div>
        <div className="mt-4 px-0.5">
          <h3 className="font-serif text-lg leading-tight text-white group-hover:text-gold transition-colors duration-300">
            {name}
          </h3>
          <p className="text-gold text-sm mt-2">{format(product.price)}</p>
        </div>
      </Link>
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
    <div className="min-h-screen bg-dark pb-24">
      <SEO title={data.name || 'Shared Products'} description="A curated selection of products from Artisan Leather." noIndex />

      <section className="pt-36 pb-12 px-6 lg:px-12 border-b border-gold/10 bg-dark-100">
        <div className="max-w-6xl mx-auto">
          <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-3">Artisan Leather</p>
          <h1 className="font-serif text-4xl md:text-5xl text-white font-light">
            {data.name || t('common.curatedForYou', 'A Curated Selection')}
          </h1>
        </div>
      </section>

      <div className="max-w-6xl mx-auto px-6 lg:px-12 py-12">
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-12">
          {data.products.map((product, i) => (
            <ProductCard key={product.id} product={product} index={i} />
          ))}
        </div>
      </div>
    </div>
  )
}
