import { useState, useEffect } from 'react'
import { fetchPosts } from '../services/api'

export function usePosts(params = {}) {
  const [posts,   setPosts]   = useState([])
  const [loading, setLoading] = useState(true)
  const [error,   setError]   = useState(null)

  useEffect(() => {
    let cancelled = false
    setLoading(true)
    fetchPosts(params)
      .then(res => { if (!cancelled) { setPosts(res.data.data); setLoading(false) } })
      .catch(err => { if (!cancelled) { setError(err); setLoading(false) } })
    return () => { cancelled = true }
  }, [JSON.stringify(params)])

  return { posts, loading, error }
}
