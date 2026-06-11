import { createContext, useContext, useState, useEffect } from 'react'
import axios from 'axios'

const API = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
const TOKEN_KEY = 'al_token'
const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user,    setUser]    = useState(null)
  const [loading, setLoading] = useState(true)

  // Restore session on mount
  useEffect(() => {
    const token = localStorage.getItem(TOKEN_KEY)
    if (!token) { setLoading(false); return }

    axios.get(`${API}/auth/me`, { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } })
      .then(res => setUser(res.data.user))
      .catch(() => localStorage.removeItem(TOKEN_KEY))
      .finally(() => setLoading(false))
  }, [])

  const authHeaders = () => ({
    Authorization: `Bearer ${localStorage.getItem(TOKEN_KEY)}`,
    Accept: 'application/json',
  })

  const register = async (data) => {
    const res = await axios.post(`${API}/auth/register`, data, { headers: { Accept: 'application/json' } })
    localStorage.setItem(TOKEN_KEY, res.data.token)
    setUser(res.data.user)
    return res.data.user
  }

  const login = async (email, password) => {
    const res = await axios.post(`${API}/auth/login`, { email, password }, { headers: { Accept: 'application/json' } })
    localStorage.setItem(TOKEN_KEY, res.data.token)
    setUser(res.data.user)
    return res.data.user
  }

  const logout = async () => {
    try { await axios.post(`${API}/auth/logout`, {}, { headers: authHeaders() }) } catch {}
    localStorage.removeItem(TOKEN_KEY)
    setUser(null)
  }

  const getMyOrders = () =>
    axios.get(`${API}/auth/orders`, { headers: authHeaders() }).then(r => r.data.data)

  const getLastOrder = () =>
    axios.get(`${API}/auth/last-order`, { headers: authHeaders() }).then(r => r.data.data)

  return (
    <AuthContext.Provider value={{ user, loading, register, login, logout, getMyOrders, getLastOrder }}>
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = () => useContext(AuthContext)
