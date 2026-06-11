import SEO from '../components/SEO'
import { useSetting } from '../hooks/useSettings'
import { useState } from 'react'
import { Link } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import { FaWhatsapp } from 'react-icons/fa'
import { trackOrder } from '../services/api'

const STATUS_COLORS = {
  pending:    'text-yellow-400',
  confirmed:  'text-blue-400',
  processing: 'text-blue-400',
  shipped:    'text-green-400',
  delivered:  'text-green-400',
  cancelled:  'text-red-400',
}

const STATUS_LABELS = {
  pending:    { en: 'Pending',    ar: 'في الانتظار' },
  confirmed:  { en: 'Confirmed',  ar: 'مؤكد' },
  processing: { en: 'Processing', ar: 'قيد التجهيز' },
  shipped:    { en: 'Shipped',    ar: 'تم الشحن' },
  delivered:  { en: 'Delivered',  ar: 'تم التسليم' },
  cancelled:  { en: 'Cancelled',  ar: 'ملغي' },
}

export default function TrackOrderPage() {
  const { i18n } = useTranslation()
  const isAr = i18n.language === 'ar'

  const [orderNum, setOrderNum] = useState('')
  const waNumber = useSetting('business.whatsapp', '96812345678').replace(/[^0-9]/g, '')
  const [loading,  setLoading]  = useState(false)
  const [result,   setResult]   = useState(null)
  const [error,    setError]    = useState('')

  const handleTrack = async (e) => {
    e.preventDefault()
    if (!orderNum.trim()) return
    setLoading(true)
    setError('')
    setResult(null)
    try {
      const res = await trackOrder(orderNum.trim())
      setResult(res.data)
    } catch (err) {
      if (err.response?.status === 404) {
        setError(isAr
          ? 'لم يتم العثور على الطلب. يرجى التحقق من رقم الطلب.'
          : 'Order not found. Please check your order number and try again.')
      } else {
        setError(isAr ? 'حدث خطأ. يرجى المحاولة مرة أخرى.' : 'Something went wrong. Please try again.')
      }
    } finally {
      setLoading(false)
    }
  }

  const waMsg = result
    ? encodeURIComponent(`Hello Artisan Leather, I'd like to enquire about order ${result.order_number}.`)
    : ''

  return (
    <div className="min-h-screen bg-dark pt-32 pb-24 px-6 lg:px-12">
      <SEO title="Track Your Order" description="Track your Artisan Leather order status in real-time." url="/track" />
      <div className="max-w-2xl mx-auto">

        {/* Header */}
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}
          className="text-center mb-12">
          <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-4">
            {isAr ? 'تتبع الطلب' : 'Order Tracking'}
          </p>
          <h1 className="font-serif text-4xl md:text-5xl text-white font-light mb-4">
            {isAr ? 'تتبع طلبك' : 'Track Your Order'}
          </h1>
          <div className="w-16 h-px bg-gold mx-auto mb-6" />
          <p className="text-white/45 font-light text-sm">
            {isAr
              ? 'أدخل رقم طلبك للاطلاع على آخر تحديثات الشحن.'
              : 'Enter your order number to see the latest shipping updates.'}
          </p>
        </motion.div>

        {/* Search form */}
        <motion.form initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.15 }} onSubmit={handleTrack}
          className="flex gap-3 mb-8">
          <input
            type="text"
            value={orderNum}
            onChange={e => setOrderNum(e.target.value.toUpperCase())}
            placeholder={isAr ? 'AL-2026-XXXXX' : 'AL-2026-XXXXX'}
            className="flex-1 bg-dark-100 border border-white/15 focus:border-gold/50 text-white placeholder-white/25 px-4 py-3.5 text-sm outline-none transition-colors duration-300 font-mono tracking-wider"
          />
          <button type="submit" disabled={loading}
            className="px-8 py-3.5 bg-gold text-dark text-[10px] tracking-[0.3em] uppercase font-bold hover:bg-gold-300 transition-all duration-300 disabled:opacity-60 flex-shrink-0">
            {loading ? (isAr ? '...' : 'Tracking...') : (isAr ? 'تتبع' : 'Track')}
          </button>
        </motion.form>

        {/* Error */}
        <AnimatePresence>
          {error && (
            <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
              className="border border-red-400/30 bg-red-400/5 px-5 py-4 text-red-400 text-sm mb-6">
              {error}
            </motion.div>
          )}
        </AnimatePresence>

        {/* Result */}
        <AnimatePresence>
          {result && (
            <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}
              className="space-y-5">

              {/* Order header card */}
              <div className="border border-gold/20 p-6">
                <div className="flex items-start justify-between gap-4 mb-5">
                  <div>
                    <p className="text-[9px] tracking-[0.4em] uppercase text-gold/50 mb-1">
                      {isAr ? 'رقم الطلب' : 'Order Number'}
                    </p>
                    <p className="font-mono text-xl text-white font-medium">{result.order_number}</p>
                  </div>
                  <span className={`text-sm font-semibold px-4 py-1.5 border ${STATUS_COLORS[result.status] || 'text-gray-400'} border-current/30 bg-current/5`}>
                    {STATUS_LABELS[result.status]?.[isAr ? 'ar' : 'en'] || result.status}
                  </span>
                </div>

                <div className="grid grid-cols-2 gap-4 text-sm mb-5">
                  {[
                    { label: isAr ? 'اسم العميل' : 'Customer',    value: result.customer_name },
                    { label: isAr ? 'الموقع' : 'Location',         value: `${result.city}, ${result.governorate}` },
                    { label: isAr ? 'الإجمالي' : 'Total',          value: `OMR ${parseFloat(result.total_omr).toFixed(3)}` },
                    { label: isAr ? 'تاريخ الطلب' : 'Order Date',  value: result.created_at },
                  ].map(item => (
                    <div key={item.label}>
                      <p className="text-[9px] tracking-[0.3em] uppercase text-white/30 mb-1">{item.label}</p>
                      <p className="text-white/70">{item.value}</p>
                    </div>
                  ))}
                </div>

                {/* Items */}
                <div className="border-t border-white/8 pt-4">
                  <p className="text-[9px] tracking-[0.3em] uppercase text-white/30 mb-3">
                    {isAr ? 'المنتجات' : 'Items'} ({result.items_count})
                  </p>
                  <div className="space-y-2">
                    {result.items.map((item, i) => (
                      <div key={i} className="flex justify-between text-sm">
                        <span className="text-white/60">{item.name} {item.color ? `(${item.color})` : ''}</span>
                        <span className="text-white/40">×{item.qty}</span>
                      </div>
                    ))}
                  </div>
                </div>
              </div>

              {/* Status tracker */}
              {result.status !== 'cancelled' && (
                <div className="border border-white/8 p-6">
                  <p className="text-[9px] tracking-[0.4em] uppercase text-white/30 mb-6">
                    {isAr ? 'مسار الطلب' : 'Order Progress'}
                  </p>
                  <div className="relative">
                    {/* Progress line */}
                    <div className="absolute left-[11px] top-3 bottom-3 w-px bg-white/10" />
                    <div
                      className="absolute left-[11px] top-3 w-px bg-gold transition-all duration-700"
                      style={{ height: `${(result.steps.filter(s => s.done).length / result.steps.length) * 100}%` }}
                    />

                    <div className="space-y-5">
                      {result.steps.map((step, i) => (
                        <div key={i} className="flex items-center gap-4">
                          <div className={`relative z-10 w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all duration-500 ${
                            step.done ? 'border-gold bg-gold' : 'border-white/20 bg-dark'
                          }`}>
                            {step.done && <span className="text-dark text-xs font-bold">✓</span>}
                          </div>
                          <span className={`text-sm transition-colors duration-300 ${
                            step.done ? 'text-white font-medium' : 'text-white/30'
                          }`}>
                            {isAr ? step.label_ar : step.label}
                          </span>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              )}

              {/* WhatsApp support */}
              <div className="flex flex-col sm:flex-row gap-3">
                <a href={`https://wa.me/${waNumber}?text=${waMsg}`} target="_blank" rel="noopener noreferrer"
                  className="flex-1 flex items-center justify-center gap-2.5 py-3.5 border border-[#25D366]/50 text-[#25D366] text-[10px] tracking-[0.3em] uppercase hover:bg-[#25D366] hover:text-white transition-all duration-300">
                  <FaWhatsapp size={15} />
                  {isAr ? 'تواصل معنا عبر واتساب' : 'Chat on WhatsApp'}
                </a>
                <Link to="/collections"
                  className="flex-1 flex items-center justify-center py-3.5 border border-gold/30 text-gold text-[10px] tracking-[0.3em] uppercase hover:bg-gold hover:text-dark transition-all duration-300">
                  {isAr ? 'تسوق المزيد' : 'Continue Shopping'}
                </Link>
              </div>
            </motion.div>
          )}
        </AnimatePresence>

        {/* Help text */}
        {!result && !error && (
          <p className="text-center text-white/25 text-xs mt-8">
            {isAr
              ? 'رقم طلبك موجود في بريدك الإلكتروني أو رسالة واتساب. يبدأ بـ AL-'
              : 'Your order number is in your confirmation email or WhatsApp message. It starts with AL-'}
          </p>
        )}
      </div>
    </div>
  )
}
