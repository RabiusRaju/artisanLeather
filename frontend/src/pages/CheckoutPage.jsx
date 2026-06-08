import SEO from '../components/SEO'
import { useSetting } from '../hooks/useSettings'
import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { HiArrowLeft, HiLockClosed } from 'react-icons/hi'
import { FaWhatsapp, FaMoneyBillWave, FaUniversity } from 'react-icons/fa'
import { useCart }     from '../context/CartContext'
import { useCurrency } from '../context/CurrencyContext'
import { placeOrder }  from '../services/api'

const omanGovernorates = [
  'Muscat', 'Dhofar', 'Musandam', 'Al Buraimi', 'Al Batinah North',
  'Al Batinah South', 'Al Dakhliyah', 'Al Dhahirah', 'Al Sharqiyah North',
  'Al Sharqiyah South', 'Al Wusta',
]

const paymentMethods = [
  {
    id: 'cod',
    label: 'Cash on Delivery',
    desc: 'Pay when your order arrives. Available across Oman.',
    icon: FaMoneyBillWave,
    color: 'text-green-400',
  },
  {
    id: 'bank',
    label: 'Bank Transfer',
    desc: 'Transfer to our Bank Muscat account. Order ships after confirmation.',
    icon: FaUniversity,
    color: 'text-blue-400',
  },
  {
    id: 'whatsapp',
    label: 'Order via WhatsApp',
    desc: 'Complete your order on WhatsApp. Our team will confirm within minutes.',
    icon: FaWhatsapp,
    color: 'text-[#25D366]',
  },
]

function Field({ label, required, children }) {
  return (
    <div>
      <label className="block text-[9px] tracking-[0.35em] uppercase text-white/35 mb-2">
        {label} {required && <span className="text-gold">*</span>}
      </label>
      {children}
    </div>
  )
}

const inputCls = 'w-full bg-dark border border-white/10 focus:border-gold/50 text-white placeholder-white/15 px-4 py-3.5 text-sm outline-none transition-colors duration-300'
const selectCls = inputCls + ' appearance-none cursor-pointer'

