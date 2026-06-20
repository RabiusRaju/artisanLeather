import { useSetting } from '../hooks/useSettings'
import { useLegalPage } from '../hooks/useLegalPage'
import { motion } from 'framer-motion'
import SEO from '../components/SEO'

function Section({ title, children }) {
  return (
    <div className="border-t border-white/8 py-8 first:border-0 first:pt-0">
      <h2 className="font-serif text-xl md:text-2xl text-white font-light mb-4">{title}</h2>
      <div className="text-white/50 font-light leading-relaxed text-[15px] space-y-3 whitespace-pre-line">
        {children}
      </div>
    </div>
  )
}

export default function PrivacyPage() {
  const email   = useSetting('business.email', '')
  const address = useSetting('business.address', '')
  const { title, lastUpdated, sections, loading } = useLegalPage('privacy')

  return (
    <div className="min-h-screen bg-dark">
      <SEO title="Privacy Policy" description="How Artisan Leather collects, uses and protects your personal information." url="/privacy" noIndex />

      <section className="pt-36 pb-12 px-6 lg:px-12 border-b border-gold/10 bg-dark-100">
        <div className="max-w-4xl mx-auto">
          <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }}>
            <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-3">Legal</p>
            <h1 className="font-serif text-4xl md:text-5xl text-white font-light">{title || 'Privacy Policy'}</h1>
            {lastUpdated && <p className="text-white/30 text-xs mt-4">Last updated: {lastUpdated}</p>}
          </motion.div>
        </div>
      </section>

      <div className="max-w-4xl mx-auto px-6 lg:px-12 py-12">
        {loading ? (
          <p className="text-white/30 text-sm">Loading…</p>
        ) : (
          sections.map((s, i) => (
            <Section key={i} title={s.heading}>
              <p>{s.body}</p>
            </Section>
          ))
        )}

        {(email || address) && (
          <Section title="Contact Us">
            <p>If you have questions about this Privacy Policy or how we handle your data, contact us at:</p>
            <p className="text-white/70">
              {email}
              {email && address && <br />}
              {address}
            </p>
          </Section>
        )}
      </div>
    </div>
  )
}
