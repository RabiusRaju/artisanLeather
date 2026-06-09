import SEO from '../components/SEO'
import Hero from '../components/Hero'
import Collections from '../components/Collections'
import Story from '../components/Story'
import FeaturedBrands from '../components/FeaturedBrands'
import Stats from '../components/Stats'
import BestSellers from '../components/BestSellers'
import Testimonials from '../components/Testimonials'
import { useSettings } from '../hooks/useSettings'

export default function Home() {
  const s = useSettings()
  return (
    <main>
      <SEO
        url="/"
        title={s['homepage.seo.meta_title'] || "Luxury Handcrafted Leather Goods — Wallets, Bags & Accessories"}
        description={s['homepage.seo.meta_description'] || "Discover premium handcrafted leather wallets, bags, belts and accessories from Artisan Leather, Muscat Oman. Free delivery across Oman and GCC. Shop now."}
        type="website"
      />
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
