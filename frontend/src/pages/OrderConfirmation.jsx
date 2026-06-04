import { useSetting } from '../hooks/useSettings'
import SEO from '../components/SEO'
import { useLocation, Link, Navigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { HiCheckCircle } from 'react-icons/hi'
import { FaWhatsapp, FaInstagram } from 'react-icons/fa'
import { useCurrency } from '../context/CurrencyContext'

const paymentLabels = {
  cod:       'Cash on Delivery',
  bank:      'Bank Transfer',
  whatsapp:  'WhatsApp Order',
}

export default function OrderConfirmation() {
  const { state }  = useLocation()
  const { format } = useCurrency()
  const waNumber = useSetting('business.whatsapp', '96812345678').replace(/[^0-9]/g, '')

  if (!state?.orderNum) return <Navigate to="/" replace />

  const { orderNum, form, items, total, payment } = state

  const waMessage = encodeURIComponent(
    `Hello, I just placed order ${orderNum} on artisanleatherom.com. Please confirm my order.`
  )

  return (
    <div className="min-h-screen bg-dark flex flex-col items-center justify-start pt-32 pb-24 px-6">

      <SEO title="Order Confirmed — Thank You!" description="Your Artisan Leather order has been confirmed." url="/order-confirmation" noIndex />

      {/* Success icon + heading */}
      <motion.div
        initial={{ opacity: 0, scale: 0.8 }}
        animate={{ opacity: 1, scale: 1 }}
        transition={{ duration: 0.6, type: 'spring' }}
        className="text-center mb-14 max-w-xl"
      >
        {/* Animated checkmark circle */}
        <div className="relative inline-flex items-center justify-center w-20 h-20 mb-8">
          <motion.div
            initial={{ scale: 0 }}
            animate={{ scale: 1 }}
            transition={{ delay: 0.1, duration: 0.5, type: 'spring' }}
            className="absolute inset-0 rounded-full border-2 border-gold/30"
          />
          <motion.div
            initial={{ scale: 0 }}
            animate={{ scale: 1 }}
            transition={{ delay: 0.25, duration: 0.4, type: 'spring' }}
            className="absolute inset-2 rounded-full bg-gold/10"
          />
          <HiCheckCircle size={38} className="text-gold relative z-10" />
        </div>

        <motion.p
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.4 }}
          className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-4"
        >
          Order Confirmed
        </motion.p>
        <motion.h1
          initial={{ opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.5 }}
          className="font-serif text-4xl md:text-5xl text-white font-light mb-4"
        >
          Thank You,{' '}
          <span className="text-gradient-gold">{form.firstName}</span>
        </motion.h1>
        <motion.div
          initial={{ scaleX: 0 }}
          animate={{ scaleX: 1 }}
          transition={{ delay: 0.65, duration: 0.5 }}
          className="w-14 h-px bg-gold mx-auto mb-6 origin-center"
        />
        <motion.p
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.75 }}
          className="text-white/45 font-light leading-relaxed"
        >
          Your order has been received and is being prepared.
          {payment === 'bank' && ' Please complete the bank transfer to confirm shipment.'}
          {payment === 'cod'  && ' Our team will contact you to arrange delivery.'}
        </motion.p>
      </motion.div>

      {/* Order detail card */}
      <motion.div
        initial={{ opacity: 0, y: 24 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.6 }}
        className="w-full max-w-2xl border border-gold/15 p-8 mb-8"
      >
        {/* Order meta */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8 pb-8 border-b border-white/7">
          {[
            { label: 'Order Number',  value: orderNum },
            { label: 'Payment',       value: paymentLabels[payment] || payment },
            { label: 'Delivery',      value: `${form.city}, ${form.governorate}` },
            { label: 'Est. Delivery', value: '3–5 business days' },
          ].map((item) => (
            <div key={item.label}>
              <p className="text-[9px] tracking-[0.35em] uppercase text-white/25 mb-1.5">{item.label}</p>
              <p className="text-white/70 text-sm font-light">{item.value}</p>
            </div>
          ))}
        </div>

        {/* Items */}
        <h3 className="font-serif text-lg text-white mb-5">Items Ordered</h3>
        <div className="space-y-4 mb-8">
          {items.map((item) => (
            <div key={`${item.id}-${item.colorName}`} className="flex items-center gap-4">
              <div className="w-12 h-12 flex-shrink-0" style={{ background: item.gradient }} />
              <div className="flex-1 min-w-0">
                <p className="text-white/70 text-sm truncate">{item.name}</p>
                <p className="text-white/30 text-xs mt-0.5">{item.colorName} · Qty {item.quantity}</p>
              </div>
              <p className="text-white/50 text-sm flex-shrink-0">{format((item.price * item.quantity))}</p>
            </div>
          ))}
        </div>

        {/* Total */}
        <div className="border-t border-gold/15 pt-5 flex justify-between items-baseline">
          <div>
            <p className="text-white/30 text-xs mb-0.5">Shipping: <span className="text-gold/60">Free</span></p>
            <p className="text-white/50 text-sm">Order Total</p>
          </div>
          <span className="font-serif text-2xl text-gold">{format(total)}</span>
        </div>
      </motion.div>

      {/* What's next */}
      <motion.div
        initial={{ opacity: 0, y: 16 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.8 }}
        className="w-full max-w-2xl mb-10"
      >
        <p className="text-[9px] tracking-[0.4em] uppercase text-white/25 mb-5 text-center">What Happens Next</p>
        <div className="grid md:grid-cols-3 gap-4">
          {[
            { step: '01', title: 'Order Confirmed',  desc: 'You will receive a WhatsApp confirmation shortly.' },
            { step: '02', title: 'Crafted & Packed', desc: 'Your piece is wrapped in our signature black gift box.' },
            { step: '03', title: 'Delivered',        desc: 'Delivered to your door within 3–5 business days.' },
          ].map((s) => (
            <div key={s.step} className="border border-white/6 p-5 text-center group hover:border-gold/20 transition-colors duration-400">
              <p className="font-serif text-2xl text-gradient-gold mb-3">{s.step}</p>
              <p className="text-white/60 text-sm font-medium mb-1.5">{s.title}</p>
              <p className="text-white/30 text-xs font-light leading-relaxed">{s.desc}</p>
            </div>
          ))}
        </div>
      </motion.div>

      {/* Action buttons */}
      <motion.div
        initial={{ opacity: 0, y: 12 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.95 }}
        className="flex flex-col sm:flex-row gap-3 w-full max-w-md"
      >
        <Link
          to="/collections"
          className="flex-1 py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold text-center hover:bg-gold-300 transition-all duration-300"
        >
          Continue Shopping
        </Link>
        <a
          href={`https://wa.me/${waNumber}?text=${waMessage}`}
          target="_blank"
          rel="noopener noreferrer"
          className="flex-1 py-4 border border-[#25D366]/50 text-[#25D366] text-[10px] tracking-[0.3em] uppercase flex items-center justify-center gap-2 hover:bg-[#25D366] hover:text-white hover:border-[#25D366] transition-all duration-300"
        >
          <FaWhatsapp size={13} /> Track on WhatsApp
        </a>
      </motion.div>

      {/* Social nudge */}
      <motion.p
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ delay: 1.1 }}
        className="text-white/20 text-xs text-center mt-8"
      >
        Love your purchase? Tag us{' '}
        <a href="#" className="text-gold/40 hover:text-gold inline-flex items-center gap-1 transition-colors">
          <FaInstagram size={11} /> @artisanleatherom
        </a>
      </motion.p>
    </div>
  )
}
