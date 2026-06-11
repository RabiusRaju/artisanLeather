import { useSetting } from '../hooks/useSettings'
import { useState, useRef, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import { Helmet } from 'react-helmet-async'
import SEO from '../components/SEO'
import { motion, AnimatePresence, useInView } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import { FaWhatsapp } from 'react-icons/fa'
import { HiChevronDown, HiArrowLeft, HiCheckCircle, HiHeart, HiStar } from 'react-icons/hi'
import { useProduct }   from '../hooks/useProduct'
import { useProducts }  from '../hooks/useProducts'
import { useCart }      from '../context/CartContext'
import { useCurrency }  from '../context/CurrencyContext'
import { useWishlist }  from '../context/WishlistContext'
import { useAuth }      from '../context/AuthContext'
import { fetchProductReviews, submitReview } from '../services/api'

// ── Accordion item ─────────────────────────────────────────────────────────
function AccordionItem({ title, children, defaultOpen = false }) {
  const [open, setOpen] = useState(defaultOpen)
  return (
    <div className="border-t border-white/8">
      <button
        onClick={() => setOpen(!open)}
        className="w-full flex items-center justify-between py-4 text-left group"
      >
        <span className="text-xs tracking-[0.3em] uppercase text-white/60 group-hover:text-gold transition-colors duration-300">
          {title}
        </span>
        <HiChevronDown
          size={14}
          className={`text-gold/50 transition-transform duration-400 ${open ? 'rotate-180' : ''}`}
        />
      </button>
      <AnimatePresence initial={false}>
        {open && (
          <motion.div
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: 'auto', opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            transition={{ duration: 0.35, ease: 'easeInOut' }}
            className="overflow-hidden"
          >
            <div className="pb-5 pr-4">{children}</div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  )
}

// ── Related product card ───────────────────────────────────────────────────
function RelatedCard({ product, index }) {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, margin: '-40px' })

  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, y: 24 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.6, delay: index * 0.1 }}
      className="group"
    >
      <Link to={`/product/${product.slug}`}>
        <div className="aspect-square relative overflow-hidden bg-dark-100" style={{ background: 'linear-gradient(160deg, #2A1A08, #1A1008)' }}>
          {product.images?.[0]?.url && (
            <img src={product.images[0].url} alt={product.name}
              className="absolute inset-0 w-full h-full object-cover" />
          )}
          {product.badge && product.badge !== 'null' && (
            <div className="absolute top-3 left-3 bg-gold text-dark text-[8px] tracking-[0.2em] uppercase px-2.5 py-0.5 font-semibold">
              {product.badge}
            </div>
          )}
          <div className="absolute top-2 left-2 w-4 h-4 border-t border-l border-gold/20 group-hover:border-gold/50 transition-colors duration-500" />
          <div className="absolute bottom-2 right-2 w-4 h-4 border-b border-r border-gold/20 group-hover:border-gold/50 transition-colors duration-500" />
          <div className="absolute inset-0 bg-dark/40 opacity-0 group-hover:opacity-100 transition-opacity duration-400 flex items-center justify-center">
            <span className="border border-gold text-gold px-4 py-1.5 text-[9px] tracking-[0.3em] uppercase">
              View
            </span>
          </div>
        </div>
        <div className="mt-3 px-0.5">
          <h4 className="font-serif text-base text-white group-hover:text-gold transition-colors duration-300 leading-tight">
            {product.name}
          </h4>
          <p className="text-gold text-xs mt-1">OMR {product.price}</p>
        </div>
      </Link>
    </motion.div>
  )
}

// ── Star rating display ─────────────────────────────────────────────────────
function StarRating({ rating, size = 14, onSelect }) {
  return (
    <div className="flex items-center gap-1">
      {[1, 2, 3, 4, 5].map((i) => (
        <button
          key={i}
          type="button"
          disabled={!onSelect}
          onClick={() => onSelect && onSelect(i)}
          className={onSelect ? 'cursor-pointer' : 'cursor-default'}
        >
          <HiStar
            size={size}
            className={i <= Math.round(rating) ? 'text-gold' : 'text-white/15'}
          />
        </button>
      ))}
    </div>
  )
}

