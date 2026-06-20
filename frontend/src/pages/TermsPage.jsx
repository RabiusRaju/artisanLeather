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

export default function TermsPage() {
  const email = useSetting('business.email', 'info@artisanleatherom.com')
  const returnPolicy = useSetting('orders.return_policy', 'We accept returns within 14 days of delivery for unused items in original packaging.')

  return (
    <div className="min-h-screen bg-dark">
      <SEO title="Terms of Service" description="The terms and conditions governing your use of the Artisan Leather website and orders." url="/terms" noIndex />

      <section className="pt-36 pb-12 px-6 lg:px-12 border-b border-gold/10 bg-dark-100">
        <div className="max-w-4xl mx-auto">
          <motion.div initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }}>
            <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-3">Legal</p>
            <h1 className="font-serif text-4xl md:text-5xl text-white font-light">Terms of Service</h1>
            <p className="text-white/30 text-xs mt-4">Last updated: June 2026</p>
          </motion.div>
        </div>
      </section>

      <div className="max-w-4xl mx-auto px-6 lg:px-12 py-12">
        <Section title="Agreement to Terms">
          <p>
            By accessing or using the Artisan Leather website and placing an order, you agree to
            be bound by these Terms of Service. If you do not agree with any part of these terms,
            please do not use our website or services.
          </p>
        </Section>

        <Section title="Products & Pricing">
          <p>
            We make every effort to display our products and prices accurately. Prices are shown
            in your selected currency for convenience, but orders are processed and charged in
            Omani Rial (OMR) unless stated otherwise. We reserve the right to correct pricing
            errors and to change prices at any time without prior notice.
          </p>
        </Section>

        <Section title="Orders & Payment">
          <p>
            When you place an order, you are making an offer to purchase the selected products.
            We reserve the right to accept or decline any order, including in cases of suspected
            fraud, pricing errors, or stock unavailability. Coupon codes and discounts are subject
            to the terms displayed at the time of use and cannot be combined unless stated.
          </p>
        </Section>

        <Section title="Delivery">
          <p>
            We aim to deliver orders within the estimated timeframes provided at checkout.
            Delivery times are estimates only and may vary due to courier delays, customs
            processing for international shipments, or circumstances beyond our control.
          </p>
        </Section>

        <Section title="Returns & Exchanges">
          <p>{returnPolicy}</p>
        </Section>

        <Section title="Product Reviews">
          <p>
            Customers who have created an account may submit reviews for products they have
            purchased. Reviews are moderated and published at our discretion. Reviews must be
            honest, relevant, and free of offensive or unlawful content. We reserve the right to
            remove any review that violates these guidelines.
          </p>
        </Section>

        <Section title="Intellectual Property">
          <p>
            All content on this website — including text, images, logos, and designs — is the
            property of Artisan Leather or its licensors and is protected by applicable
            intellectual property laws. You may not reproduce, distribute, or use this content
            without our prior written consent.
          </p>
        </Section>

        <Section title="Limitation of Liability">
          <p>
            To the fullest extent permitted by law, Artisan Leather shall not be liable for any
            indirect, incidental, or consequential damages arising from your use of our website
            or products.
          </p>
        </Section>

        <Section title="Governing Law">
          <p>
            These Terms of Service are governed by the laws of the Sultanate of Oman, and any
            disputes shall be subject to the exclusive jurisdiction of the Omani courts.
          </p>
        </Section>

        <Section title="Contact Us">
          <p>If you have any questions about these Terms, please contact us at:</p>
          <p className="text-white/70">{email}</p>
        </Section>
      </div>
    </div>
  )
}
