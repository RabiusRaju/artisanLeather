import { useState } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import { motion } from 'framer-motion'
import { useAuth } from '../context/AuthContext'

export default function LoginPage() {
  const { login } = useAuth()
  const navigate  = useNavigate()
  const location  = useLocation()
  const from      = location.state?.from || '/'

  const [form,    setForm]    = useState({ email: '', password: '' })
  const [error,   setError]   = useState('')
  const [loading, setLoading] = useState(false)

  const set = (k) => (e) => setForm(f => ({ ...f, [k]: e.target.value }))

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      await login(form.email, form.password)
      navigate(from, { replace: true })
    } catch (err) {
      setError(err.response?.data?.message || 'Invalid email or password.')
    } finally { setLoading(false) }
  }

  const input = 'w-full bg-dark-100 border border-white/10 focus:border-gold/50 text-white placeholder-white/20 px-4 py-3.5 text-sm outline-none transition-colors duration-300'

  return (
    <div className="min-h-screen bg-dark flex items-center justify-center px-6">
      <motion.div initial={{ opacity: 0, y: 24 }} animate={{ opacity: 1, y: 0 }}
        className="w-full max-w-md">
        {/* Logo */}
        <div className="text-center mb-10">
          <Link to="/">
            <img src="/logo-icon.png" alt="Artisan Leather" className="h-14 w-14 mx-auto mb-4" />
          </Link>
          <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-2">Welcome Back</p>
          <h1 className="font-serif text-3xl text-white font-light">Sign In</h1>
          <div className="w-12 h-px bg-gold mx-auto mt-4" />
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          {error && (
            <div className="border border-red-400/30 bg-red-400/5 px-4 py-3 text-red-400 text-sm">
              {error}
            </div>
          )}

          <div>
            <label className="block text-[9px] tracking-[0.35em] uppercase text-white/35 mb-2">
              Email Address
            </label>
            <input type="email" required value={form.email} onChange={set('email')}
              placeholder="hello@example.com" className={input} />
          </div>

          <div>
            <label className="block text-[9px] tracking-[0.35em] uppercase text-white/35 mb-2">
              Password
            </label>
            <input type="password" required value={form.password} onChange={set('password')}
              placeholder="••••••••" className={input} />
          </div>

          <button type="submit" disabled={loading}
            className="w-full py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-gold-300 transition-all duration-300 disabled:opacity-60 mt-2">
            {loading ? 'Signing in…' : 'Sign In'}
          </button>
        </form>

        <p className="text-center text-white/35 text-sm mt-8">
          Don't have an account?{' '}
          <Link to="/register" className="text-gold hover:underline">Create one</Link>
        </p>
        <p className="text-center mt-4">
          <Link to="/" className="text-white/25 text-xs hover:text-gold transition-colors">
            ← Back to store
          </Link>
        </p>
      </motion.div>
    </div>
  )
}