// ── Reviews section ─────────────────────────────────────────────────────────
function ReviewsSection({ product }) {
  const { user } = useAuth()
  const [reviews, setReviews]   = useState([])
  const [summary, setSummary]   = useState({ average_rating: product.average_rating, review_count: product.review_count })
  const [loading, setLoading]   = useState(true)
  const [rating, setRating]     = useState(5)
  const [title, setTitle]       = useState('')
  const [comment, setComment]   = useState('')
  const [submitting, setSubmitting] = useState(false)
  const [feedback, setFeedback] = useState(null)

  const loadReviews = () => {
    setLoading(true)
    fetchProductReviews(product.id)
      .then((res) => {
        setReviews(res.data.data)
        setSummary(res.data.summary)
      })
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadReviews()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [product.id])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setSubmitting(true)
    setFeedback(null)
    try {
      const res = await submitReview(product.id, { rating, title, comment })
      setFeedback({ type: 'success', message: res.data.message })
      setTitle('')
      setComment('')
      setRating(5)
    } catch (err) {
      setFeedback({ type: 'error', message: err.response?.data?.message || 'Something went wrong.' })
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <section className="mt-28 pt-16 border-t border-gold/10">
      <div className="mb-12">
        <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-3">Feedback</p>
        <h2 className="font-serif text-3xl text-white font-light mb-3">Customer Reviews</h2>
        <div className="flex items-center gap-3">
          <StarRating rating={summary.average_rating} size={16} />
          <span className="text-white/60 text-sm">
            {summary.average_rating > 0 ? summary.average_rating.toFixed(1) : '—'}
          </span>
          <span className="text-white/30 text-xs">
            ({summary.review_count} {summary.review_count === 1 ? 'review' : 'reviews'})
          </span>
        </div>
      </div>

      <div className="grid lg:grid-cols-3 gap-12">
        {/* Review list */}
        <div className="lg:col-span-2 space-y-6">
          {loading ? (
            <p className="text-white/30 text-sm">Loading reviews…</p>
          ) : reviews.length === 0 ? (
            <p className="text-white/30 text-sm">No reviews yet. Be the first to share your thoughts.</p>
          ) : (
            reviews.map((r) => (
              <div key={r.id} className="border-b border-white/5 pb-6">
                <div className="flex items-center justify-between mb-2">
                  <StarRating rating={r.rating} />
                  <span className="text-white/25 text-[10px] tracking-wider uppercase">{r.created_at}</span>
                </div>
                {r.title && <p className="text-white text-sm font-medium mb-1">{r.title}</p>}
                {r.comment && <p className="text-white/50 text-sm font-light leading-relaxed mb-2">{r.comment}</p>}
                <p className="text-white/30 text-[10px] tracking-[0.2em] uppercase">{r.user_name}</p>
              </div>
            ))
          )}
        </div>

        {/* Review form */}
        <div className="border border-white/8 p-6 h-fit">
          <h3 className="font-serif text-lg text-white mb-4">Write a Review</h3>
          {!user ? (
            <p className="text-white/40 text-sm font-light">
              Please <Link to="/login" className="text-gold hover:underline">sign in</Link> to write a review.
            </p>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <p className="text-[9px] tracking-[0.3em] uppercase text-white/30 mb-2">Your Rating</p>
                <StarRating rating={rating} size={20} onSelect={setRating} />
              </div>
              <input
                type="text"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                placeholder="Title (optional)"
                className="w-full bg-transparent border border-white/10 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-gold/40 focus:outline-none"
              />
              <textarea
                value={comment}
                onChange={(e) => setComment(e.target.value)}
                placeholder="Share your thoughts (optional)"
                rows={4}
                className="w-full bg-transparent border border-white/10 px-3 py-2.5 text-sm text-white placeholder:text-white/25 focus:border-gold/40 focus:outline-none resize-none"
              />
              <button
                type="submit"
                disabled={submitting}
                className="w-full py-3 bg-gold text-dark text-[10px] tracking-[0.3em] uppercase font-bold hover:bg-gold-300 transition-all duration-300 disabled:opacity-50"
              >
                {submitting ? '…' : 'Submit Review'}
              </button>
              {feedback && (
                <p className={`text-xs ${feedback.type === 'success' ? 'text-green-400' : 'text-red-400'}`}>
                  {feedback.message}
                </p>
              )}
            </form>
          )}
        </div>
      </div>
    </section>
  )
}

