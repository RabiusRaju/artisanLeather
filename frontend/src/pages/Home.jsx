import { Helmet } from 'react-helmet-async'
import SEO from '../components/SEO'
import Hero from '../components/Hero'
import Collections from '../components/Collections'
import Story from '../components/Story'
import FeaturedBrands from '../components/FeaturedBrands'
import Stats from '../components/Stats'
import BestSellers from '../components/BestSellers'
import Testimonials from '../components/Testimonials'
import { useSettings } from '../hooks/useSettings'

const websiteSchema = {
  '@context': 'https://schema.org',
  '@type': 'WebSite',
  name: 'Artisan Leather',
  url: 'https://artisanleatherom.com',
}

const organizationSchema = {
  '@context': 'https://schema.org',
  '@type': 'Organization',
  name: 'Artisan Leather',
  url: 'https://artisanleatherom.com',
  logo: 'https://artisanleatherom.com/logo.png',
  description: 'Premium handcrafted leather goods — wallets, bags, belts and accessories. Made by artisans in Muscat, Oman.',
  address: { '@type': 'PostalAddress', addressLocality: 'Muscat', addressCountry: 'OM' },
  contactPoint: { '@type': 'ContactPoint', contactType: 'customer service', availableLanguage: ['English', 'Arabic'] },
  sameAs: ['https://www.instagram.com/artisanleather', 'https://www.facebook.com/artisanleather'],
}

export default function Home() {
  const s = useSettings()

  return (
    <main>
      <SEO
        url="/"
        title={s['homepage.seo.meta_title'] || "Luxury Leather Wallets, Bags & Accessories"}
        description={s['homepage.seo.meta_description'] || "Discover premium handcrafted leather wallets, bags, belts and accessories from Artisan Leather, Muscat Oman. Free delivery across Oman and GCC. Shop now."}
        type="website"
      />
      <Helmet>
        <script type="application/ld+json">{JSON.stringify(organizationSchema)}</script>
        <script type="application/ld+json">{JSON.stringify(websiteSchema)}</script>
      </Helmet>
      <Hero />
      <Collections />
      <Story />
      <FeaturedBrands />
      <Stats />
      <BestSellers />
      <Testimonials />
    </main>
  )
}
