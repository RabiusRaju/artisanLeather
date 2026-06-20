import { useState, useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import { fetchLegalPage } from '../services/api'

export function useLegalPage(slug) {
  const { i18n } = useTranslation()
  const isAr = i18n.language?.startsWith('ar')
  const [page,    setPage]    = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    setLoading(true)
    fetchLegalPage(slug)
      .then(res => { setPage(res.data.data); setLoading(false) })
      .catch(()  => { setPage(null); setLoading(false) })
  }, [slug])

  const title = page ? ((isAr && page.title_ar) ? page.title_ar : page.title) : ''
  const sections = (page?.sections || []).map(s => ({
    heading: (isAr && s.heading_ar) ? s.heading_ar : s.heading,
    body:    (isAr && s.body_ar)    ? s.body_ar    : s.body,
  }))

  return { title, lastUpdated: page?.last_updated, sections, loading }
}
