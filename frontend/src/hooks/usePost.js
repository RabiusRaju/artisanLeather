import { useState, useEffect } from 'react'
import { fetchPost } from '../services/api'

export function usePost(slug) {
  const [post,    setPost]    = useState(null)
  const [loading, setLoading] = useState(true)
  const [error,   setError]   = useState(null)

  useEffect(() => {
    if (!slug) return
    let cancelled = false
    setLoading(true)
    fetchPost(slug)
      .then(res => { if (!cancelled) { setPost(res.data.data); setLoading(false) } })
      .catch(err => { if (!cancelled) { setError(err); setLoading(false) } })
    return () => { cancelled = true }
  }, [slug])

  return { post, loading, error }
}
