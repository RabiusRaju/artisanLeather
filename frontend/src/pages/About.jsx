import SEO from '../components/SEO'
import { useRef } from 'react'
import { Link } from 'react-router-dom'
import { motion, useInView } from 'framer-motion'

// ── Data ───────────────────────────────────────────────────────────────────
const craftSteps = [
  {
    num: '01',
    title: 'Select the Hide',
    body: 'Every hide is hand-inspected for natural grain, firmness, and character. Only the top 15% passes our standard — the rest is returned.',
  },
  {
    num: '02',
    title: 'Cut & Shape',
    body: 'Each pattern is traced and cut by hand using solid steel templates. No laser cutters — only a steady hand and decades of muscle memory.',
  },
  {
    num: '03',
    title: 'Hand Stitch',
    body: 'We use the saddle-stitch technique — two needles, one thread, pulled in opposite directions — creating a lock stitch that holds even if one side breaks.',
  },
  {
    num: '04',
    title: 'Finish & Age',
    body: 'Edges are bevelled, burnished, and hand-painted. The piece is conditioned with natural beeswax and left to settle — becoming truly itself.',
  },
]

const materials = [
  {
    name: 'Full Grain',
    subtitle: 'The Pinnacle of Leather',
    desc: 'The outermost layer of the hide — untouched by sanding or buffing. Full grain retains every natural mark, developing a rich unique patina over decades.',
    gradient: 'linear-gradient(135deg, #5A2C10, #3A1A08, #1E0C04)',
  },
  {
    name: 'Vegetable Tanned',
    subtitle: 'Slow-Made & Sustainable',
    desc: 'Tanned using plant extracts — bark, leaves, roots — over 30–60 days. The result is leather with remarkable firmness that softens and deepens with age.',
    gradient: 'linear-gradient(135deg, #3D2010, #261408, #160A04)',
  },
  {
    name: 'Italian Calfskin',
    subtitle: 'Silken & Refined',
    desc: 'Sourced from the finest Italian tanneries. Calfskin offers an unmatched surface — fine-grained, almost silk-like, ideal for slim wallets and dress pieces.',
    gradient: 'linear-gradient(135deg, #2A1A0A, #1A1006, #0C0803)',
  },
]

const values = [
  { number: 'I',   title: 'Heritage',     desc: 'Rooted in centuries of leather tradition. Every technique we use can be traced back further than any trend.' },
  { number: 'II',  title: 'Precision',    desc: 'Every millimeter is intentional. Every edge, stitch, and finish is measured and placed with care.' },
  { number: 'III', title: 'Longevity',    desc: 'We do not design for seasons. We design for decades. Our pieces are made to outlast the person who owns them first.' },
  { number: 'IV',  title: 'Authenticity', desc: 'No shortcuts. No synthetic blends. No compromise. What you hold is exactly what it claims to be.' },
]

const timeline = [
  { year: '2009', title: 'First Workshop',    desc: 'A small atelier opened in the heart of Muscat. Three craftsmen. One mission.' },
  { year: '2013', title: 'First Collection',  desc: 'The Heritage Collection — six wallets and two belts — sold out in three weeks.' },
  { year: '2018', title: 'GCC Expansion',     desc: 'Artisan Leather pieces reached Dubai, Riyadh, and Kuwait through word of mouth alone.' },
  { year: '2023', title: 'Flagship Identity', desc: 'The gold-and-black mark became recognised across the Gulf.' },
  { year: '2025', title: 'Online Launch',     desc: 'Bringing our full collection online — crafted in Oman, delivered to the world.' },
]

// ── Sub-components ─────────────────────────────────────────────────────────
function CraftStep({ step, index }) {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, margin: '-60px' })
  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, x: -24 }}
      animate={isInView ? { opacity: 1, x: 0 } : {}}
      transition={{ duration: 0.7, delay: index * 0.1 }}
      className="flex gap-8 md:gap-12 items-start group py-10 border-b border-white/5 last:border-0"
    >
      <div className="flex-shrink-0 w-10 h-10 rounded-full border border-gold/30 group-hover:border-gold group-hover:bg-gold/5 flex items-center justify-center transition-all duration-400 relative z-10 bg-dark-100">
        <span className="font-serif text-gold/60 group-hover:text-gold text-xs transition-colors duration-400">
          {step.num}
        </span>
      </div>
      <div className="pt-1.5 flex-1 max-w-xl">
        <h3 className="font-serif text-2xl text-white group-hover:text-gold/90 transition-colors duration-300 mb-3">
          {step.title}
        </h3>
        <p className="text-white/45 font-light leading-relaxed text-[15px]">{step.body}</p>
      </div>
    </motion.div>
  )
}