export default function CheckoutPage() {
  const { items, subtotal, clearCart } = useCart()
  const { format, currency } = useCurrency()
  const navigate   = useNavigate()
  const waNumber = useSetting('business.whatsapp', '96812345678').replace(/[^0-9]/g, '')
  const total      = subtotal

  const [payment, setPayment] = useState('cod')
  const [loading, setLoading] = useState(false)
  const [form, setForm] = useState({
    firstName: '', lastName: '', email: '', phone: '',
    governorate: '', city: '', address: '', notes: '',
  })

  const set = (field) => (e) => setForm((f) => ({ ...f, [field]: e.target.value }))

  // If cart is empty, redirect back
  if (items.length === 0) {
    return (
      <div className="min-h-screen bg-dark flex items-center justify-center px-6">
        <div className="text-center">
          <p className="text-white/40 mb-6 font-light">Your cart is empty.</p>
          <Link to="/collections" className="text-gold text-sm tracking-widest uppercase">
            Go Shopping →
          </Link>
        </div>
      </div>
    )
  }

  const waLines = items.map(
    (i) => `• ${i.name} (${i.colorName}) × ${i.quantity} — OMR ${(i.price * i.quantity).toFixed(3)}`
  ).join('\n')
  const waMessage = encodeURIComponent(
    `Hello Artisan Leather, I'd like to order:\n\n${waLines}\n\nTotal: OMR ${total.toFixed(3)}\n\nDelivery to: ${form.city || '...'}, ${form.governorate || 'Oman'}\nName: ${form.firstName} ${form.lastName}\nPhone: ${form.phone}`
  )

  const handlePlaceOrder = async (e) => {
    e.preventDefault()

    if (payment === 'whatsapp') {
      window.open(`https://wa.me/${waNumber}?text=${waMessage}`, '_blank')
      return
    }

    setLoading(true)
    try {
      const payload = {
        first_name:     form.firstName,
        last_name:      form.lastName,
        email:          form.email,
        phone:          form.phone,
        governorate:    form.governorate,
        city:           form.city,
        address:        form.address,
        notes:          form.notes || null,
        payment_method: payment,
        currency_code:  currency.code,
        currency_rate:  parseFloat(currency.rate),
        items: items.map(item => ({
          product_id:   item.id,
          product_name: item.name,
          color_name:   item.colorName,
          color_hex:    item.colorHex,
          quantity:     item.quantity,
          unit_price:   parseFloat(item.price),
        })),
      }

      const res = await placeOrder(payload)
      const orderNum = res.data.order_number
      clearCart()
      navigate('/order-confirmation', {
        state: { orderNum, form, items: [...items], total, payment }
      })
    } catch (err) {
      console.error('Order failed:', err)
      alert('Something went wrong. Please try again or order via WhatsApp.')
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-dark">

      <SEO title="Secure Checkout" description="Complete your Artisan Leather order securely." url="/checkout" noIndex />

      {/* Header */}
      <section className="pt-36 pb-10 px-6 lg:px-12 border-b border-gold/10 bg-dark-100">
        <div className="max-w-7xl mx-auto">
          {/* Progress */}
          <div className="flex items-center gap-3 mb-6">
            {['Cart', 'Checkout', 'Confirmation'].map((step, i) => (
              <div key={step} className="flex items-center gap-3">
                <div className={`flex items-center gap-2 text-[9px] tracking-[0.3em] uppercase ${
                  i === 1 ? 'text-gold' : i < 1 ? 'text-white/40' : 'text-white/20'
                }`}>
                  <span className={`w-5 h-5 rounded-full border flex items-center justify-center text-[8px] ${
                    i === 1 ? 'border-gold bg-gold text-dark font-bold' :
                    i < 1 ? 'border-white/30 text-white/40' : 'border-white/15 text-white/20'
                  }`}>
                    {i + 1}
                  </span>
                  {step}
                </div>
                {i < 2 && <div className={`w-8 h-px ${i < 1 ? 'bg-gold/30' : 'bg-white/10'}`} />}
              </div>
            ))}
          </div>

          <motion.div initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }}>
            <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-3">Almost There</p>
            <h1 className="font-serif text-4xl md:text-5xl text-white font-light">Checkout</h1>
          </motion.div>
        </div>
      </section>

      <form onSubmit={handlePlaceOrder}>
        <div className="max-w-7xl mx-auto px-6 lg:px-12 py-12">
          <div className="grid lg:grid-cols-3 gap-12 lg:gap-16 items-start">

            {/* LEFT — Delivery + Payment */}
            <div className="lg:col-span-2 space-y-12">

              {/* Delivery details */}
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.1 }}
              >
                <h2 className="font-serif text-2xl text-white mb-8 flex items-center gap-3">
                  <span className="w-7 h-7 rounded-full border border-gold/40 flex items-center justify-center text-gold/60 text-xs">1</span>
                  Delivery Details
                </h2>

                <div className="grid sm:grid-cols-2 gap-5">
                  <Field label="First Name" required>
                    <input required className={inputCls} value={form.firstName} onChange={set('firstName')} placeholder="Mohammed" />
                  </Field>
                  <Field label="Last Name" required>
                    <input required className={inputCls} value={form.lastName} onChange={set('lastName')} placeholder="Al Rashidi" />
                  </Field>
                  <Field label="Email Address" required>
                    <input required type="email" className={inputCls} value={form.email} onChange={set('email')} placeholder="hello@example.com" />
                  </Field>
                  <Field label="Phone Number" required>
                    <div className="flex">
                      <span className="bg-dark-100 border border-r-0 border-white/10 px-3.5 flex items-center text-white/35 text-sm flex-shrink-0">
                        +968
                      </span>
                      <input
                        required
                        type="tel"
                        className={inputCls + ' rounded-l-none border-l-0'}
                        value={form.phone}
                        onChange={set('phone')}
                        placeholder="9123 4567"
                      />
                    </div>
                  </Field>
                  <Field label="Governorate" required>
                    <select required className={selectCls} value={form.governorate} onChange={set('governorate')}>
                      <option value="">Select governorate…</option>
                      {omanGovernorates.map((g) => <option key={g} value={g}>{g}</option>)}
                    </select>
                  </Field>
                  <Field label="City / Wilayat" required>
                    <input required className={inputCls} value={form.city} onChange={set('city')} placeholder="e.g. Al Seeb" />
                  </Field>
                  <Field label="Street Address" required>
                    <input required className={inputCls + ' sm:col-span-2'} value={form.address} onChange={set('address')} placeholder="Building, Street, Area…" />
                  </Field>
                  <div className="sm:col-span-2">
                    <Field label="Order Notes (optional)">
                      <textarea
                        rows={3}
                        className={inputCls + ' resize-none'}
                        value={form.notes}
                        onChange={set('notes')}
                        placeholder="Special instructions, gift message, preferred delivery time…"
                      />
                    </Field>
                  </div>
                </div>
              </motion.div>

              {/* Payment method */}
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.2 }}
              >
                <h2 className="font-serif text-2xl text-white mb-8 flex items-center gap-3">
                  <span className="w-7 h-7 rounded-full border border-gold/40 flex items-center justify-center text-gold/60 text-xs">2</span>
                  Payment Method
                </h2>

                <div className="space-y-3">
                  {paymentMethods.map((method) => {
                    const Icon = method.icon
                    const active = payment === method.id
                    return (
                      <label
                        key={method.id}
                        className={`flex items-start gap-5 p-5 border cursor-pointer transition-all duration-300 ${
                          active ? 'border-gold/40 bg-gold/5' : 'border-white/8 hover:border-white/20'
                        }`}
                      >
                        <input
                          type="radio"
                          name="payment"
                          value={method.id}
                          checked={active}
                          onChange={() => setPayment(method.id)}
                          className="sr-only"
                        />
                        {/* Custom radio */}
                        <div className={`w-4 h-4 rounded-full border flex-shrink-0 mt-0.5 flex items-center justify-center transition-colors duration-300 ${
                          active ? 'border-gold' : 'border-white/25'
                        }`}>
                          {active && <div className="w-2 h-2 rounded-full bg-gold" />}
                        </div>

                        <Icon size={18} className={`${method.color} flex-shrink-0 mt-0.5`} />

                        <div className="flex-1">
                          <p className={`text-sm font-medium mb-0.5 transition-colors duration-300 ${active ? 'text-white' : 'text-white/60'}`}>
                            {method.label}
                          </p>
                          <p className="text-white/30 text-xs font-light leading-relaxed">{method.desc}</p>
                        </div>
                      </label>
                    )
                  })}
                </div>

                {payment === 'bank' && (
                  <motion.div
                    initial={{ opacity: 0, height: 0 }}
                    animate={{ opacity: 1, height: 'auto' }}
                    className="mt-4 border border-blue-400/20 bg-blue-400/5 p-5 overflow-hidden"
                  >
                    <p className="text-[9px] tracking-[0.35em] uppercase text-blue-400/60 mb-3">Bank Transfer Details</p>
                    <div className="space-y-1.5 text-sm">
                      {[
                        ['Bank', 'Bank Muscat'],
                        ['Account Name', 'Artisan Leather LLC'],
                        ['Account No.', '0123-4567890-001'],
                        ['IBAN', 'OM12 1234 0000 0123 4567 890'],
                        ['Reference', `Your phone number`],
                      ].map(([k, v]) => (
                        <div key={k} className="flex gap-4">
                          <span className="text-white/30 w-28 flex-shrink-0">{k}</span>
                          <span className="text-white/60 font-light">{v}</span>
                        </div>
                      ))}
                    </div>
                  </motion.div>
                )}
              </motion.div>

              {/* Back link */}
              <Link
                to="/cart"
                className="inline-flex items-center gap-2 text-white/30 hover:text-gold text-[10px] tracking-[0.25em] uppercase transition-colors duration-300 group"
              >
                <HiArrowLeft size={12} className="group-hover:-translate-x-1 transition-transform duration-300" />
                Back to Cart
              </Link>
            </div>

            {/* RIGHT — Order Summary */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.3 }}
              className="lg:sticky lg:top-28"
            >
              <div className="border border-gold/15 p-7">
                <h2 className="font-serif text-xl text-white mb-7">Your Order</h2>

                {/* Items */}
                <div className="space-y-4 mb-6">
                  {items.map((item) => (
                    <div key={`${item.id}-${item.colorName}`} className="flex gap-3">
                      <div className="w-12 h-12 flex-shrink-0 relative overflow-hidden" style={{ background: item.gradient }}>
                        {item.image && (
                          <img src={item.image} alt={item.name} className="absolute inset-0 w-full h-full object-cover" />
                        )}
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="text-white/70 text-sm leading-snug truncate">{item.name}</p>
                        <p className="text-white/30 text-xs mt-0.5">{item.colorName} · Qty {item.quantity}</p>
                      </div>
                      <p className="text-white/50 text-sm flex-shrink-0">
                        {format((item.price * item.quantity))}
                      </p>
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
                    <span className="text-white text-sm">Total</span>
                    <span className="font-serif text-2xl text-gold">{format(total)}</span>
                  </div>
                </div>

                {/* Place Order */}
                <button
                  type="submit"
                  disabled={loading}
                  className="w-full py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-gold-300 active:scale-[0.99] transition-all duration-300 disabled:opacity-60 flex items-center justify-center gap-2"
                >
                  {loading ? (
                    <span>Placing Order…</span>
                  ) : (
                    <>
                      <HiLockClosed size={12} />
                      {payment === 'whatsapp' ? 'Continue on WhatsApp' : 'Place Order'}
                    </>
                  )}
                </button>

                <div className="mt-5 space-y-2">
                  {['Secure checkout', 'Free gift wrapping', '14-day returns'].map((t) => (
                    <div key={t} className="flex items-center gap-2 text-white/25 text-[10px]">
                      <span className="text-gold/40">✓</span> {t}
                    </div>
                  ))}
                </div>
              </div>
            </motion.div>

          </div>
        </div>
      </form>
    </div>
  )
}
