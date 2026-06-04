import { motion } from 'framer-motion'
import { FaWhatsapp } from 'react-icons/fa'
import { useSetting } from '../hooks/useSettings'

export default function WhatsAppButton() {
  const whatsapp = useSetting('business.whatsapp', '96812345678')
  const message  = useSetting('orders.whatsapp_message', "Hello Artisan Leather, I'm interested in your products.")
  const href = `https://wa.me/${whatsapp.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(message)}`

  return (
    <motion.a
      href={href}
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