function MaterialCard({ mat, index }) {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, margin: '-60px' })
  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, y: 32 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.7, delay: index * 0.15 }}
      className="group border border-gold/10 hover:border-gold/30 transition-all duration-500 overflow-hidden"
    >
      <div className="relative" style={{ aspectRatio: '4/2', background: mat.gradient }}>
        <div className="absolute inset-4 border border-dashed border-white/[0.04]" />
        <div className="absolute bottom-4 left-4 right-4 h-px bg-gradient-to-r from-gold/30 to-transparent" />
      </div>
      <div className="p-8">
        <p className="text-[9px] tracking-[0.4em] uppercase text-gold/50 mb-2">{mat.subtitle}</p>
        <h3 className="font-serif text-2xl text-white group-hover:text-gold transition-colors duration-400 mb-4">
          {mat.name}
        </h3>
        <div className="w-8 h-px bg-gold/40 mb-5" />
        <p className="text-white/40 font-light text-sm leading-relaxed">{mat.desc}</p>
      </div>
    </motion.div>
  )
}

function ValueCard({ v, index }) {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, margin: '-60px' })
  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, y: 28 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.6, delay: index * 0.12 }}
      className="text-center group"
    >
      <div className="font-serif text-5xl text-gradient-gold mb-5 inline-block group-hover:scale-110 transition-transform duration-400">
        {v.number}
      </div>
      <h3 className="font-serif text-xl text-white mb-3 group-hover:text-gold transition-colors duration-300">
        {v.title}
      </h3>
      <div className="w-8 h-px bg-gold/40 mx-auto mb-4" />
      <p className="text-white/35 text-sm font-light leading-relaxed">{v.desc}</p>
    </motion.div>
  )
}

function TimelineItem({ item, index }) {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true, margin: '-40px' })
  return (
    <motion.div
      ref={ref}
      initial={{ opacity: 0, y: 24 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.6, delay: index * 0.1 }}
      className="relative group"
    >
      <div className="flex justify-center md:justify-start mb-4">
        <div className="w-4 h-4 rounded-full border border-gold/40 group-hover:border-gold group-hover:bg-gold/20 bg-dark transition-all duration-400 relative z-10" />
      </div>
      <div className="md:pt-4 text-center md:text-left">
        <p className="font-serif text-2xl text-gradient-gold mb-2">{item.year}</p>
        <h4 className="text-white text-sm font-medium mb-2 group-hover:text-gold transition-colors duration-300">
          {item.title}
        </h4>
        <p className="text-white/35 text-xs font-light leading-relaxed">{item.desc}</p>
      </div>
    </motion.div>
  )
}

function StorySection() {
  const leftRef  = useRef(null)
  const rightRef = useRef(null)
  const leftInView  = useInView(leftRef,  { once: true, margin: '-100px' })
  const rightInView = useInView(rightRef, { once: true, margin: '-100px' })

  return (
    <section className="py-28 px-6 lg:px-12 max-w-7xl mx-auto">
      <div className="grid md:grid-cols-2 gap-20 items-center">
        <motion.div
          ref={leftRef}
          initial={{ opacity: 0, x: -32 }}
          animate={leftInView ? { opacity: 1, x: 0 } : {}}
          transition={{ duration: 0.9 }}
          className="relative"
        >
          <div className="relative overflow-hidden" style={{ aspectRatio: '4/5', background: 'linear-gradient(135deg, #3A2210, #240F06, #160E06)' }}>
            <div className="absolute inset-8 border border-dashed border-gold/8" />
            <div className="absolute inset-0 flex items-center justify-center select-none pointer-events-none">
              <span className="font-serif text-[12rem] font-bold leading-none text-white/[0.025] italic">AL</span>
            </div>
            <div className="absolute bottom-0 left-0 right-0 h-40 bg-gradient-to-t from-dark-100/80 to-transparent" />
          </div>
          <div className="absolute -top-5 -left-5 w-28 h-28 border border-gold/12 hidden md:block" />
          <motion.div
            initial={{ opacity: 0, scale: 0.85 }}
            animate={leftInView ? { opacity: 1, scale: 1 } : {}}
            transition={{ duration: 0.7, delay: 0.55 }}
            className="absolute -bottom-8 -right-6 bg-dark border border-gold/20 px-8 py-6 hidden md:block"
          >
            <div className="font-serif text-5xl text-gradient-gold">16+</div>
            <div className="text-white/35 text-[9px] tracking-[0.35em] uppercase mt-1.5">Years of Craft</div>
          </motion.div>
        </motion.div>

        <motion.div
          ref={rightRef}
          initial={{ opacity: 0, x: 32 }}
          animate={rightInView ? { opacity: 1, x: 0 } : {}}
          transition={{ duration: 0.9, delay: 0.15 }}
        >
          <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-5">Our Story</p>
          <h2 className="font-serif text-4xl md:text-5xl text-white font-light leading-tight mb-8">
            Born from a Love
            <br />
            <span className="italic text-gradient-gold">of the Craft</span>
          </h2>
          <div className="w-12 h-px bg-gold mb-8" />
          <p className="text-white/55 leading-loose mb-6 font-light text-[15px]">
            Artisan Leather began not as a business plan, but as an obsession. Our founder spent
            years studying leatherwork — in Italy, in Morocco, and eventually in Oman — learning
            what makes a piece truly last.
          </p>
          <p className="text-white/45 leading-loose mb-6 font-light text-[15px]">
            The first workshop was a single room in Muscat. Three craftsmen. One set of tools.
            No shortcuts. That ethos has never changed, even as the brand has grown across the GCC.
          </p>
          <p className="text-white/38 leading-loose font-light text-[15px]">
            Today, every piece that leaves our workshop is still inspected by hand, still stitched
            by hand, and still conditioned by hand — because the day we stop caring is the day we
            stop being Artisan Leather.
          </p>
        </motion.div>
      </div>
    </section>
  )
}

