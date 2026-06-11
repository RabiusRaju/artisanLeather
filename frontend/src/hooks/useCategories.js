import { useState, useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import { fetchCategories } from '../services/api'

export function useCategories() {
  const [categories, setCategories] = useState([])
  const [loading,    setLoading]    = useState(true)
  const { i18n } = useTranslation()

  useEffect(() => {
    fetchCategories()
      .then(res => { setCategories(res.data.data); setLoading(false) })
      .catch(()  => setLoading(false))
  }, [i18n.language])

  return { categories, loading }
}
