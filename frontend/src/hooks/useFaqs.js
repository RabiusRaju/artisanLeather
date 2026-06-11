import { useState, useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import { fetchFaqs } from '../services/api'

export function useFaqs() {
  const { i18n } = useTranslation()
  const [faqs,    setFaqs]    = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchFaqs()
      .then(res => { setFaqs(res.data.data); setLoading(false) })
      .catch(()  => setLoading(false))
  }, [i18n.language])

  return { faqs, loading }
}
