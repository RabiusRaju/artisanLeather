import { useRef, useState, useEffect } from 'react'
import { motion, useInView, AnimatePresence } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import { fetchTestimonials } from '../services/api'

const FALLBACK = [
  {
    id: 1,
    quote: 'The most exquisite wallet I have ever owned. The leather is buttery smooth and the craftsmanship is simply unmatched. Worth every Baisa.',
    quote_ar: 'إنها أروع محفظة امتلكتها على الإطلاق. الجلد ناعم كالحرير والحرفية لا مثيل لها. تستحق كل بيسة.',
    author: 'Mohammed Al Rashidi',
    location: 'Muscat, Oman',
    rating: 5,
  },
  {
    id: 2,
    quote: 'I gifted an Artisan Leather bag to my wife for our anniversary. She was speechless. The quality speaks before the price.',
    quote_ar: 'أهديت زوجتي حقيبة من آرتيزان ليذر بمناسبة ذكرى زواجنا، فأُصيبت بالذهول. الجودة تتحدث عن نفسها قبل السعر.',
    author: 'Khalid Al Harthi',
    location: 'Dubai, UAE',
    rating: 5,
  },
  {
    id: 3,
    quote: 'These are not just leather goods — they are heirlooms in the making. I have had my belt for three years and it only looks better with age.',
    quote_ar: 'هذه ليست مجرد منتجات جلدية، بل قطع أثرية في طور التكوين. أمتلك حزامي منذ ثلاث سنوات وهو يزداد جمالاً مع الوقت.',
    author: 'Salim Al Balushi',
    location: 'Salalah, Oman',
    rating: 5,
  },
]

function Stars({ rating }) {
  return (
    <div className="flex justify-center gap-0.5 mb-6" aria-label={`${rating} out of 5 stars`}>
      {Array.from({ length: 5 }, (_, i) => (
        <svg
          key={i}
          className={`w-4 h-4 ${i < rating ? 'text-gold' : 'text-white/10'}`}
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
      ))}
    </div>
  )
}

export default function Testimonials() {
  const [testimonials, setTestimonials] = useState(FALLBACK)
  const [current, setCurrent]           = useState(0)
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true })
  const { t: translate, i18n } = useTranslation()
  const isAr = i18n.language?.startsWith('ar')

  useEffect(() => {
    fetchTestimonials()
      .then(res => {
        const data = res.data?.data
        if (Array.isArray(data) && data.length > 0) {
          setTestimonials(data)
          setCurrent(0)
        }
      })
      .catch(() => {}) // keep fallback on error
  }, [])

  const prev = () => setCurrent(c => (c === 0 ? testimonials.length - 1 : c - 1))
  const next = () => setCurrent(c => (c === testimonials.length - 1 ? 0 : c + 1))

  const t = testimonials[current] ?? testimonials[0]
  const quote = (isAr && t.quote_ar) ? t.quote_ar : t.quote
  const author = (isAr && t.author_ar) ? t.author_ar : t.author
  const location = (isAr && t.location_ar) ? t.location_ar : t.location

  return (
    <section ref={ref} className="py-24 bg-dark-100">
      <div className="max-w-4xl mx-auto px-6 lg:px-12 text-center">

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={isInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.7 }}
          className="mb-16"
        >
          <p className="text-gold/70 tracking-[0.5em] uppercase text-[10px] mb-4">{translate('home.testimonials.eyebrow')}</p>
          <h2 className="font-serif text-4xl text-white font-light">{translate('home.testimonials.title')}</h2>
          <div className="w-16 h-px bg-gold mx-auto mt-6" />
        </motion.div>

        {/* Quote card */}
        <div className="min-h-[280px] flex flex-col justify-center">
          <AnimatePresence mode="wait">
            <motion.div
              key={t.id ?? current}
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -16 }}
              transition={{ duration: 0.45 }}
            >
              <div className="font-serif text-6xl text-gold/30 leading-none mb-4 select-none">"</div>

              <p className="font-serif text-xl md:text-2xl text-white/75 font-light italic leading-relaxed mb-8 max-w-2xl mx-auto">
                {quote}
              </p>

              <div className="w-8 h-px bg-gold/50 mx-auto mb-5" />

              <Stars rating={t.rating ?? 5} />

              <p className="text-white font-medium tracking-wide text-sm">{author}</p>
              {location && (
                <p className="text-white/35 text-xs tracking-wide mt-1.5">{location}</p>
              )}
            </motion.div>
          </AnimatePresence>
        </div>

        {/* Controls — only shown when there are multiple testimonials */}
        {testimonials.length > 1 && (
          <div className="flex items-center justify-center gap-8 mt-10">
            <button
              onClick={prev}
              className="text-white/30 hover:text-gold transition-colors duration-300 text-xl"
              aria-label="Previous testimonial"
            >
              ←
            </button>

            <div className="flex gap-3">
              {testimonials.map((_, i) => (
                <button
                  key={i}
                  onClick={() => setCurrent(i)}
                  className={`h-px transition-all duration-400 ${
                    i === current ? 'bg-gold w-10' : 'bg-white/20 w-5'
                  }`}
                  aria-label={`Go to testimonial ${i + 1}`}
                />
              ))}
            </div>

            <button
              onClick={next}
              className="text-white/30 hover:text-gold transition-colors duration-300 text-xl"
              aria-label="Next testimonial"
            >
              →
            </button>
          </div>
        )}

      </div>
    </section>
  )
}
