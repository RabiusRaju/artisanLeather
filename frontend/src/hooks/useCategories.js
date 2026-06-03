import { useState, useEffect } from 'react'
import { fetchCategories } from '../services/api'

export function useCategories() {
  const [categories, setCategories] = useState([])
  const [loading,    setLoading]    = useState(true)

  useEffect(() => {
    fetchCategories()
      .then(res => { setCategories(res.data.data); setLoading(false) })
      .catch(()  => setLoading(false))
  }, [])

  return { categories, loading }
}
