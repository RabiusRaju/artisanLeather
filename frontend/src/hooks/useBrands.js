import { useState, useEffect } from 'react'
import { fetchBrands } from '../services/api'

export function useBrands() {
  const [brands,  setBrands]  = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchBrands()
      .then(res => { setBrands(res.data.data); setLoading(false) })
      .catch(() => setLoading(false))
  }, [])

  return { brands, loading }
}