function CTASection() {
  const ref = useRef(null)
  const isInView = useInView(ref, { once: true })
  return (
    <section ref={ref} className="py-28 px-6 bg-dark-100 border-t border-gold/10">
      <motion.div
        initial={{ opacity: 0, y: 24 }}
        animate={isInView ? { opacity: 1, y: 0 } : {}}
        transition={{ duration: 0.8 }}
        className="max-w-2xl mx-auto text-center"
      >
        <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-6">Start Your Journey</p>
        <h2 className="font-serif text-4xl md:text-5xl text-white font-light mb-6">
          Own a Piece of the Craft
        </h2>
        <div className="w-14 h-px bg-gold mx-auto mb-8" />
        <p className="text-white/40 font-light leading-relaxed mb-12 text-[15px]">
          Every wallet, bag, and belt we make is a promise — that the hands behind it
          cared as much as the hands that will carry it.
        </p>
        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Link
            to="/collections"
            className="px-12 py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-gold-300 transition-all duration-300"
          >
            Shop Collection
          </Link>
          <Link
            to="/contact"
            className="px-12 py-4 border border-white/20 text-white text-[10px] tracking-[0.35em] uppercase hover:border-gold hover:text-gold transition-all duration-300"
          >
            Get in Touch
          </Link>
        </div>
      </motion.div>
    </section>
  )
}

