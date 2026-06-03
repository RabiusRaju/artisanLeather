import { useState, useEffect } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import { useAuth } from '../context/AuthContext'
import { HiShoppingBag, HiLogout, HiUser } from 'react-icons/hi'

function OrderRow({ order }) {
  const statusColor = {
    pending:    'text-yellow-400',
    confirmed:  'text-blue-400',
    processing: 'text-blue-400',
    shipped:    'text-green-400',
    delivered:  'text-green-400',
    cancelled:  'text-red-400',
  }
  return (
    <div className="flex items-center justify-between py-4 border-b border-white/7">
      <div>
        <p className="text-white font-medium text-sm">{order.order_number}</p>
        <p className="text-white/35 text-xs mt-0.5">
          {order.items_count} item{order.items_count !== 1 ? 's' : ''} ·{' '}
          {new Date(order.created_at).toLocaleDateString('en-OM', { day: 'numeric', month: 'short', year: 'numeric' })}
        </p>
      </div>
      <div className="text-right">
        <p className="text-gold text-sm">OMR {parseFloat(order.total_omr).toFixed(3)}</p>
        <p className={`text-xs capitalize mt-0.5 ${statusColor[order.status] || 'text-white/40'}`}>
          {order.status}
        </p>
      </div>
    </div>
  )
}

export default function AccountPage() {
  const { user, logout, getMyOrders, loading: authLoading } = useAuth()
  const navigate = useNavigate()
  const [orders,       setOrders]       = useState([])
  const [ordersLoading, setOrdersLoading] = useState(true)

  useEffect(() => {
    if (!authLoading && !user) navigate('/login', { state: { from: '/account' } })
  }, [user, authLoading, navigate])

  useEffect(() => {
    if (!user) return
    getMyOrders()
      .then(setOrders)
      .catch(() => setOrders([]))
      .finally(() => setOrdersLoading(false))
  }, [user])

  const handleLogout = async () => {
    await logout()
    navigate('/')
  }

  if (authLoading) return (
    <div className="min-h-screen bg-dark flex items-center justify-center">
      <div className="text-white/30 text-sm tracking-widest uppercase animate-pulse">Loading…</div>
    </div>
  )

  if (!user) return null

  return (
    <div className="min-h-screen bg-dark pt-32 pb-24 px-6 lg:px-12">
      <div className="max-w-3xl mx-auto">
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>

          {/* Header */}
          <div className="flex items-start justify-between mb-12">
            <div>
              <p className="text-gold/60 tracking-[0.5em] uppercase text-[10px] mb-3">My Account</p>
              <h1 className="font-serif text-4xl text-white font-light">
                Welcome, {user.name.split(' ')[0]}
              </h1>
              <div className="w-12 h-px bg-gold mt-4" />
            </div>
            <button onClick={handleLogout}
              className="flex items-center gap-2 text-white/30 hover:text-red-400 text-[10px] tracking-[0.3em] uppercase transition-colors mt-2">
              <HiLogout size={14} /> Sign Out
            </button>
          </div>

          {/* Profile card */}
          <div className="border border-gold/15 p-7 mb-8">
            <h2 className="font-serif text-xl text-white mb-6 flex items-center gap-3">
              <HiUser size={18} className="text-gold/60" /> Profile
            </h2>
            <div className="grid sm:grid-cols-3 gap-5">
              {[
                { label: 'Full Name', value: user.name },
                { label: 'Email',    value: user.email },
                { label: 'Phone',    value: user.phone || '—' },
              ].map(item => (
                <div key={item.label}>
                  <p className="text-[9px] tracking-[0.35em] uppercase text-white/30 mb-1">{item.label}</p>
                  <p className="text-white/70 text-sm">{item.value}</p>
                </div>
              ))}
            </div>
          </div>

          {/* Orders */}
          <div className="border border-gold/15 p-7">
            <h2 className="font-serif text-xl text-white mb-6 flex items-center gap-3">
              <HiShoppingBag size={18} className="text-gold/60" /> Order History
            </h2>

            {ordersLoading ? (
              <div className="space-y-4">
                {[1,2,3].map(i => (
                  <div key={i} className="animate-pulse flex justify-between py-4 border-b border-white/7">
                    <div className="space-y-2"><div className="h-3 bg-dark-100 w-32" /><div className="h-2 bg-dark-100 w-20" /></div>
                    <div className="space-y-2 text-right"><div className="h-3 bg-dark-100 w-20" /><div className="h-2 bg-dark-100 w-16" /></div>
                  </div>
                ))}
              </div>
            ) : orders.length === 0 ? (
              <div className="py-12 text-center">
                <HiShoppingBag size={40} className="text-gold/15 mx-auto mb-4" />
                <p className="text-white/30 text-sm mb-6">No orders yet.</p>
                <Link to="/collections"
                  className="inline-block px-8 py-3 bg-gold text-dark text-[10px] tracking-[0.3em] uppercase font-bold hover:bg-gold-300 transition-all">
                  Start Shopping
                </Link>
              </div>
            ) : (
              <div>
                {orders.map(o => <OrderRow key={o.order_number} order={o} />)}
              </div>
            )}
          </div>

        </motion.div>
      </div>
    </div>
  )
}
