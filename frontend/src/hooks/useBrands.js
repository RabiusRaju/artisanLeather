import { useState, useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import { fetchBrands } from '../services/api'

export function useBrands() {
  const [brands,  setBrands]  = useState([])
  const [loading, setLoading] = useState(true)
  const { i18n } = useTranslation()

  useEffect(() => {
    fetchBrands()
      .then(res => { setBrands(res.data.data); setLoading(false) })
      .catch(() => setLoading(false))
  }, [i18n.language])

  return { brands, loading }
}
