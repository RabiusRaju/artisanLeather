import { useRef } from 'react'
import { Link } from 'react-router-dom'
import { motion, useInView } from 'framer-motion'
import { useSettings } from '../hooks/useSettings'

export default function Story() {
  const s = useSettings()
  const image = s['home.story.image']
  const imageAlt = s['home.story.image_alt'] || 'Artisan Leather handcrafted leather workshop story'
  const eyebrow = s['home.story.eyebrow'] || 'Our Story'
  const title1 = s['home.story.title1'] || 'Crafted with Passion,'
  const title2 = s['home.story.title2'] || 'Built to Last'
  const p1 = s['home.story.p1'] || 'At Artisan Leather, every piece begins with a single vision — to create something that transcends the ordinary. Founded in the heart of Oman, we source only the finest full-grain leathers from around the world.'
  const p2 = s['home.story.p2'] || 'Each stitch is placed by hand. Each edge is burnished to perfection. We believe that luxury is not just in the material — it is in the mastery of those who shape it.'
  const cardTitle = s['home.story.years'] || 'Building for the Future'
  const cardSubtitle = s['home.story.years_label'] || 'Years of Craft'
  const buttonLabel = s['home.story.button_label'] || 'Discover Our Heritage'
  const buttonUrl = s['home.story.button_url'] || '/about'
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, margin: '-100px' })

  return (
    <section ref={ref} className="py-24 bg-dark-100">
      <div className="max-w-7xl mx-auto px-6 lg:px-12">
        <div className="grid md:grid-cols-2 gap-16 lg:gap-24 items-center">

          {/* Image / visual side */}
          <motion.div
            initial={{ opacity: 0, x: -40 }}
            animate={isInView ? { opacity: 1, x: 0 } : {}}
            transition={{ duration: 0.9 }}
            className="relative"
          >
            <div className="relative overflow-hidden" style={{ aspectRatio: '4/5', background: image ? undefined : 'linear-gradient(135deg, #3A2210, #240F06, #160E06)' }}>
              {image ? (
                <img
                  src={image}
                  alt={imageAlt}
                  loading="lazy"
                  decoding="async"
                  className="absolute inset-0 h-full w-full object-cover"
                />
              ) : (
                <>
                  <div className="absolute inset-6 border border-dashed border-gold/10" />
                  <div className="absolute inset-0 flex items-center justify-center">
                    <span className="font-serif text-[8rem] font-light text-gold/8 italic leading-none select-none">
                      AL
                    </span>
                  </div>
                </>
              )}
              <div className="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-dark-100 to-transparent" />
            </div>

            {/* Floating stat card */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={isInView ? { opacity: 1, y: 0 } : {}}
              transition={{ duration: 0.7, delay: 0.5 }}
              className="absolute -bottom-6 -right-6 bg-dark border border-gold/20 px-7 py-6 hidden md:block"
            >
              <div className="font-serif text-5xl text-gradient-gold">{cardTitle}</div>
              <div className="text-white/40 text-[10px] tracking-[0.3em] uppercase mt-1">
                {cardSubtitle}
              </div>
            </motion.div>

            {/* Gold border offset */}
            <div className="absolute -top-4 -left-4 w-24 h-24 border border-gold/15 hidden md:block" />
          </motion.div>

          {/* Text side */}
          <motion.div
            initial={{ opacity: 0, x: 40 }}
            animate={isInView ? { opacity: 1, x: 0 } : {}}
            transition={{ duration: 0.9, delay: 0.2 }}
          >
            <p className="text-gold/70 tracking-[0.5em] uppercase text-[10px] mb-6">{eyebrow}</p>
            <h2 className="font-serif text-4xl md:text-5xl text-white font-light leading-tight mb-8">
              {title1}
              <br />
              <span className="italic text-gradient-gold">{title2}</span>
            </h2>
            <div className="w-12 h-px bg-gold mb-8" />
            <p className="text-white/55 leading-relaxed mb-6 text-lg font-light">
              {p1}
            </p>
            <p className="text-white/40 leading-relaxed mb-12 font-light">
              {p2}
            </p>
            <Link
              to={buttonUrl}
              className="inline-flex items-center gap-4 text-gold text-xs tracking-[0.3em] uppercase group"
            >
              <span>{buttonLabel}</span>
              <span className="transition-all duration-300 group-hover:translate-x-2">→</span>
            </Link>
          </motion.div>

        </div>
      </div>
    </section>
  )
}
