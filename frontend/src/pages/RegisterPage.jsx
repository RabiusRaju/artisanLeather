import SEO from '../components/SEO'
import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { useAuth } from '../context/AuthContext'

export default function RegisterPage() {
  const { register } = useAuth()
  const navigate = useNavigate()

  const [form, setForm] = useState({
    name: '', email: '', phone: '', password: '', password_confirmation: ''
  })
  const [error,   setError]   = useState('')
  const [loading, setLoading] = useState(false)

  const set = (k) => (e) => setForm(f => ({ ...f, [k]: e.target.value }))

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    if (form.password !== form.password_confirmation) {
      setError('Passwords do not match.')
      return
    }
    setLoading(true)
    try {
      await register(form)
      navigate('/account')
    } catch (err) {
      const errors = err.response?.data?.errors
      if (errors) setError(Object.values(errors).flat().join(' '))
      else setError(err.response?.data?.message || 'Registration failed. Please try again.')
    } finally { setLoading(false) }
  }

  const input = 'w-full bg-dark-100 border border-white/10 focus:border-gold/50 text-white placeholder-white/20 px-4 py-3.5 text-sm outline-none transition-colors duration-300'

  return (
    <div className="min-h-screen bg-dark flex items-center justify-center px-6 py-16">
      <SEO title="Create Account" description="Join Artisan Leather and enjoy exclusive access to our handcrafted leather collections." url="/register" noIndex />
      <motion.div initial={{ opacity: 0, y: 24 }} animate={{ opacity: 1, y: 0 }}
        className="w-full max-w-md">
        <div className="text-center mb-10">
          <Link to="/">
            <img src="/logo-icon-transparent.png" alt="Artisan Leather" className="h-14 w-14 mx-auto mb-4" />
          </Link>
          <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-2">Join Us</p>
          <h1 className="font-serif text-3xl text-white font-light">Create Account</h1>
          <div className="w-12 h-px bg-gold mx-auto mt-4" />
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          {error && (
            <div className="border border-red-400/30 bg-red-400/5 px-4 py-3 text-red-400 text-sm">
              {error}
            </div>
          )}

          {[
            { key: 'name',                  label: 'Full Name',            type: 'text',     placeholder: 'Mohammed Al Rashidi', required: true },
            { key: 'email',                 label: 'Email Address',        type: 'email',    placeholder: 'hello@example.com',   required: true },
            { key: 'phone',                 label: 'Phone (optional)',     type: 'tel',      placeholder: '+968 ···· ····',      required: false },
            { key: 'password',              label: 'Password',             type: 'password', placeholder: '8+ characters',       required: true },
            { key: 'password_confirmation', label: 'Confirm Password',     type: 'password', placeholder: '••••••••',            required: true },
          ].map(f => (
            <div key={f.key}>
              <label className="block text-[9px] tracking-[0.35em] uppercase text-white/35 mb-2">
                {f.label} {f.required && <span className="text-gold">*</span>}
              </label>
              <input type={f.type} required={f.required} value={form[f.key]}
                onChange={set(f.key)} placeholder={f.placeholder} className={input} />
            </div>
          ))}

          <button type="submit" disabled={loading}
            className="w-full py-4 bg-gold text-dark text-[10px] tracking-[0.35em] uppercase font-bold hover:bg-gold-300 transition-all duration-300 disabled:opacity-60 mt-2">
            {loading ? 'Creating account…' : 'Create Account'}
          </button>
        </form>

        <p className="text-center text-white/35 text-sm mt-8">
          Already have an account?{' '}
          <Link to="/login" className="text-gold hover:underline">Sign in</Link>
        </p>
        <p className="text-center mt-4">
          <Link to="/" className="text-white/25 text-xs hover:text-gold transition-colors">← Back to store</Link>
        </p>
      </motion.div>
    </div>
  )
}
