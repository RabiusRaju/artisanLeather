import { createContext, useContext, useReducer, useEffect } from 'react'

const CartContext = createContext(null)

const STORAGE_KEY = 'al_cart'

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

export function CartProvider({ children }) {
  const [items, dispatch] = useReducer(cartReducer, [], loadCart)

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items))
  }, [items])

  const addItem    = (item)           => dispatch({ type: 'ADD', item })
  const removeItem = (key)            => dispatch({ type: 'REMOVE', key })
  const updateQty  = (key, quantity)  => dispatch({ type: 'UPDATE_QTY', key, quantity })
  const clearCart  = ()               => dispatch({ type: 'CLEAR' })

  const totalItems = items.reduce((sum, i) => sum + i.quantity, 0)
  const subtotal   = items.reduce((sum, i) => sum + i.price * i.quantity, 0)

  return (
    <CartContext.Provider value={{ items, addItem, removeItem, updateQty, clearCart, totalItems, subtotal }}>
      {children}
    </CartContext.Provider>
  )
}

export const useCart = () => useContext(CartContext)
