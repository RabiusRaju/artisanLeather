import { motion } from 'framer-motion'
import { FaWhatsapp } from 'react-icons/fa'

export default function WhatsAppButton() {
  return (
    <motion.a
      href="https://wa.me/96812345678?text=Hello%20Artisan%20Leather%2C%20I'm%20interested%20in%20your%20products."
      target="_blank"
      rel="noopener noreferrer"
      initial={{ scale: 0, opacity: 0 }}
      animate={{ scale: 1, opacity: 1 }}
      transition={{ delay: 2.5, type: 'spring', stiffness: 200 }}
      whileHover={{ scale: 1.08 }}
      whileTap={{ scale: 0.95 }}
      className="fixed bottom-8 right-8 z-50 bg-[#25D366] text-white flex items-center gap-2.5 px-4 py-3.5 shadow-2xl shadow-black/40 group"
      aria-label="Chat on WhatsApp"
    >
      <FaWhatsapp size={22} />
      <span className="text-xs font-semibold tracking-wider pr-1 hidden sm:block">
        WhatsApp
      </span>
    </motion.a>
  )
}
