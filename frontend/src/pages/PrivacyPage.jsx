import { useSetting } from '../hooks/useSettings'
import { motion } from 'framer-motion'
import SEO from '../components/SEO'

function Section({ title, children }) {
  return (
    <div className="border-t border-white/8 py-8 first:border-0 first:pt-0">
      <h2 className="font-serif text-xl md:text-2xl text-white font-light mb-4">{title}</h2>
      <div className="text-white/50 font-light leading-relaxed text-[15px] space-y-3">
        {children}
      </div>
    </div>
  )
}

export default function PrivacyPage() {
  const email   = useSetting('business.email', 'info@artisanleatherom.com')
  const address = useSetting('business.address', 'Muscat, Sultanate of Oman')

  return (
    <div className="min-h-screen bg-dark">
      <SEO title="Privacy Policy" description="How Artisan Leather collects, uses and protects your personal information." url="/privacy" noIndex />

      <section className="pt-36 pb-12 px-6 lg:px-12 border-b border-gold/10 bg-dark-100">
        <div className="max-w-4xl mx-auto">
          <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }}>
            <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-3">Legal</p>
            <h1 className="font-serif text-4xl md:text-5xl text-white font-light">Privacy Policy</h1>
            <p className="text-white/30 text-xs mt-4">Last updated: June 2026</p>
          </motion.div>
        </div>
      </section>

      <div className="max-w-4xl mx-auto px-6 lg:px-12 py-12">
        <Section title="Introduction">
          <p>
            Artisan Leather ("we", "us", "our") respects your privacy and is committed to protecting
            the personal information you share with us when you visit our website or place an order.
            This policy explains what information we collect, how we use it, and the choices you have.
          </p>
        </Section>

        <Section title="Information We Collect">
          <p>When you browse our site, create an account, or place an order, we may collect:</p>
          <ul className="list-disc list-inside space-y-1.5 ml-2">
            <li>Name, email address, phone number, and delivery address</li>
            <li>Order history and items added to your cart or wishlist</li>
            <li>Payment confirmation details (we do not store full card numbers)</li>
            <li>Device, browser, and usage information collected via cookies and analytics tools</li>
          </ul>
        </Section>

        <Section title="How We Use Your Information">
          <p>We use the information we collect to:</p>
          <ul className="list-disc list-inside space-y-1.5 ml-2">
            <li>Process and deliver your orders, including coordination with delivery partners</li>
            <li>Communicate with you about your orders, account, or enquiries</li>
            <li>Improve our products, website, and customer experience</li>
            <li>Send promotional offers, where you have opted in to receive them</li>
            <li>Comply with legal and regulatory obligations</li>
          </ul>
        </Section>

        <Section title="Cookies">
          <p>
            We use cookies and similar technologies to keep you signed in, remember your cart and
            wishlist, remember your language and currency preferences, and understand how visitors
            use our site. You can disable cookies in your browser settings, though some features of
            the site may not work correctly as a result.
          </p>
        </Section>

        <Section title="Sharing Your Information">
          <p>
            We do not sell your personal information. We may share it with trusted third parties
            who help us operate our business, such as payment processors, delivery and courier
            partners, and IT service providers — solely for the purpose of fulfilling your order
            and operating our website. These partners are required to keep your information secure
            and use it only for the services they provide to us.
          </p>
        </Section>

        <Section title="Data Retention">
          <p>
            We retain your personal information for as long as necessary to fulfil the purposes
            described in this policy, including any legal, accounting, or reporting requirements.
          </p>
        </Section>

        <Section title="Your Rights">
          <p>
            You may request access to, correction of, or deletion of your personal information,
            and you may opt out of marketing communications at any time. To exercise these rights,
            please contact us using the details below.
          </p>
        </Section>

        <Section title="Contact Us">
          <p>If you have questions about this Privacy Policy or how we handle your data, contact us at:</p>
          <p className="text-white/70">
            {email}
            <br />
            {address}
          </p>
        </Section>
      </div>
    </div>
  )
}
