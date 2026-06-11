import { createContext, useContext, useState, useCallback } from 'react'
import { AnimatePresence, motion } from 'framer-motion'
import { HiCheckCircle, HiExclamationCircle, HiInformationCircle, HiX } from 'react-icons/hi'

const ToastContext = createContext(null)

const ICONS = {
  success: HiCheckCircle,
  error:   HiExclamationCircle,
  info:    HiInformationCircle,
}

const ACCENTS = {
  success: 'border-emerald-400/30 text-emerald-400',
  error:   'border-red-400/30 text-red-400',
  info:    'border-gold/30 text-gold',
}

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([])

  const dismiss = useCallback((id) => {
    setToasts((prev) => prev.filter((t) => t.id !== id))
  }, [])

  const toast = useCallback((message, { type = 'info', duration = 5000 } = {}) => {
    const id = Date.now() + Math.random()
    setToasts((prev) => [...prev, { id, message, type }])
    if (duration > 0) {
      setTimeout(() => dismiss(id), duration)
    }
    return id
  }, [dismiss])

  return (
    <ToastContext.Provider value={{ toast, dismiss }}>
      {children}
      <div className="fixed bottom-6 right-6 z-[100] flex flex-col gap-3 w-[calc(100%-3rem)] max-w-sm">
        <AnimatePresence>
          {toasts.map(({ id, message, type }) => {
            const Icon = ICONS[type] || ICONS.info
            return (
              <motion.div
                key={id}
                initial={{ opacity: 0, y: 16, scale: 0.95 }}
                animate={{ opacity: 1, y: 0, scale: 1 }}
                exit={{ opacity: 0, x: 32, scale: 0.95 }}
                transition={{ duration: 0.25 }}
                className={`flex items-start gap-3 bg-dark-100 border ${ACCENTS[type] || ACCENTS.info} px-4 py-3 shadow-lg backdrop-blur-sm`}
              >
                <Icon size={20} className="flex-shrink-0 mt-0.5" />
                <p className="text-sm text-white/80 font-light leading-snug flex-1">{message}</p>
                <button
                  onClick={() => dismiss(id)}
                  className="text-white/30 hover:text-white/70 transition-colors duration-200 flex-shrink-0"
                  aria-label="Dismiss"
                >
                  <HiX size={16} />
                </button>
              </motion.div>
            )
          })}
        </AnimatePresence>
      </div>
    </ToastContext.Provider>
  )
}

// eslint-disable-next-line react-refresh/only-export-components -- hook colocated with its provider
export const useToast = () => useContext(ToastContext)
