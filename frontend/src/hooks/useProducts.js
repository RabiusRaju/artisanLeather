import { useState, useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import { fetchProducts } from '../services/api'

export function useProducts(params = {}) {
  const [products, setProducts] = useState([])
  const [loading,  setLoading]  = useState(true)
  const [error,    setError]    = useState(null)
  const { i18n } = useTranslation()

  const key = JSON.stringify(params)

  useEffect(() => {
    let cancelled = false
    setLoading(true)
    setError(null)

    fetchProducts(params)
      .then(res => { if (!cancelled) { setProducts(res.data.data); setLoading(false) } })
      .catch(err => { if (!cancelled) { setError(err); setLoading(false) } })

    return () => { cancelled = true }
  }, [key, i18n.language])

  return { products, loading, error }
}
