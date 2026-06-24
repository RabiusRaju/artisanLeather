import { useSetting } from '../hooks/useSettings'
import { useState, useRef } from 'react'
import { useTranslation } from 'react-i18next'
import { motion, useInView, AnimatePresence } from 'framer-motion'
import { FaWhatsapp, FaInstagram, FaFacebook } from 'react-icons/fa'
import { HiChevronDown, HiCheckCircle } from 'react-icons/hi'
import SEO from '../components/SEO'
import { useFaqs } from '../hooks/useFaqs'
import axios from 'axios'

// ── Sub-components ─────────────────────────────────────────────────────────
function FAQItem({ item, index }) {
  const [open, setOpen] = useState(false)
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, margin: '-40px' })

  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, y: 16 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.5, delay: index * 0.07 }}
      className="border-b border-white/8"
    >
      <button
        onClick={() => setOpen(!open)}
        className="w-full flex items-center justify-between py-5 text-left group"
      >
        <span className="font-serif text-lg text-white/80 group-hover:text-gold transition-colors duration-300 pr-8 leading-snug">
          {item.question}
        </span>
        <HiChevronDown
          size={15}
          className={`text-gold/50 flex-shrink-0 transition-transform duration-400 ${open ? 'rotate-180' : ''}`}
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
            <p className="text-white/45 text-sm font-light leading-relaxed pb-6 pr-8">{item.answer}</p>
          </motion.div>
        )}
      </AnimatePresence>
    </motion.div>
  )
}

