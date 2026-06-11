import { useState, useEffect } from 'react'
import SEO from '../components/SEO'
import { Link } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import { HiHeart, HiX, HiArrowLeft } from 'react-icons/hi'
import { useCurrency } from '../context/CurrencyContext'
import { useWishlist } from '../context/WishlistContext'
import { fetchProduct } from '../services/api'

function EmptyWishlist() {
  return (
    <motion.div
      initial={{ opacity: 0, y: 24 }}
      animate={{ opacity: 1, y: 0 }}
      className="py-32 text-center"
    >
      <HiHeart size={52} className="text-gold/20 mx-auto mb-6" />
      <h2 className="font-serif text-3xl text-white font-light mb-4">Your wishlist is empty</h2>
      <p className="text-white/35 font-light mb-10 text-sm">
        Save the pieces you love and find them here later.
      </p>
      <Link
        to="/collections"
        className="inline-block px-10 py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-gold-300 transition-all duration-300"
      >
        Explore Collection
      </Link>
    </motion.div>
  )
}

function WishlistCard({ product }) {
  const { format } = useCurrency()
  const { i18n } = useTranslation()
  const { toggle } = useWishlist()
  const isAr = i18n.language === 'ar'
  const name = isAr && product.name_ar ? product.name_ar : product.name
  const firstImage = product.images?.[0]?.url

  return (
    <motion.div
      layout
      initial={{ opacity: 0, y: 16 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, scale: 0.95 }}
      transition={{ duration: 0.35 }}
      className="group relative"
    >
      <button
        onClick={() => toggle(product.id)}
        className="absolute top-3 right-3 z-10 w-8 h-8 flex items-center justify-center bg-dark/70 backdrop-blur-sm border border-white/10 text-white/60 hover:text-red-400 hover:border-red-400/40 transition-colors duration-300"
        aria-label="Remove from wishlist"
      >
        <HiX size={15} />
      </button>

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
          <p className="text-white/30 text-[9px] tracking-[0.35em] uppercase mb-1.5">
            {product.material}
          </p>
          <h3 className="font-serif text-lg leading-tight text-white group-hover:text-gold transition-colors duration-300">
            {name}
          </h3>
          <p className="text-gold text-sm mt-2">{format(product.price)}</p>
        </div>
      </Link>
    </motion.div>
  )
}

export default function WishlistPage() {
  const { productIds, products: accountProducts, loading } = useWishlist()
  const [guestProducts, setGuestProducts] = useState([])
  const [guestLoading, setGuestLoading] = useState(false)

  const isAccountWishlist = accountProducts.length > 0 || (loading && productIds.length > 0)
  const products = isAccountWishlist ? accountProducts : guestProducts

  useEffect(() => {
    if (accountProducts.length > 0) return
    if (productIds.length === 0) {
      setGuestProducts([])
      return
    }
    setGuestLoading(true)
    Promise.all(productIds.map((id) => fetchProduct(id).then((res) => res.data.data).catch(() => null)))
      .then((results) => setGuestProducts(results.filter(Boolean)))
      .finally(() => setGuestLoading(false))
  }, [productIds, accountProducts])

  const isLoading = loading || guestLoading

  return (
    <div className="min-h-screen bg-dark">
      <SEO title="Wishlist" description="Your saved Artisan Leather pieces." url="/wishlist" noIndex />

      <section className="pt-36 pb-12 px-6 lg:px-12 border-b border-gold/10 bg-dark-100">
        <div className="max-w-7xl mx-auto">
          <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }}>
            <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-3">Saved</p>
            <h1 className="font-serif text-4xl md:text-5xl text-white font-light">
              Your Wishlist
              {products.length > 0 && (
                <span className="text-white/25 text-2xl ml-4">({products.length})</span>
              )}
            </h1>
          </motion.div>
        </div>
      </section>

      <div className="max-w-7xl mx-auto px-6 lg:px-12 py-12">
        {isLoading ? (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-12">
            {Array.from({ length: 4 }).map((_, i) => (
              <div key={i} className="animate-pulse">
                <div className="bg-dark-100" style={{ aspectRatio: '3/4' }} />
                <div className="mt-4 space-y-2">
                  <div className="h-2 bg-dark-50 w-1/3 rounded" />
                  <div className="h-4 bg-dark-50 w-2/3 rounded" />
                </div>
              </div>
            ))}
          </div>
        ) : products.length === 0 ? (
          <EmptyWishlist />
        ) : (
          <>
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-12">
              <AnimatePresence>
                {products.map((product) => (
                  <WishlistCard key={product.id} product={product} />
                ))}
              </AnimatePresence>
            </div>

            <div className="mt-12 pt-6 border-t border-white/5">
              <Link
                to="/collections"
                className="flex items-center gap-2 text-white/35 hover:text-gold text-[10px] tracking-[0.25em] uppercase transition-colors duration-300 group w-fit"
              >
                <HiArrowLeft size={12} className="group-hover:-translate-x-1 transition-transform duration-300" />
                Continue Shopping
              </Link>
            </div>
          </>
        )}
      </div>
    </div>
  )
}
