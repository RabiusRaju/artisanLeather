import SEO from '../components/SEO'
import Hero from '../components/Hero'
import Collections from '../components/Collections'
import Story from '../components/Story'
import FeaturedBrands from '../components/FeaturedBrands'
import Stats from '../components/Stats'
import BestSellers from '../components/BestSellers'
import Testimonials from '../components/Testimonials'

export default function Home() {
  return (
    <main>
      <SEO url="/" />
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
