import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import SEO from '../components/SEO'
import { useCurrency } from '../context/CurrencyContext'
import { fetchSharedProducts } from '../services/api'

// ── Running header — repeats above every product slide, like a PDF page header ──
function SlideHeader() {
  return (
    <div className="flex items-center justify-end pb-3 mb-8 border-b border-[#cfc8b8] print:mb-6">
      <span className="text-[13px] text-[#6b6b6b] tracking-wide">PRODUCT PRESENTATION</span>
    </div>
  )
}

function ProductSlide({ product, index, reverse }) {
  const { format } = useCurrency()
  const { i18n } = useTranslation()
  const isAr = i18n.language?.startsWith('ar')
  const name = isAr && product.name_ar ? product.name_ar : product.name
  const images = (product.images || []).slice(0, 4)
  const tiers = product.bulk_pricing || []
  const categoryName = product.category?.name || 'Leather Goods'

  const details = [
    { label: 'Product Name', value: name },
    { label: 'Materials', value: product.material },
    { label: 'Size', value: product.dimensions },
    { label: 'Category', value: categoryName },
    { label: 'Product Code', value: product.sku },
  ].filter((d) => d.value)

  return (
    <motion.section
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, margin: '-80px' }}
      transition={{ duration: 0.45 }}
      className="py-14 print:py-8 print:break-inside-avoid"
    >
      <SlideHeader />

      <div className={`grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center ${reverse ? 'lg:[&>*:first-child]:order-2' : ''}`}>
        {/* ── Text column ───────────────────────────────────────────── */}
        <div>
          <h2 className="inline-block bg-white text-[34px] sm:text-[42px] leading-tight font-semibold tracking-tight text-[#3a3a3a] uppercase mb-8 px-1">
            {categoryName}
          </h2>

          <p className="text-[12px] font-bold tracking-wide text-[#3a3a3a] uppercase mb-3">
            Product Details
          </p>

          <dl className="space-y-1 mb-5">
            {details.map((d) => (
              <div key={d.label} className="flex flex-wrap gap-1 text-[14px] text-[#4a4a4a]">
                <dt className="font-medium">{d.label} :</dt>
                <dd>{d.value}</dd>
              </div>
            ))}
          </dl>

          {tiers.length > 0 ? (
            <div className="text-[14px] text-[#4a4a4a] mb-5 space-y-0.5">
              {tiers.map((tier, i) => (
                <p key={i}>
                  {i === 0 ? 'Quantity- ' : <span className="inline-block w-[68px]" />}
                  {tier.label} &nbsp; Price- {tier.price} (Per Colour)
                </p>
              ))}
            </div>
          ) : (
            <p className="font-serif text-2xl text-[#3a3a3a] mb-5">{format(product.price)}</p>
          )}

          <Link
            to={`/product/${product.slug}`}
            className="print:hidden inline-block text-[11px] tracking-[0.25em] uppercase text-[#8a6d2f] border-b border-[#8a6d2f] pb-0.5 hover:text-[#3a3a3a] hover:border-[#3a3a3a] transition-colors duration-300"
          >
            View Product →
          </Link>
        </div>

        {/* ── Image column ──────────────────────────────────────────── */}
        <Link to={`/product/${product.slug}`} className="block">
          {images.length > 1 ? (
            <div className="grid grid-cols-3 grid-rows-2 gap-2.5 h-[340px] sm:h-[420px]">
              <div className="col-span-2 row-span-2 bg-[#eee9dd] overflow-hidden">
                <img src={images[0].url} alt={name} className="w-full h-full object-cover" />
              </div>
              {images.slice(1, 4).map((img, i) => (
                <div key={i} className="bg-[#eee9dd] overflow-hidden">
                  <img src={img.url} alt={name} className="w-full h-full object-cover" />
                </div>
              ))}
            </div>
          ) : (
            <div className="bg-[#eee9dd] overflow-hidden h-[340px] sm:h-[420px]">
              {images[0] && (
                <img src={images[0].url} alt={name} className="w-full h-full object-cover" />
              )}
            </div>
          )}
        </Link>
      </div>
    </motion.section>
  )
}

export default function SharedProductsPage() {
  const { token } = useParams()
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
    return <div className="min-h-screen bg-[#f3efe5]" />
  }

  if (error || !data || data.products.length === 0) {
    return (
      <div className="min-h-screen bg-[#f3efe5] flex items-center justify-center">
        <div className="text-center px-6">
          <p className="text-5xl mb-6">🔗</p>
          <p className="text-2xl text-[#6b6b6b] font-light mb-6">This link is invalid or has expired.</p>
          <Link to="/" className="text-[#8a6d2f] text-sm tracking-widest uppercase">← artisanleatherom.com</Link>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-[#f3efe5]">
      <SEO title={data.name || 'Shared Products'} description="A curated selection of products from Artisan Leather." noIndex />

      {/* ── Cover slide ─────────────────────────────────────────────── */}
      <section className="px-6 lg:px-16 pt-32 pb-16 print:pt-10">
        <div className="max-w-5xl mx-auto flex items-center justify-end pb-3 mb-10 border-b border-[#cfc8b8]">
          <span className="text-[13px] text-[#6b6b6b] tracking-wide">artisanleatherom.com</span>
        </div>

        <div className="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
          <div className="bg-[#eee9dd] overflow-hidden h-[280px] sm:h-[360px]">
            {data.products[0]?.images?.[0]?.url && (
              <img
                src={data.products[0].images[0].url}
                alt=""
                className="w-full h-full object-cover"
              />
            )}
          </div>
          <div>
            <h1 className="text-[40px] sm:text-[52px] leading-[1.05] font-bold tracking-tight text-[#3a3a3a] uppercase mb-5">
              {data.name || 'Product Presentation'}
            </h1>
            <p className="text-[15px] text-[#6b6b6b] mb-8">
              Experience the Art of Genuine Leather Craftsmanship
            </p>
            <button
              onClick={() => window.print()}
              className="print:hidden text-[10px] tracking-[0.3em] uppercase text-[#3a3a3a] border border-[#3a3a3a] px-6 py-3 hover:bg-[#3a3a3a] hover:text-white transition-colors duration-300"
            >
              Print / Save as PDF
            </button>
          </div>
        </div>
      </section>

      {/* ── Product slides ──────────────────────────────────────────── */}
      <div className="max-w-5xl mx-auto px-6 lg:px-16 divide-y divide-[#cfc8b8]">
        {data.products.map((product, i) => (
          <ProductSlide key={product.id} product={product} index={i} reverse={i % 2 === 1} />
        ))}
      </div>

      {/* ── Closing slide ───────────────────────────────────────────── */}
      <section className="px-6 lg:px-16 py-24 text-center">
        <p className="text-[40px] sm:text-[56px] font-bold tracking-tight text-[#3a3a3a] uppercase mb-3">
          Thank You
        </p>
        <p className="text-[14px] text-[#6b6b6b]">Artisan Leather — Muscat, Oman</p>
      </section>
    </div>
  )
}