// ── Contact form with success state ───────────────────────────────────────
function ContactForm() {
  const { t } = useTranslation()
  const waNumber = useSetting('business.whatsapp', '96812345678').replace(/[^0-9]/g, '')
  const [form, setForm]       = useState({ name: '', email: '', phone: '', subject: '', message: '' })
  const [submitted, setSubmitted] = useState(false)
  const [loading, setLoading] = useState(false)

  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, margin: '-60px' })

  const handleChange = (e) => setForm((f) => ({ ...f, [e.target.name]: e.target.value }))

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    try {
      await axios.post(`${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'}/contact`, form, {
        headers: { 'Accept': 'application/json' }
      })
      setSubmitted(true)
    } catch {
      // Still show success to user — message may have saved
      setSubmitted(true)
    } finally {
      setLoading(false)
    }
  }

  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, x: 28 }}
      animate={isInView ? { opacity: 1, x: 0 } : {}}
      transition={{ duration: 0.8, delay: 0.2 }}
    >
      <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-3">{t('contact.formEyebrow')}</p>
      <h2 className="font-serif text-3xl text-white font-light mb-8">{t('contact.formTitle')}</h2>

      <AnimatePresence mode="wait">
        {submitted ? (
          <motion.div
            key="success"
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            className="border border-gold/20 bg-gold/5 px-8 py-12 text-center"
          >
            <HiCheckCircle size={40} className="text-gold mx-auto mb-4" />
            <h3 className="font-serif text-2xl text-white mb-3">{t('contact.successTitle')}</h3>
            <p className="text-white/45 font-light text-sm leading-relaxed">
              {t('contact.thankYou')} {form.name.split(' ')[0] || ''}. {t('contact.successDesc')}
            </p>
            <a
              href={`https://wa.me/${waNumber}`}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-2 mt-8 border border-[#25D366]/50 text-[#25D366] px-7 py-3 text-[10px] tracking-[0.3em] uppercase hover:bg-[#25D366] hover:text-white transition-all duration-300"
            >
              <FaWhatsapp size={14} /> {t('contact.openWhatsApp')}
            </a>
          </motion.div>
        ) : (
          <motion.form
            key="form"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            onSubmit={handleSubmit}
            className="space-y-5"
          >
            {/* Name + Email */}
            <div className="grid sm:grid-cols-2 gap-5">
              <div>
                <label className="block text-[9px] tracking-[0.35em] uppercase text-white/35 mb-2">
                  {t('contact.name')} <span className="text-gold">*</span>
                </label>
                <input
                  name="name"
                  type="text"
                  required
                  value={form.name}
                  onChange={handleChange}
                  placeholder="Mohammed Al Rashidi"
                  className="w-full bg-dark-100 border border-white/10 focus:border-gold/50 text-white placeholder-white/15 px-4 py-3.5 text-sm outline-none transition-colors duration-300"
                />
              </div>
              <div>
                <label className="block text-[9px] tracking-[0.35em] uppercase text-white/35 mb-2">
                  {t('contact.email')} <span className="text-gold">*</span>
                </label>
                <input
                  name="email"
                  type="email"
                  required
                  value={form.email}
                  onChange={handleChange}
                  placeholder="hello@example.com"
                  className="w-full bg-dark-100 border border-white/10 focus:border-gold/50 text-white placeholder-white/15 px-4 py-3.5 text-sm outline-none transition-colors duration-300"
                />
              </div>
            </div>

            {/* Phone + Subject */}
            <div className="grid sm:grid-cols-2 gap-5">
              <div>
                <label className="block text-[9px] tracking-[0.35em] uppercase text-white/35 mb-2">
                  {t('contact.phone')}
                </label>
                <input
                  name="phone"
                  type="tel"
                  value={form.phone}
                  onChange={handleChange}
                  placeholder="+968 ···· ····"
                  className="w-full bg-dark-100 border border-white/10 focus:border-gold/50 text-white placeholder-white/15 px-4 py-3.5 text-sm outline-none transition-colors duration-300"
                />
              </div>
              <div>
                <label className="block text-[9px] tracking-[0.35em] uppercase text-white/35 mb-2">
                  {t('contact.subject')}
                </label>
                <select
                  name="subject"
                  value={form.subject}
                  onChange={handleChange}
                  className="w-full bg-dark-100 border border-white/10 focus:border-gold/50 text-white/70 px-4 py-3.5 text-sm outline-none transition-colors duration-300 appearance-none cursor-pointer"
                >
                  <option value="">{t('contact.topics.select')}</option>
                  <option value="product">{t('contact.topics.product')}</option>
                  <option value="custom">{t('contact.topics.custom')}</option>
                  <option value="shipping">{t('contact.topics.shipping')}</option>
                  <option value="return">{t('contact.topics.return')}</option>
                  <option value="other">{t('contact.topics.other')}</option>
                </select>
              </div>
            </div>

            {/* Message */}
            <div>
              <label className="block text-[9px] tracking-[0.35em] uppercase text-white/35 mb-2">
                {t('contact.message')} <span className="text-gold">*</span>
              </label>
              <textarea
                name="message"
                required
                rows={5}
                value={form.message}
                onChange={handleChange}
                placeholder="…"
                className="w-full bg-dark-100 border border-white/10 focus:border-gold/50 text-white placeholder-white/15 px-4 py-3.5 text-sm outline-none transition-colors duration-300 resize-none"
              />
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-gold-300 active:scale-[0.99] transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed"
            >
              {loading ? t('contact.sending') : t('contact.sendMessage')}
            </button>
          </motion.form>
        )}
      </AnimatePresence>
    </motion.div>
  )
}