// ── Page ───────────────────────────────────────────────────────────────────
export default function About() {
  const craftRef     = useRef(null)
  const materialsRef = useRef(null)
  const valuesRef    = useRef(null)
  const timelineRef  = useRef(null)

  const craftInView     = useInView(craftRef,     { once: true, margin: '-80px' })
  const materialsInView = useInView(materialsRef, { once: true, margin: '-80px' })
  const valuesInView    = useInView(valuesRef,    { once: true, margin: '-80px' })
  const timelineInView  = useInView(timelineRef,  { once: true, margin: '-80px' })

  return (
    <div className="min-h-screen bg-dark">

      <SEO
        title="Our Story — Handcrafted Leather Artisans in Muscat, Oman"
        description="Learn about Artisan Leather's heritage, craftsmanship philosophy, and the skilled artisans behind every handcrafted leather piece made in Muscat, Oman."
        url="/about"
      />

      {/* HERO */}
      <section className="relative h-[70vh] min-h-[520px] flex items-center justify-center overflow-hidden">
        <div className="absolute inset-0" style={{ background: 'linear-gradient(to bottom, #150F06, #120D05)' }} />
        <div
          className="absolute inset-0 opacity-[0.06]"
          style={{
            backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)'/%3E%3C/svg%3E")`,
            backgroundSize: '200px',
          }}
        />
        <div className="absolute left-1/4 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-gold/10 to-transparent pointer-events-none" />
        <div className="absolute right-1/4 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-gold/10 to-transparent pointer-events-none" />

        <div className="relative z-10 text-center px-6 max-w-3xl mx-auto">
          <motion.p
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            className="text-gold/60 tracking-[0.6em] uppercase text-[10px] mb-8"
          >
            Muscat · Oman · Est. 2009
          </motion.p>
          <motion.h1
            initial={{ opacity: 0, y: 28 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.9, delay: 0.15 }}
            className="font-serif text-6xl md:text-7xl text-white font-light leading-[1.05] mb-8"
          >
            A Story Written
            <br />
            <span className="text-gradient-gold italic">in Leather</span>
          </motion.h1>
          <motion.div
            initial={{ scaleX: 0 }}
            animate={{ scaleX: 1 }}
            transition={{ duration: 0.7, delay: 0.5 }}
            className="w-16 h-px bg-gold mx-auto mb-8 origin-center"
          />
          <motion.p
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.8, delay: 0.7 }}
            className="text-white/45 text-lg font-light leading-relaxed"
          >
            Sixteen years of craft. One unwavering standard.
          </motion.p>
        </div>
      </section>

      {/* BRAND STORY */}
      <StorySection />

      {/* CRAFTSMANSHIP */}
      <section className="py-24 bg-dark-100">
        <div className="max-w-7xl mx-auto px-6 lg:px-12">
          <motion.div
            ref={craftRef}
            initial={{ opacity: 0, y: 24 }}
            animate={craftInView ? { opacity: 1, y: 0 } : {}}
            transition={{ duration: 0.7 }}
            className="mb-20"
          >
            <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-4">The Process</p>
            <h2 className="font-serif text-4xl md:text-5xl text-white font-light">The Art of Making</h2>
            <div className="w-14 h-px bg-gold mt-6" />
          </motion.div>

          <div className="relative">
            <div className="absolute left-[1.25rem] top-10 bottom-10 w-px bg-gradient-to-b from-gold/40 via-gold/15 to-transparent hidden md:block" />
            <div className="space-y-0">
              {craftSteps.map((step, i) => (
                <CraftStep key={step.num} step={step} index={i} />
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* MATERIALS */}
      <section className="py-24 px-6 lg:px-12 max-w-7xl mx-auto">
        <motion.div
          ref={materialsRef}
          initial={{ opacity: 0, y: 24 }}
          animate={materialsInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.7 }}
          className="text-center mb-20"
        >
          <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-4">What We Use</p>
          <h2 className="font-serif text-4xl md:text-5xl text-white font-light">Only the Finest Materials</h2>
          <div className="w-14 h-px bg-gold mx-auto mt-6" />
        </motion.div>
        <div className="grid md:grid-cols-3 gap-6">
          {materials.map((mat, i) => <MaterialCard key={mat.name} mat={mat} index={i} />)}
        </div>
      </section>

      {/* VALUES */}
      <section className="py-24 bg-dark-100 border-y border-gold/8">
        <div className="max-w-7xl mx-auto px-6 lg:px-12">
          <motion.div
            ref={valuesRef}
            initial={{ opacity: 0, y: 24 }}
            animate={valuesInView ? { opacity: 1, y: 0 } : {}}
            transition={{ duration: 0.7 }}
            className="text-center mb-20"
          >
            <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-4">What We Stand For</p>
            <h2 className="font-serif text-4xl md:text-5xl text-white font-light">Our Four Pillars</h2>
            <div className="w-14 h-px bg-gold mx-auto mt-6" />
          </motion.div>
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {values.map((v, i) => <ValueCard key={v.title} v={v} index={i} />)}
          </div>
        </div>
      </section>

      {/* TIMELINE */}
      <section className="py-24 px-6 lg:px-12 max-w-7xl mx-auto overflow-hidden">
        <motion.div
          ref={timelineRef}
          initial={{ opacity: 0, y: 24 }}
          animate={timelineInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.7 }}
          className="text-center mb-20"
        >
          <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-4">Our Journey</p>
          <h2 className="font-serif text-4xl md:text-5xl text-white font-light">Sixteen Years in the Making</h2>
          <div className="w-14 h-px bg-gold mx-auto mt-6" />
        </motion.div>

        <div className="relative">
          <div className="absolute top-[0.45rem] left-0 right-0 h-px bg-gradient-to-r from-transparent via-gold/20 to-transparent hidden md:block" />
          <div className="grid md:grid-cols-5 gap-8 md:gap-4">
            {timeline.map((item, i) => <TimelineItem key={item.year} item={item} index={i} />)}
          </div>
        </div>
      </section>

      {/* CTA */}
      <CTASection />
    </div>
  )
}
