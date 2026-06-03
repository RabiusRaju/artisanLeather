import { Link, useNavigate } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import { HiTrash, HiShoppingBag, HiArrowLeft } from 'react-icons/hi'
import { FaWhatsapp } from 'react-icons/fa'
import { useCart }     from '../context/CartContext'
import { useCurrency } from '../context/CurrencyContext'

function CartItem({ item }) {
  const { removeItem, updateQty } = useCart()
  const { format } = useCurrency()
  const { t, i18n } = useTranslation()
  const isAr = i18n.language === 'ar'
  const name = isAr && item.name_ar ? item.name_ar : item.name
  const key = `${item.id}-${item.colorName}`

  return (
    <motion.div
      layout
      initial={{ opacity: 0, y: 16 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, x: -40 }}
      transition={{ duration: 0.35 }}
      className="flex gap-5 py-6 border-b border-white/7 group"
    >
      {/* Thumbnail */}
      <Link to={`/product/${item.id}`} className="flex-shrink-0">
        <div className="w-20 h-20 md:w-24 md:h-24 relative overflow-hidden" style={{ background: item.gradient }}>
          <div className="absolute top-1.5 left-1.5 w-3 h-3 border-t border-l border-gold/30" />
          <div className="absolute bottom-1.5 right-1.5 w-3 h-3 border-b border-r border-gold/30" />
        </div>
      </Link>

      {/* Info */}
      <div className="flex-1 min-w-0">
        <div className="flex items-start justify-between gap-4">
          <div>
            <p className="text-[9px] tracking-[0.35em] uppercase text-white/30 mb-1">
              {item.category}
            </p>
            <Link
              to={`/product/${item.id}`}
              className="font-serif text-lg text-white hover:text-gold transition-colors duration-300 leading-tight block"
            >
              {item.name}
            </Link>
            <div className="flex items-center gap-2 mt-1.5">
              <span
                className="w-3 h-3 rounded-full border border-white/20 flex-shrink-0"
                style={{ backgroundColor: item.colorHex }}
              />
              <span className="text-white/35 text-xs">{item.colorName}</span>
            </div>
          </div>

          {/* Remove */}
          <button
            onClick={() => removeItem(key)}
            className="text-white/20 hover:text-red-400 transition-colors duration-300 flex-shrink-0 p-1"
            aria-label="Remove item"
          >
            <HiTrash size={16} />
          </button>
        </div>

        {/* Qty + Price */}
        <div className="flex items-center justify-between mt-4">
          {/* Qty stepper */}
          <div className="flex items-center border border-white/10">
            <button
              onClick={() => updateQty(key, item.quantity - 1)}
              className="px-3 py-1.5 text-white/40 hover:text-gold transition-colors text-base leading-none"
            >
              −
            </button>
            <span className="px-4 py-1.5 text-white text-sm border-x border-white/10 min-w-[2.5rem] text-center">
              {item.quantity}
            </span>
            <button
              onClick={() => updateQty(key, item.quantity + 1)}
              className="px-3 py-1.5 text-white/40 hover:text-gold transition-colors text-base leading-none"
            >
              +
            </button>
          </div>

          <div className="text-right">
            <p className="text-gold font-medium">
              {format(item.price * item.quantity)}
            </p>
            {item.quantity > 1 && (
              <p className="text-white/25 text-xs mt-0.5">
                {format(item.price)} each
              </p>
            )}
          </div>
        </div>
      </div>
    </motion.div>
  )
}

