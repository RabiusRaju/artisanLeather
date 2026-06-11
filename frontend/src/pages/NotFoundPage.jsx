import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { HiArrowLeft } from 'react-icons/hi'
import SEO from '../components/SEO'

export default function NotFoundPage() {
  return (
    <div className="min-h-screen bg-dark flex items-center justify-center px-6">
      <SEO title="Page Not Found" description="The page you're looking for doesn't exist." noIndex />

      <motion.div
        initial={{ opacity: 0, y: 24 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.6 }}
        className="text-center max-w-md"
      >
        <p className="font-serif text-7xl md:text-8xl text-gold/30 font-light mb-4">404</p>
        <h1 className="font-serif text-3xl text-white font-light mb-4">Page Not Found</h1>
        <p className="text-white/40 font-light mb-10 text-sm leading-relaxed">
          The page you're looking for doesn't exist or may have been moved.
        </p>
        <Link
          to="/"
          className="inline-flex items-center gap-3 px-10 py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-gold-300 transition-all duration-300"
        >
          <HiArrowLeft size={14} />
          Back to Home
        </Link>
      </motion.div>
    </div>
  )
}
