import { createContext, useContext, useState, useEffect, useCallback } from 'react'
import { fetchWishlist, toggleWishlist, syncWishlist } from '../services/api'
import { useAuth } from './AuthContext'

const WishlistContext = createContext(null)
const STORAGE_KEY = 'al_wishlist'

function loadLocalIds() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    return raw ? JSON.parse(raw) : []
  } catch {
    return []
  }
}

export function WishlistProvider({ children }) {
  const { user, loading: authLoading } = useAuth()
  const [productIds, setProductIds] = useState(loadLocalIds)
  const [products, setProducts] = useState([])
  const [loading, setLoading] = useState(false)

  const refresh = useCallback(async () => {
    if (!user) return
    setLoading(true)
    try {
      const res = await fetchWishlist()
      const items = res.data.data || res.data
      setProducts(items)
      setProductIds(items.map((p) => p.id))
    } finally {
      setLoading(false)
    }
  }, [user])

  // Sync localStorage wishlist to account on login
  useEffect(() => {
    if (authLoading || !user) return

    const localIds = loadLocalIds()
    if (localIds.length > 0) {
      syncWishlist(localIds)
        .then(() => {
          localStorage.removeItem(STORAGE_KEY)
          refresh()
        })
        .catch(() => refresh())
    } else {
      refresh()
    }
  }, [user, authLoading, refresh])

  // Persist guest wishlist
  useEffect(() => {
    if (!user) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(productIds))
    }
  }, [productIds, user])

  const toggle = async (productId) => {
    if (user) {
      const res = await toggleWishlist(productId)
      const inWishlist = res.data.data.in_wishlist
      setProductIds((ids) => inWishlist ? [...ids, productId] : ids.filter((id) => id !== productId))
      refresh()
      return inWishlist
    }

    let inWishlist
    setProductIds((ids) => {
      if (ids.includes(productId)) {
        inWishlist = false
        return ids.filter((id) => id !== productId)
      }
      inWishlist = true
      return [...ids, productId]
    })
    return inWishlist
  }

  const isInWishlist = (productId) => productIds.includes(productId)

  return (
    <WishlistContext.Provider value={{ productIds, products, loading, toggle, isInWishlist, refresh }}>
      {children}
    </WishlistContext.Provider>
  )
}

// eslint-disable-next-line react-refresh/only-export-components -- hook colocated with its provider
export const useWishlist = () => useContext(WishlistContext)
