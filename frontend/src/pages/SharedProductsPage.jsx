import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import SEO from '../components/SEO'
import ShareButton from '../components/ShareButton'
import { useCurrency } from '../context/CurrencyContext'
import { fetchSharedProducts } from '../services/api'

function truncate(text, max) {
  if (!text || text.length <= max) return text
  return text.slice(0, text.lastIndexOf(' ', max)).trim() + '…'
}

function ProductSlide({ product, index, reverse }) {
  const { format } = useCurrency()
  const { i18n } = useTranslation()
  const isAr = i18n.language?.startsWith('ar')
  const name = isAr && product.name_ar ? product.name_ar : product.name
  const description = isAr && product.description_ar ? product.description_ar : product.description
  const images = (product.images || []).slice(0, 4)
  const tiers = product.bulk_pricing || []
  const categoryName = product.category?.name || 'Leather Goods'
  const productUrl = `/product/${product.slug}?lang=${isAr ? 'ar' : 'en'}`

  const details = [
    { label: isAr ? 'الخامة' : 'Materials', value: product.material },
    { label: isAr ? 'المقاس' : 'Size', value: product.dimensions },
    { label: isAr ? 'الفئة' : 'Category', value: categoryName },
    { label: isAr ? 'رمز المنتج' : 'Product Code', value: product.sku },
  ].filter((d) => d.value)

  return (
    <motion.section
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, margin: '-80px' }}
      transition={{ duration: 0.45 }}
      className="py-14"
    >
      <div className={`grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center ${reverse ? 'lg:[&>*:first-child]:order-2' : ''}`}>
        {/* ── Text column ───────────────────────────────────────────── */}
        <div>
          <h2 className="font-serif text-[34px] sm:text-[42px] leading-tight font-light tracking-tight text-white mb-3">
            {name}
          </h2>

          {description && (
            <p className="text-[14px] text-white/45 leading-relaxed mb-6 max-w-md">
              {truncate(description, 160)}
            </p>
          )}

          <p className="text-[11px] font-bold tracking-[0.2em] text-gold/70 uppercase mb-3">
            {isAr ? 'تفاصيل المنتج' : 'Product Details'}
          </p>

          <dl className="space-y-1.5 mb-5">
            {details.map((d) => (
              <div key={d.label} className="flex flex-wrap gap-1 text-[14px] text-white/55">
                <dt className="font-medium text-white/35">{d.label} :</dt>
                <dd>{d.value}</dd>
              </div>
            ))}
          </dl>

          {tiers.length > 0 ? (
            <div className="text-[14px] text-white/55 mb-5 space-y-0.5">
              {tiers.map((tier, i) => (
                <p key={i}>
                  {i === 0 ? (isAr ? 'الكمية- ' : 'Quantity- ') : <span className="inline-block w-[68px]" />}
                  {tier.label} &nbsp; <span className="text-gold">{isAr ? 'السعر' : 'Price'}- {tier.price}</span> {isAr ? '(لكل لون)' : '(Per Colour)'}
                </p>
              ))}
            </div>
          ) : (
            <p className="font-serif text-2xl text-gold font-light mb-5">{format(product.price)}</p>
          )}

          <Link
            to={productUrl}
            className="inline-block text-[11px] tracking-[0.25em] uppercase text-gold border-b border-gold/40 pb-0.5 hover:text-white hover:border-white/40 transition-colors duration-300"
          >
            {isAr ? 'عرض المنتج' : 'View Product'} →
          </Link>
        </div>

        {/* ── Image column ──────────────────────────────────────────── */}
        <Link to={productUrl} className="block">
          {images.length > 1 ? (
            <div className="grid grid-cols-3 grid-rows-2 gap-2.5 h-[340px] sm:h-[420px]">
              <div className="col-span-2 row-span-2 bg-dark-100 overflow-hidden">
                <img src={images[0].url} alt={name} loading="lazy" decoding="async" className="w-full h-full object-cover" />
              </div>
              {images.slice(1, 4).map((img, i) => (
                <div key={i} className="bg-dark-100 overflow-hidden">
                  <img src={img.url} alt={name} loading="lazy" decoding="async" className="w-full h-full object-cover" />
                </div>
              ))}
            </div>
          ) : (
            <div className="bg-dark-100 overflow-hidden h-[340px] sm:h-[420px]">
              {images[0] && (
                <img src={images[0].url} alt={name} loading="lazy" decoding="async" className="w-full h-full object-cover" />
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
  const { i18n } = useTranslation()
  const isAr = i18n.language?.startsWith('ar')
  const [data, setData] = useState(null)
  const [error, setError] = useState(false)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    setLoading(true)
    setError(false)
    fetchSharedProducts(token)
      .then((res) => setData(res.data.data))
      .catch(() => setError(true))
      .finally(() => setLoading(false))
  }, [token, i18n.language])

  if (loading) {
    return <div className="min-h-screen bg-dark" />
  }

  if (error || !data || data.products.length === 0) {
    return (
      <div className="min-h-screen bg-dark flex items-center justify-center">
        <div className="text-center px-6">
          <p className="text-5xl mb-6">🔗</p>
          <p className="font-serif text-2xl text-white/40 font-light mb-6">
            {isAr ? 'هذا الرابط غير صالح أو انتهت صلاحيته.' : 'This link is invalid or has expired.'}
          </p>
          <Link to="/" className="text-gold text-sm tracking-widest uppercase">← artisanleatherom.com</Link>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-dark">
      <SEO
        title={data.name || (isAr ? 'منتجات مختارة' : 'Shared Products')}
        description={isAr ? 'مجموعة مختارة من منتجات آرتيزان ليذر.' : 'A curated selection of products from Artisan Leather.'}
        url={`/share/${token}?lang=${isAr ? 'ar' : 'en'}`}
        noIndex
      />

      {/* ── Cover slide ─────────────────────────────────────────────── */}
      <section className="px-6 lg:px-16 pt-32 pb-16 bg-dark-100">
        <div className="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
          <div className="bg-dark overflow-hidden h-[280px] sm:h-[360px]">
            {data.products[0]?.images?.[0]?.url && (
              <img
                src={data.products[0].images[0].url}
                alt={data.name || (isAr ? 'مجموعة مختارة من آرتيزان ليذر' : 'Artisan Leather curated selection')}
                loading="eager"
                decoding="async"
                fetchPriority="high"
                className="w-full h-full object-cover"
              />
            )}
          </div>
          <div>
            <div className="flex items-start justify-between gap-4 mb-5">
              <h1 className="font-serif text-[40px] sm:text-[52px] leading-[1.05] font-light tracking-tight text-white uppercase">
                {data.name || (isAr ? 'عرض المنتجات' : 'Product Presentation')}
              </h1>
              <ShareButton url={window.location.href} title={data.name || (isAr ? 'مجموعة مختارة — آرتيزان ليذر' : 'A Curated Selection — Artisan Leather')} />
            </div>
            <p className="text-[15px] text-white/40">
              {isAr ? 'اختبر جمال الحرفية في الجلد الطبيعي' : 'Experience the Art of Genuine Leather Craftsmanship'}
            </p>
          </div>
        </div>
      </section>

      {/* ── Product slides ──────────────────────────────────────────── */}
      <div className="max-w-5xl mx-auto px-6 lg:px-16 divide-y divide-gold/15">
        {data.products.map((product, i) => (
          <ProductSlide key={product.id} product={product} index={i} reverse={i % 2 === 1} />
        ))}
      </div>

      {/* ── Closing slide ───────────────────────────────────────────── */}
      <section className="px-6 lg:px-16 py-24 text-center bg-dark-100">
        <p className="font-serif text-[40px] sm:text-[56px] font-light tracking-tight text-white uppercase mb-3">
          {isAr ? 'شكرا لكم' : 'Thank You'}
        </p>
        <p className="text-[14px] text-white/40">Artisan Leather — Muscat, Oman</p>
      </section>
    </div>
  )
}
