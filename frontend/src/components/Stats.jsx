import { useRef } from 'react'
import { motion, useInView } from 'framer-motion'

const stats = [
  { value: '100%', label: 'Handcrafted' },
  { value: '15+', label: 'Years of Excellence' },
  { value: '50+', label: 'Unique Designs' },
  { value: 'GCC', label: 'Wide Delivery' },
]

export default function Stats() {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true })

  return (
    <section ref={ref} className="py-20 border-y border-gold/10">
      <div className="max-w-7xl mx-auto px-6 lg:px-12">
        <div className="grid grid-cols-2 md:grid-cols-4">
          {stats.map((stat, i) => (
            <motion.div
              key={stat.label}
              initial={{ opacity: 0, y: 24 }}
              animate={isInView ? { opacity: 1, y: 0 } : {}}
              transition={{ duration: 0.6, delay: i * 0.1 }}
              className="text-center py-8 px-4 border-r border-gold/10 last:border-r-0 even:border-r-0 md:even:border-r md:last:border-r-0"
            >
              <div className="font-serif text-5xl md:text-6xl text-gradient-gold mb-3">
                {stat.value}
              </div>
              <div className="text-white/35 text-[10px] tracking-[0.35em] uppercase">
                {stat.label}
              </div>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  )
}