function EmptyCart() {
  return (
    <motion.div
      initial={{ opacity: 0, y: 24 }}
      animate={{ opacity: 1, y: 0 }}
      className="py-32 text-center"
    >
      <HiShoppingBag size={52} className="text-gold/20 mx-auto mb-6" />
      <h2 className="font-serif text-3xl text-white font-light mb-4">Your cart is empty</h2>
      <p className="text-white/35 font-light mb-10 text-sm">
        You haven't added any pieces yet. Explore our collection.
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

export default function CartPage() {
  const { items, subtotal, clearCart } = useCart()
  const { format } = useCurrency()
  const { t }      = useTranslation()
  const navigate   = useNavigate()
  const shipping   = 0
  const total      = subtotal + shipping

  // WhatsApp order message
  const waLines   = items.map((i) => `• ${i.name} (${i.colorName}) × ${i.quantity} — OMR ${(i.price * i.quantity).toFixed(3)}`).join('\n')
  const waMessage = encodeURIComponent(
    `Hello Artisan Leather, I'd like to place an order:\n\n${waLines}\n\nTotal: OMR ${total.toFixed(3)}`
  )

  return (
    <div className="min-h-screen bg-dark">

      {/* Header */}
      <section className="pt-36 pb-12 px-6 lg:px-12 border-b border-gold/10 bg-dark-100">
        <div className="max-w-7xl mx-auto">
          <motion.div
            initial={{ opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
          >
            <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-3">Review</p>
            <h1 className="font-serif text-4xl md:text-5xl text-white font-light">
              Your Cart
              {items.length > 0 && (
                <span className="text-white/25 text-2xl ml-4">({items.length} {items.length === 1 ? 'item' : 'items'})</span>
              )}
            </h1>
          </motion.div>
        </div>
      </section>

      <div className="max-w-7xl mx-auto px-6 lg:px-12 py-12">
        {items.length === 0 ? (
          <EmptyCart />
        ) : (
          <div className="grid lg:grid-cols-3 gap-12 lg:gap-16 items-start">

            {/* Cart items */}
            <div className="lg:col-span-2">
              <AnimatePresence>
                {items.map((item) => (
                  <CartItem key={`${item.id}-${item.colorName}`} item={item} />
                ))}
              </AnimatePresence>

              {/* Actions row */}
              <div className="flex items-center justify-between mt-8 pt-6 border-t border-white/5">
                <Link
                  to="/collections"
                  className="flex items-center gap-2 text-white/35 hover:text-gold text-[10px] tracking-[0.25em] uppercase transition-colors duration-300 group"
                >
                  <HiArrowLeft size={12} className="group-hover:-translate-x-1 transition-transform duration-300" />
                  Continue Shopping
                </Link>
                <button
                  onClick={clearCart}
                  className="text-white/25 hover:text-red-400 text-[10px] tracking-[0.25em] uppercase transition-colors duration-300"
                >
                  Clear Cart
                </button>
              </div>
            </div>

            {/* Order Summary */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.2 }}
              className="lg:sticky lg:top-28"
            >
              <div className="border border-gold/15 p-7">
                <h2 className="font-serif text-xl text-white mb-7">Order Summary</h2>

                {/* Line items summary */}
                <div className="space-y-3 mb-6">
                  {items.map((item) => (
                    <div key={`${item.id}-${item.colorName}`} className="flex justify-between text-sm">
                      <span className="text-white/45 font-light truncate max-w-[65%]">
                        {item.name}
                        <span className="text-white/25"> × {item.quantity}</span>
                      </span>
                      <span className="text-white/60 flex-shrink-0">
                        {format(item.price * item.quantity)}
                      </span>
                    </div>
                  ))}
                </div>

                <div className="border-t border-white/8 pt-5 space-y-3 mb-6">
                  <div className="flex justify-between text-sm">
                    <span className="text-white/45">Subtotal</span>
                    <span className="text-white/70">{format(subtotal)}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-white/45">Shipping</span>
                    <span className="text-gold text-[10px] tracking-wider uppercase">Free</span>
                  </div>
                </div>

                <div className="border-t border-gold/20 pt-5 mb-8">
                  <div className="flex justify-between items-baseline">
                    <span className="text-white text-sm tracking-wide">Total</span>
                    <span className="font-serif text-2xl text-gold">{format(total)}</span>
                  </div>
                  <p className="text-white/25 text-[9px] mt-1.5">Inclusive of VAT where applicable</p>
                </div>

                {/* Checkout CTA */}
                <button
                  onClick={() => navigate('/checkout')}
                  className="w-full py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-gold-300 active:scale-[0.99] transition-all duration-300 mb-3"
                >
                  Proceed to Checkout
                </button>

                {/* WhatsApp order alternative */}
                <a
                  href={`https://wa.me/96812345678?text=${waMessage}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-full py-3.5 border border-[#25D366]/50 text-[#25D366] text-[10px] tracking-[0.3em] uppercase flex items-center justify-center gap-2 hover:bg-[#25D366] hover:text-white hover:border-[#25D366] transition-all duration-300"
                >
                  <FaWhatsapp size={13} /> Order via WhatsApp
                </a>

                {/* Trust signals */}
                <div className="mt-7 pt-6 border-t border-white/5 space-y-2.5">
                  {[
                    'Free delivery across GCC',
                    'Gift-wrapped in signature black box',
                    '14-day return policy',
                  ].map((t) => (
                    <div key={t} className="flex items-center gap-2.5 text-white/30 text-xs">
                      <span className="text-gold/50">✓</span>
                      {t}
                    </div>
                  ))}
                </div>
              </div>
            </motion.div>

          </div>
        )}
      </div>
    </div>
  )
}
