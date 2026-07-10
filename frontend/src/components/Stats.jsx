import { useRef } from 'react'
import { motion, useInView } from 'framer-motion'
import { useSettings } from '../hooks/useSettings'

export default function Stats() {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true })
  const s = useSettings()

  const stats = [
    { value: s['stats.1.value'], label: s['stats.1.label'] },
    { value: s['stats.2.value'], label: s['stats.2.label'] },
    { value: s['stats.3.value'], label: s['stats.3.label'] },
    { value: s['stats.4.value'], label: s['stats.4.label'] },
  ].filter(stat => stat.value && stat.label)

  if (stats.length === 0) return null

  return (
    <section ref={ref} className="py-20 border-y border-gold/10">
      <div className="max-w-7xl mx-auto px-6 lg:px-12">
        <div className={`grid grid-cols-2 ${stats.length >= 4 ? 'md:grid-cols-4' : stats.length === 3 ? 'md:grid-cols-3' : 'md:grid-cols-2'}`}>
          {stats.map((stat, i) => (
            <motion.div
              key={i}
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