// ── Loading skeleton ────────────────────────────────────────────────────────
function ProductSkeleton() {
  return (
    <div className="min-h-screen bg-dark pb-24 pt-28">
      <div className="max-w-7xl mx-auto px-6 lg:px-12">
        <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 animate-pulse">
          <div className="aspect-square bg-dark-100" />
          <div className="space-y-6 pt-10">
            <div className="h-3 bg-dark-100 w-1/4" />
            <div className="h-10 bg-dark-100 w-3/4" />
            <div className="h-4 bg-dark-100 w-1/2" />
            <div className="h-8 bg-dark-100 w-1/3" />
            <div className="space-y-2 mt-8">
              {[1,2,3].map(i => <div key={i} className="h-3 bg-dark-100 w-full" />)}
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

// ── Main page ──────────────────────────────────────────────────────────────
export default function ProductPage() {
  const { slug } = useParams()
  const { product, loading, error } = useProduct(slug)

  const [activeImage, setActiveImage]   = useState(0)
  const [activeColor, setActiveColor]   = useState(0)
  const [quantity, setQuantity]         = useState(1)
  const [toastVisible, setToastVisible] = useState(false)

  const { addItem }    = useCart()
  const { format }     = useCurrency()
  const { t, i18n }   = useTranslation()
  const isAr           = i18n.language === 'ar'
  const waNumber = useSetting('business.whatsapp', '96812345678').replace(/[^0-9]/g, '')
  const { toggle: toggleWishlistItem, isInWishlist } = useWishlist()
  const [wishlistBusy, setWishlistBusy] = useState(false)

  // Fetch related (same category)
  const { products: related } = useProducts(
    product ? { category: product.category?.slug } : {}
  )
  const relatedProducts = related.filter(p => p.id !== product?.id).slice(0, 4)

  if (loading) return <ProductSkeleton />
  if (error || !product) return (
    <div className="min-h-screen bg-dark flex items-center justify-center">
      <div className="text-center">
        <p className="text-white/40 mb-6">Product not found.</p>
        <Link to="/collections" className="text-gold text-sm tracking-widest uppercase">← Back to Collections</Link>
      </div>
    </div>
  )

  const productName    = isAr && product.name_ar    ? product.name_ar    : product.name
  const productTagline = isAr && product.tagline_ar ? product.tagline_ar : product.tagline
  const categoryLabel  = product.category?.name || ''
  const categorySlug   = product.category?.slug  || ''

  const handleAddToCart = () => {
    const color = product.colors?.[activeColor]
    addItem({
      id:        product.id,
      slug:      product.slug,
      name:      product.name,
      name_ar:   product.name_ar,
      price:     parseFloat(product.price),
      category:  categorySlug,
      colorName: color?.name  || '',
      colorHex:  color?.hex   || '#1A1A1A',
      quantity,
      image:     product.images?.[0]?.url || null,
      gradient:  `linear-gradient(160deg, #2A1A08, #1A1008, #0A0704)`,
    })
    setToastVisible(true)
    setTimeout(() => setToastVisible(false), 2800)
  }

  const handleWishlistToggle = async () => {
    if (wishlistBusy) return
    setWishlistBusy(true)
    try {
      await toggleWishlistItem(product.id)
    } finally {
      setWishlistBusy(false)
    }
  }

  const waMessage = encodeURIComponent(
    `Hello Artisan Leather, I'm interested in purchasing "${product.name}" (OMR ${product.price}). Could you please provide more details?`
  )

  const productImage = product.images?.[0]?.url || 'https://artisanleatherom.com/og-image.jpg'
  const seoDesc = product.meta_description
    || `${product.name} — ${product.tagline || 'Premium handcrafted leather from Artisan Leather, Muscat Oman'}. Price: OMR ${product.price}. Free delivery across Oman and GCC.`
  const seoTitle = product.meta_title
    || `${product.name} — Handcrafted Leather | Artisan Leather Oman`

  // JSON-LD Product Schema
  const productSchema = {
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: product.name,
    description: product.tagline || seoDesc,
    image: productImage,
    brand: { '@type': 'Brand', name: product.brand?.name || 'Artisan Leather' },
    offers: {
      '@type': 'Offer',
      price: product.price,
      priceCurrency: 'OMR',
      availability: 'https://schema.org/InStock',
      seller: { '@type': 'Organization', name: 'Artisan Leather' },
    },
    ...(categoryLabel && { category: categoryLabel }),
  }

  return (
    <div className="min-h-screen bg-dark pb-24">

      <SEO
        title={seoTitle}
        description={seoDesc}
        image={productImage}
        url={`/product/${product.slug}`}
        type="product"
      />
      <Helmet>
        <script type="application/ld+json">{JSON.stringify(productSchema)}</script>
        {/* Breadcrumb schema */}
        <script type="application/ld+json">{JSON.stringify({
          '@context': 'https://schema.org',
          '@type': 'BreadcrumbList',
          itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Home',        item: 'https://artisanleatherom.com' },
            { '@type': 'ListItem', position: 2, name: 'Collections', item: 'https://artisanleatherom.com/collections' },
            ...(categoryLabel ? [{ '@type': 'ListItem', position: 3, name: categoryLabel, item: `https://artisanleatherom.com/collections/${categorySlug}` }] : []),
            { '@type': 'ListItem', position: categoryLabel ? 4 : 3, name: product.name },
          ],
        })}</script>
      </Helmet>

      {/* Add-to-cart toast */}
      <AnimatePresence>
        {toastVisible && (
          <motion.div
            initial={{ opacity: 0, y: 24, x: '-50%' }}
            animate={{ opacity: 1, y: 0,  x: '-50%' }}
            exit={{ opacity: 0, y: 16, x: '-50%' }}
            transition={{ duration: 0.35 }}
            className="fixed bottom-24 left-1/2 z-50 bg-dark-100 border border-gold/30 px-6 py-4 flex items-center gap-4 shadow-2xl shadow-black/60 min-w-[300px]"
          >
            <HiCheckCircle size={20} className="text-gold flex-shrink-0" />
            <div className="flex-1">
              <p className="text-white text-sm font-medium">{product.name}</p>
              <p className="text-white/40 text-xs mt-0.5">Added to your cart</p>
            </div>
            <Link
              to="/cart"
              className="text-gold text-[10px] tracking-[0.25em] uppercase hover:underline flex-shrink-0"
            >
              View Cart →
            </Link>
          </motion.div>
        )}
      </AnimatePresence>
      {/* ── Breadcrumb ──────────────────────────────────────── */}
      <div className="max-w-7xl mx-auto px-6 lg:px-12 pt-28 pb-8">
        <motion.nav
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          className="flex items-center gap-2 text-[10px] tracking-[0.2em] uppercase text-white/30"
        >
          <Link to="/" className="hover:text-gold transition-colors">Home</Link>
          <span>/</span>
          <Link to="/collections" className="hover:text-gold transition-colors">Collections</Link>
          <span>/</span>
          <Link to={`/collections/${categorySlug}`} className="hover:text-gold transition-colors">
            {categoryLabel}
          </Link>
          <span>/</span>
          <span className="text-gold/60">{product.name}</span>
        </motion.nav>
      </div>

      {/* ── Product layout ──────────────────────────────────── */}
      <div className="max-w-7xl mx-auto px-6 lg:px-12">
        <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">

          {/* LEFT — Image gallery */}
          <motion.div
            initial={{ opacity: 0, x: -24 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8 }}
            className="lg:sticky lg:top-28"
          >
            {/* Main image */}
            <AnimatePresence mode="wait">
              <motion.div
                key={activeImage}
                initial={{ opacity: 0, scale: 0.98 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0 }}
                transition={{ duration: 0.35 }}
                className="aspect-square relative overflow-hidden"
                style={{ background: 'linear-gradient(160deg, #2A1A08, #1A1008)' }}
              >
                {/* Product photo */}
                {product.images?.[activeImage] && (
                  <img
                    src={product.images?.[activeImage]?.url}
                    alt={`${product.name} - view ${activeImage + 1}`}
                    className="absolute inset-0 w-full h-full object-cover"
                  />
                )}

                {/* Badge */}
                {product.badge && product.badge !== 'null' && (
                  <div className="absolute top-5 left-5 z-10 bg-gold text-dark text-[9px] tracking-[0.25em] uppercase px-3 py-1 font-semibold">
                    {product.badge}
                  </div>
                )}

                {/* Gold corner marks */}
                <div className="absolute top-4 left-4 w-7 h-7 border-t-2 border-l-2 border-gold/30" />
                <div className="absolute top-4 right-4 w-7 h-7 border-t-2 border-r-2 border-gold/30" />
                <div className="absolute bottom-4 left-4 w-7 h-7 border-b-2 border-l-2 border-gold/30" />
                <div className="absolute bottom-4 right-4 w-7 h-7 border-b-2 border-r-2 border-gold/30" />

                {/* View indicator */}
                <div className="absolute bottom-5 right-5 text-[9px] tracking-[0.3em] uppercase text-white/20">
                  {['Front', 'Side', 'Open', 'Detail'][activeImage]}
                </div>
              </motion.div>
            </AnimatePresence>

            {/* Thumbnails */}
            <div className="grid grid-cols-4 gap-3 mt-3">
              {(product.images || []).map((img, i) => (
                <button
                  key={i}
                  onClick={() => setActiveImage(i)}
                  style={{ background: 'linear-gradient(160deg, #2A1A08, #1A1008)' }}
                  className={`aspect-square relative overflow-hidden transition-all duration-300 ${
                    activeImage === i
                      ? 'ring-1 ring-gold ring-offset-1 ring-offset-dark'
                      : 'opacity-50 hover:opacity-80'
                  }`}
                >
                  {product.images?.[i] && (
                    <img
                      src={img.url}
                      alt={`${product.name} view ${i + 1}`}
                      className="absolute inset-0 w-full h-full object-cover"
                    />
                  )}
                  <div className="absolute bottom-1.5 left-0 right-0 text-center text-[7px] tracking-widest uppercase text-white/40 z-10">
                    {['Front', 'Side', 'Open', 'Detail'][i]}
                  </div>
                </button>
              ))}
            </div>
          </motion.div>

          {/* RIGHT — Product info */}
          <motion.div
            initial={{ opacity: 0, x: 24 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8, delay: 0.1 }}
            className="pt-0 lg:pt-2"
          >
            {/* Category + Brand + Badge */}
            <div className="flex items-center flex-wrap gap-2 mb-5">
              <Link
                to={`/collections/${categorySlug}`}
                className="text-[9px] tracking-[0.4em] uppercase text-gold/60 hover:text-gold transition-colors"
              >
                {categoryLabel}
              </Link>
              {product.brand && (
                <>
                  <span className="text-white/15">·</span>
                  <Link
                    to={`/collections?brand=${product.brand.slug}`}
                    className="text-[9px] tracking-[0.3em] uppercase text-white/40 border border-white/15 px-2.5 py-0.5 hover:border-gold/40 hover:text-gold transition-colors"
                  >
                    {isAr && product.brand.name_ar ? product.brand.name_ar : product.brand.name}
                  </Link>
                </>
              )}
              {product.badge && product.badge !== 'null' && (
                <>
                  <span className="text-white/15">·</span>
                  <span className="text-[9px] tracking-[0.3em] uppercase text-gold bg-gold/10 px-2.5 py-0.5">
                    {product.badge}
                  </span>
                </>
              )}
            </div>

            {/* Name + wishlist */}
            <div className="flex items-start justify-between gap-4 mb-3">
              <h1 className="font-serif text-4xl md:text-5xl text-white font-light leading-tight">
                {productName}
              </h1>
              <button
                onClick={handleWishlistToggle}
                disabled={wishlistBusy}
                aria-label="Toggle wishlist"
                className="flex-shrink-0 mt-2 w-10 h-10 flex items-center justify-center border border-white/10 hover:border-gold/40 transition-colors duration-300 disabled:opacity-50"
              >
                <HiHeart
                  size={18}
                  className={isInWishlist(product.id) ? 'text-gold' : 'text-white/40'}
                />
              </button>
            </div>

            {/* Tagline */}
            <p className="font-serif text-lg text-white/40 italic font-light mb-4">
              {productTagline}
            </p>

            {/* Average rating */}
            {product.review_count > 0 && (
              <div className="flex items-center gap-2 mb-6">
                <StarRating rating={product.average_rating} size={14} />
                <span className="text-white/40 text-xs">
                  {product.average_rating.toFixed(1)} ({product.review_count})
                </span>
              </div>
            )}

            {/* Divider + price */}
            <div className="flex items-center gap-6 mb-8">
              <div className="w-10 h-px bg-gold" />
              <span className="font-serif text-3xl text-gold font-light">
                {format(product.price)}
              </span>
            </div>

            {/* Description */}
            <p className="text-white/55 leading-relaxed font-light mb-8 text-[15px]">
              {product.description}
            </p>

            {/* Material */}
            <div className="flex items-center gap-3 mb-8">
              <span className="text-[9px] tracking-[0.35em] uppercase text-white/30">Material</span>
              <span className="text-[9px] tracking-[0.2em] uppercase text-white/60 border border-white/10 px-3 py-1">
                {product.material}
              </span>
            </div>

            {/* Color selector */}
            {(product.colors?.length ?? 0) > 1 && (
              <div className="mb-8">
                <p className="text-[9px] tracking-[0.35em] uppercase text-white/30 mb-3">
                  Color — <span className="text-white/60">{product.colors?.[activeColor]?.name}</span>
                </p>
                <div className="flex gap-3">
                  {product.colors.map((color, i) => (
                    <button
                      key={color.name}
                      onClick={() => setActiveColor(i)}
                      title={color.name}
                      className={`w-7 h-7 rounded-full transition-all duration-300 ${
                        activeColor === i
                          ? 'ring-2 ring-gold ring-offset-2 ring-offset-dark scale-110'
                          : 'ring-1 ring-white/20 hover:ring-white/50'
                      }`}
                      style={{ backgroundColor: color.hex }}
                    />
                  ))}
                </div>
              </div>
            )}

            {/* Quantity */}
            <div className="mb-8">
              <p className="text-[9px] tracking-[0.35em] uppercase text-white/30 mb-3">Quantity</p>
              <div className="flex items-center border border-white/10 w-fit">
                <button
                  onClick={() => setQuantity((q) => Math.max(1, q - 1))}
                  className="px-4 py-3 text-white/50 hover:text-gold transition-colors duration-300 text-lg leading-none"
                >
                  −
                </button>
                <span className="px-6 py-3 text-white text-sm border-x border-white/10 min-w-[3rem] text-center">
                  {quantity}
                </span>
                <button
                  onClick={() => setQuantity((q) => q + 1)}
                  className="px-4 py-3 text-white/50 hover:text-gold transition-colors duration-300 text-lg leading-none"
                >
                  +
                </button>
              </div>
            </div>

            {/* CTA buttons */}
            <div className="flex flex-col gap-3 mb-10">
              <button
                onClick={handleAddToCart}
                className="w-full py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-gold-300 active:scale-[0.99] transition-all duration-300"
              >
                {t('product.addToCart')} — {format(product.price * quantity)}
              </button>
              <a
                href={`https://wa.me/${waNumber}?text=${waMessage}`}
                target="_blank"
                rel="noopener noreferrer"
                className="w-full py-4 border border-[#25D366]/60 text-[#25D366] text-[10px] tracking-[0.35em] uppercase flex items-center justify-center gap-2.5 hover:bg-[#25D366] hover:text-white hover:border-[#25D366] active:scale-[0.99] transition-all duration-300"
              >
                <FaWhatsapp size={15} /> {t('product.enquireWhatsApp')}
              </a>
            </div>

            {/* Origin */}
            <div className="flex items-center gap-3 mb-10 pb-10 border-b border-white/8">
              <div className="w-4 h-px bg-gold/50" />
              <p className="text-[10px] tracking-[0.3em] uppercase text-white/30">
                {product.origin}
              </p>
            </div>

            {/* Accordion: Details / Care / Shipping */}
            <div>
              <AccordionItem title={t('product.details')} defaultOpen>
                <ul className="space-y-2.5">
                  {(product.details || []).map((d, i) => (
                    <li key={i} className="flex items-start gap-3 text-white/45 text-sm font-light">
                      <span className="text-gold/60 mt-0.5 flex-shrink-0">—</span>
                      {isAr && d.detail_ar ? d.detail_ar : d.detail}
                    </li>
                  ))}
                </ul>
              </AccordionItem>

              <AccordionItem title={t('product.care')}>
                <p className="text-white/45 text-sm font-light leading-relaxed">{product.care}</p>
              </AccordionItem>

              <AccordionItem title={t('product.delivery')}>
                <p className="text-white/45 text-sm font-light leading-relaxed">{product.shipping}</p>
                <p className="text-white/35 text-sm font-light leading-relaxed mt-3">
                  We accept returns within 14 days of delivery for unused items in original packaging.
                </p>
              </AccordionItem>
            </div>
          </motion.div>
        </div>

        {/* ── Reviews ──────────────────────────────────────── */}
        <ReviewsSection product={product} />

        {/* ── Related products ─────────────────────────────── */}
        {relatedProducts.length > 0 && (
          <section className="mt-28 pt-16 border-t border-gold/10">
            <div className="flex items-end justify-between mb-12">
              <div>
                <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-3">You May Also Like</p>
                <h2 className="font-serif text-3xl text-white font-light">From {categoryLabel}</h2>
              </div>
              <Link
                to={`/collections/${categorySlug}`}
                className="hidden md:flex items-center gap-2 text-gold/60 hover:text-gold text-[10px] tracking-[0.3em] uppercase group transition-colors"
              >
                <span>View All</span>
                <span className="transition-transform duration-300 group-hover:translate-x-1">→</span>
              </Link>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
              {relatedProducts.map((p, i) => (
                <RelatedCard key={p.id} product={p} index={i} />
              ))}
            </div>
          </section>
        )}

        {/* ── Back link ────────────────────────────────────── */}
        <div className="mt-16 pt-8 border-t border-white/5">
          <Link
            to="/collections"
            className="inline-flex items-center gap-3 text-white/30 hover:text-gold text-[10px] tracking-[0.3em] uppercase transition-colors duration-300 group"
          >
            <HiArrowLeft
              size={12}
              className="transition-transform duration-300 group-hover:-translate-x-1"
            />
            Back to Collections
          </Link>
        </div>
      </div>
    </div>
  )
}