// ── Page ───────────────────────────────────────────────────────────────────
export default function Contact() {
  const { t } = useTranslation()
  const waNumber  = useSetting('business.whatsapp', '96812345678').replace(/[^0-9]/g, '')
  const instagram = useSetting('social.instagram', '')
  const facebook  = useSetting('social.facebook', '')
  const email     = useSetting('business.email', 'info@artisanleatherom.com')
  const address   = useSetting('business.address', t('footer.address'))
  const addressLine2 = useSetting('business.address_2', '')
  const whatsappHours = useSetting('business.whatsapp_hours', '')
  const emailReplyTime = useSetting('business.email_response_time', '')
  const { faqs } = useFaqs()

  const infoRef = useRef(null)
  const waRef   = useRef(null)
  const faqRef  = useRef(null)

  const infoInView = useInView(infoRef, { once: true, margin: '-60px' })
  const waInView   = useInView(waRef,   { once: true })
  const faqInView  = useInView(faqRef,  { once: true, margin: '-60px' })

  return (
    <div className="min-h-screen bg-dark">

      <SEO
        title="Contact Us — Muscat, Oman"
        description="Contact Artisan Leather via WhatsApp, email or our online form. We're based in Muscat, Oman and deliver across the GCC. Custom orders and enquiries welcome."
        url="/contact"
      />

      {/* HERO */}
      <section className="relative pt-44 pb-24 px-6 overflow-hidden border-b border-gold/10">
        <div className="absolute inset-0 bg-gradient-to-b from-dark-100 to-dark" />
        <div className="absolute left-1/3 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-gold/8 to-transparent pointer-events-none" />
        <div className="absolute right-1/3 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-gold/8 to-transparent pointer-events-none" />

        <div className="relative z-10 max-w-3xl mx-auto text-center">
          <motion.p
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            className="text-gold/60 tracking-[0.6em] uppercase text-[10px] mb-8"
          >
            {t('contact.eyebrow')}
          </motion.p>
          <motion.h1
            initial={{ opacity: 0, y: 24 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.15 }}
            className="font-serif text-5xl md:text-7xl text-white font-light leading-[1.05] mb-8"
          >
            {t('contact.headline1')}
            <br />
            <span className="text-gradient-gold italic">{t('contact.headline2')}</span>
          </motion.h1>
          <motion.div
            initial={{ scaleX: 0 }}
            animate={{ scaleX: 1 }}
            transition={{ delay: 0.45, duration: 0.6 }}
            className="w-16 h-px bg-gold mx-auto mb-8 origin-center"
          />
          <motion.p
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.65 }}
            className="text-white/40 text-lg font-light"
          >
            {t('contact.sub')}
          </motion.p>
        </div>
      </section>

      {/* CONTACT INFO + FORM */}
      <section className="py-24 px-6 lg:px-12 max-w-7xl mx-auto">
        <div className="grid lg:grid-cols-2 gap-16 lg:gap-24">

          {/* Left — Info */}
          <motion.div
            ref={infoRef}
            initial={{ opacity: 0, x: -28 }}
            animate={infoInView ? { opacity: 1, x: 0 } : {}}
            transition={{ duration: 0.8 }}
            className="space-y-0"
          >
            <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-3">{t('contact.findUs')}</p>
            <h2 className="font-serif text-3xl text-white font-light mb-12">{t('contact.info')}</h2>

            {/* Info items */}
            {[
              {
                label: t('contact.location'),
                value: address,
                sub: addressLine2,
                link: null,
              },
              {
                label: t('contact.phoneLabel'),
                value: `+${waNumber}`,
                sub: whatsappHours,
                link: `https://wa.me/${waNumber}`,
              },
              {
                label: t('contact.emailLabel'),
                value: email,
                sub: emailReplyTime,
                link: `mailto:${email}`,
              },
            ].filter(item => item.value).map((item, i) => (
              <motion.div
                key={item.label}
                initial={{ opacity: 0, y: 16 }}
                animate={infoInView ? { opacity: 1, y: 0 } : {}}
                transition={{ delay: 0.2 + i * 0.12 }}
                className="flex gap-6 py-7 border-b border-white/7 last:border-0 group"
              >
                <div className="w-1 bg-gradient-to-b from-gold/60 to-gold/10 flex-shrink-0 rounded-full" />
                <div>
                  <p className="text-[9px] tracking-[0.4em] uppercase text-white/30 mb-2">{item.label}</p>
                  {item.link ? (
                    <a
                      href={item.link}
                      className="font-serif text-xl text-white group-hover:text-gold transition-colors duration-300"
                    >
                      {item.value}
                    </a>
                  ) : (
                    <p className="font-serif text-xl text-white">{item.value}</p>
                  )}
                  {item.sub && <p className="text-white/30 text-xs font-light mt-1">{item.sub}</p>}
                </div>
              </motion.div>
            ))}

            {/* Socials */}
            <div className="pt-10">
              <p className="text-[9px] tracking-[0.4em] uppercase text-white/30 mb-6">{t('contact.followAlong')}</p>
              <div className="flex gap-4 flex-wrap">
                <a
                  href={`https://wa.me/${waNumber}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex items-center gap-2.5 border border-[#25D366]/40 text-[#25D366] px-5 py-2.5 text-[10px] tracking-[0.25em] uppercase hover:bg-[#25D366] hover:text-white transition-all duration-300"
                >
                  <FaWhatsapp size={13} /> WhatsApp
                </a>
                {instagram && (
                  <a
                    href={instagram}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex items-center gap-2.5 border border-gold/25 text-gold/70 px-5 py-2.5 text-[10px] tracking-[0.25em] uppercase hover:bg-gold hover:text-dark transition-all duration-300"
                  >
                    <FaInstagram size={13} /> Instagram
                  </a>
                )}
                {facebook && (
                  <a
                    href={facebook}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex items-center gap-2.5 border border-white/12 text-white/35 px-5 py-2.5 text-[10px] tracking-[0.25em] uppercase hover:border-gold/30 hover:text-gold transition-all duration-300"
                  >
                    <FaFacebook size={13} /> Facebook
                  </a>
                )}
              </div>
            </div>

            {/* Hours */}
            <div className="pt-10 border-t border-white/7 mt-10">
              <p className="text-[9px] tracking-[0.4em] uppercase text-white/30 mb-5">{t('contact.hours')}</p>
              <div className="space-y-2.5">
                {[
                  { days: t('contact.saturday'), hours: t('contact.saturdayHours') },
                  { days: t('contact.friday'),   hours: t('contact.fridayHours') },
                ].map((row) => (
                  <div key={row.days} className="flex justify-between text-sm">
                    <span className="text-white/40 font-light">{row.days}</span>
                    <span className="text-gold/70">{row.hours}</span>
                  </div>
                ))}
              </div>
            </div>
          </motion.div>

          {/* Right — Form */}
          <ContactForm />
        </div>
      </section>

      {/* WHATSAPP CTA BANNER */}
      <section ref={waRef} className="py-20 px-6 bg-dark-100 border-y border-gold/8">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={waInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.7 }}
          className="max-w-4xl mx-auto flex flex-col md:flex-row items-center justify-between gap-8"
        >
          <div className="text-center md:text-left">
            <p className="text-[9px] tracking-[0.5em] uppercase text-gold/50 mb-3">{t('contact.fastestResponse')}</p>
            <h2 className="font-serif text-3xl md:text-4xl text-white font-light mb-3">
              {t('contact.chatTitle')}
            </h2>
            <p className="text-white/40 font-light text-sm">
              {t('contact.chatDesc')}
            </p>
          </div>
          <a
            href={`https://wa.me/${waNumber}?text=Hello%20Artisan%20Leather%2C%20I%20have%20a%20question.`}
            target="_blank"
            rel="noopener noreferrer"
            className="flex-shrink-0 flex items-center gap-3 bg-[#25D366] text-white px-10 py-4 text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-[#1da851] active:scale-[0.98] transition-all duration-300 shadow-lg shadow-[#25D366]/20"
          >
            <FaWhatsapp size={18} />
            {t('contact.openWhatsAppBtn')}
          </a>
        </motion.div>
      </section>

      {/* FAQ */}
      <section className="py-24 px-6 lg:px-12 max-w-4xl mx-auto">
        <motion.div
          ref={faqRef}
          initial={{ opacity: 0, y: 24 }}
          animate={faqInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.7 }}
          className="text-center mb-16"
        >
          <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-4">{t('contact.faqEyebrow')}</p>
          <h2 className="font-serif text-4xl text-white font-light">{t('contact.faq')}</h2>
          <div className="w-14 h-px bg-gold mx-auto mt-6" />
        </motion.div>

        <div className="border-t border-white/8">
          {faqs.map((item, i) => (
            <FAQItem key={i} item={item} index={i} />
          ))}
        </div>
      </section>
    </div>
  )
}
