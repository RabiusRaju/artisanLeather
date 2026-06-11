import { useState, useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import { fetchProduct } from '../services/api'

export function useProduct(identifier) {
  const [product, setProduct] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error,   setError]   = useState(null)
  const { i18n } = useTranslation()

  useEffect(() => {
    if (!identifier) return
    let cancelled = false
    setLoading(true)
    setError(null)
    setProduct(null)

    fetchProduct(identifier)
      .then(res => { if (!cancelled) { setProduct(res.data.data); setLoading(false) } })
      .catch(err => { if (!cancelled) { setError(err); setLoading(false) } })

    return () => { cancelled = true }
  }, [identifier, i18n.language])

  return { product, loading, error }
}
