import { useRef, useState } from 'react'
import { motion, useInView, AnimatePresence } from 'framer-motion'

const testimonials = [
  {
    id: 1,
    quote:
      'The most exquisite wallet I have ever owned. The leather is buttery smooth and the craftsmanship is simply unmatched. Worth every Baisa.',
    author: 'Mohammed Al Rashidi',
    location: 'Muscat, Oman',
  },
  {
    id: 2,
    quote:
      'I gifted an Artisan Leather bag to my wife for our anniversary. She was speechless. The quality speaks before the price.',
    author: 'Khalid Al Harthi',
    location: 'Dubai, UAE',
  },
  {
    id: 3,
    quote:
      'These are not just leather goods — they are heirlooms in the making. I have had my belt for three years and it only looks better with age.',
    author: 'Salim Al Balushi',
    location: 'Salalah, Oman',
  },
]

export default function Testimonials() {
  const [current, setCurrent] = useState(0)
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true })

  const prev = () => setCurrent((c) => (c === 0 ? testimonials.length - 1 : c - 1))
  const next = () => setCurrent((c) => (c === testimonials.length - 1 ? 0 : c + 1))

  return (
    <section ref={ref} className="py-24 bg-dark-100">
      <div className="max-w-4xl mx-auto px-6 lg:px-12 text-center">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={isInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.7 }}
          className="mb-16"
        >
          <p className="text-gold/70 tracking-[0.5em] uppercase text-[10px] mb-4">Testimonials</p>
          <h2 className="font-serif text-4xl text-white font-light">What Our Clients Say</h2>
          <div className="w-16 h-px bg-gold mx-auto mt-6" />
        </motion.div>

        {/* Quote */}
        <div className="min-h-[260px] flex flex-col justify-center">
          <AnimatePresence mode="wait">
            <motion.div
              key={current}
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -16 }}
              transition={{ duration: 0.45 }}
            >
              <div className="font-serif text-6xl text-gold/30 leading-none mb-4 select-none">"</div>
              <p className="font-serif text-xl md:text-2xl text-white/75 font-light italic leading-relaxed mb-10 max-w-2xl mx-auto">
                {testimonials[current].quote}
              </p>
              <div className="w-8 h-px bg-gold/50 mx-auto mb-6" />
              <p className="text-white font-medium tracking-wide text-sm">{testimonials[current].author}</p>
              <p className="text-white/35 text-xs tracking-wide mt-1.5">{testimonials[current].location}</p>
            </motion.div>
          </AnimatePresence>
        </div>

        {/* Controls */}
        <div className="flex items-center justify-center gap-8 mt-10">
          <button
            onClick={prev}
            className="text-white/30 hover:text-gold transition-colors duration-300 text-xl"
            aria-label="Previous"
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
            aria-label="Next"
          >
            →
          </button>
        </div>
      </div>
    </section>
  )
}
