import { createContext, useContext, useReducer, useState, useEffect } from 'react'
import { validateCoupon } from '../services/api'

const CartContext = createContext(null)

const STORAGE_KEY = 'al_cart'
const COUPON_KEY = 'al_coupon'

function cartReducer(state, action) {
  switch (action.type) {
    case 'ADD': {
      const key = `${action.item.id}-${action.item.colorName}`
      const existing = state.find((i) => `${i.id}-${i.colorName}` === key)
      if (existing) {
        return state.map((i) =>
          `${i.id}-${i.colorName}` === key
            ? { ...i, quantity: i.quantity + action.item.quantity }
            : i
        )
      }
      return [...state, action.item]
    }
    case 'REMOVE':
      return state.filter((i) => `${i.id}-${i.colorName}` !== action.key)
    case 'UPDATE_QTY':
      return state.map((i) =>
        `${i.id}-${i.colorName}` === action.key
          ? { ...i, quantity: Math.max(1, action.quantity) }
          : i
      )
    case 'CLEAR':
      return []
    default:
      return state
  }
}

function loadCart() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    return raw ? JSON.parse(raw) : []
  } catch {
    return []
  }
}

function loadCoupon() {
  try {
    const raw = localStorage.getItem(COUPON_KEY)
    return raw ? JSON.parse(raw) : null
  } catch {
    return null
  }
}

export function CartProvider({ children }) {
  const [items, dispatch] = useReducer(cartReducer, [], loadCart)
  const [coupon, setCoupon] = useState(loadCoupon)
  const [couponError, setCouponError] = useState(null)
  const [couponLoading, setCouponLoading] = useState(false)

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items))
  }, [items])

  useEffect(() => {
    if (coupon) {
      localStorage.setItem(COUPON_KEY, JSON.stringify(coupon))
    } else {
      localStorage.removeItem(COUPON_KEY)
    }
  }, [coupon])

  const addItem    = (item)           => dispatch({ type: 'ADD', item })
  const removeItem = (key)            => dispatch({ type: 'REMOVE', key })
  const updateQty  = (key, quantity)  => dispatch({ type: 'UPDATE_QTY', key, quantity })
  const clearCart  = ()               => { dispatch({ type: 'CLEAR' }); setCoupon(null); setCouponError(null) }

  const totalItems = items.reduce((sum, i) => sum + i.quantity, 0)
  const subtotal   = items.reduce((sum, i) => sum + i.price * i.quantity, 0)

  const discount = coupon ? Math.min(coupon.discount_amount, subtotal) : 0
  const total = Math.max(0, subtotal - discount)

  const applyCoupon = async (code) => {
    setCouponLoading(true)
    setCouponError(null)
    try {
      const res = await validateCoupon(code, subtotal)
      setCoupon(res.data.data)
      return res.data.data
    } catch (err) {
      setCoupon(null)
      setCouponError(err.response?.data?.message || 'Invalid coupon code.')
      throw err
    } finally {
      setCouponLoading(false)
    }
  }

  const removeCoupon = () => {
    setCoupon(null)
    setCouponError(null)
  }

  return (
    <CartContext.Provider value={{
      items, addItem, removeItem, updateQty, clearCart, totalItems, subtotal,
      coupon, couponError, couponLoading, applyCoupon, removeCoupon, discount, total,
    }}>
      {children}
    </CartContext.Provider>
  )
}

export const useCart = () => useContext(CartContext)
