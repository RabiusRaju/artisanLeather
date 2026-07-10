import { useRef } from 'react'
import { Link } from 'react-router-dom'
import { motion, useInView } from 'framer-motion'
import { useSettings } from '../hooks/useSettings'

export default function Story() {
  const s = useSettings()
  const image = s['home.story.image']
  const imageAlt = s['home.story.image_alt'] || ''
  const eyebrow = s['home.story.eyebrow'] || ''
  const title1 = s['home.story.title1'] || ''
  const title2 = s['home.story.title2'] || ''
  const p1 = s['home.story.p1'] || ''
  const p2 = s['home.story.p2'] || ''
  const cardTitle = s['home.story.years'] || ''
  const cardSubtitle = s['home.story.years_label'] || ''
  const buttonLabel = s['home.story.button_label'] || ''
  const buttonUrl = s['home.story.button_url'] || ''
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, margin: '-100px' })

  if (!image && !eyebrow && !title1 && !title2 && !p1 && !p2 && !cardTitle && !cardSubtitle && !buttonLabel) {
    return null
  }

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
                    <span className="font-serif text-[8rem] font-light text-gold/8 italic leading-none select-none" />
                  </div>
                </>
              )}
              <div className="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-dark-100 to-transparent" />
            </div>

            {/* Floating stat card */}
            {(cardTitle || cardSubtitle) && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={isInView ? { opacity: 1, y: 0 } : {}}
                transition={{ duration: 0.7, delay: 0.5 }}
                className="absolute -bottom-6 -right-6 bg-dark border border-gold/20 px-7 py-6 hidden md:block"
              >
                {cardTitle && <div className="font-serif text-5xl text-gradient-gold">{cardTitle}</div>}
                {cardSubtitle && (
                  <div className="text-white/40 text-[10px] tracking-[0.3em] uppercase mt-1">
                    {cardSubtitle}
                  </div>
                )}
              </motion.div>
            )}

            {/* Gold border offset */}
            <div className="absolute -top-4 -left-4 w-24 h-24 border border-gold/15 hidden md:block" />
          </motion.div>

          {/* Text side */}
          <motion.div
            initial={{ opacity: 0, x: 40 }}
            animate={isInView ? { opacity: 1, x: 0 } : {}}
            transition={{ duration: 0.9, delay: 0.2 }}
          >
            {eyebrow && <p className="text-gold/70 tracking-[0.5em] uppercase text-[10px] mb-6">{eyebrow}</p>}
            {(title1 || title2) && (
              <h2 className="font-serif text-4xl md:text-5xl text-white font-light leading-tight mb-8">
                {title1}
                {title1 && title2 && <br />}
                {title2 && <span className="italic text-gradient-gold">{title2}</span>}
              </h2>
            )}
            {(p1 || p2) && <div className="w-12 h-px bg-gold mb-8" />}
            {p1 && <p className="text-white/55 leading-relaxed mb-6 text-lg font-light">{p1}</p>}
            {p2 && <p className="text-white/40 leading-relaxed mb-12 font-light">{p2}</p>}
            {buttonLabel && buttonUrl && (
              <Link
                to={buttonUrl}
                className="inline-flex items-center gap-4 text-gold text-xs tracking-[0.3em] uppercase group"
              >
                <span>{buttonLabel}</span>
                <span className="transition-all duration-300 group-hover:translate-x-2">→</span>
              </Link>
            )}
          </motion.div>

        </div>
      </div>
    </section>
  )
}
