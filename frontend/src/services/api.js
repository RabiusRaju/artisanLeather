import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1',
  headers: { 'Accept': 'application/json' },
})

// Attach language header + auth token from localStorage on every request
api.interceptors.request.use((config) => {
  const lang = localStorage.getItem('i18nextLng') || 'en'
  config.headers['Accept-Language'] = lang.startsWith('ar') ? 'ar' : (lang.startsWith('bn') ? 'bn' : 'en')

  const token = localStorage.getItem('al_token')
  if (token) {
    config.headers['Authorization'] = `Bearer ${token}`
  }

  const staffPin = localStorage.getItem('al_survey_staff_pin')
  if (staffPin) {
    config.headers['X-Staff-Pin'] = staffPin
  }

  return config
})

export const fetchProducts    = (params = {})  => api.get('/products',         { params })
export const fetchProduct     = (identifier)   => api.get(`/products/${identifier}`)
export const fetchCategories  = ()             => api.get('/categories')
export const fetchCurrencies  = ()             => api.get('/currencies')
export const placeOrder       = (data)         => api.post('/orders', data)
export const fetchBrands    = ()            => api.get('/brands')
export const trackOrder     = (orderNumber) => api.get(`/track/${orderNumber}`)
export const fetchPosts     = (params = {}) => api.get('/posts', { params })
export const fetchPost      = (slug)        => api.get(`/posts/${slug}`)
export const fetchSurvey      = (slug)        => api.get(`/surveys/${slug}`)
export const submitSurvey     = (slug, data, token) => api.post(`/surveys/${slug}/respond`, data, {
  headers: token ? { 'X-Survey-Token': token } : {}
})
export const verifyStaffPin   = (pin)         => api.post('/surveys/staff-unlock', { pin })
export const fetchSettings      = ()            => api.get('/settings')
export const fetchTestimonials  = ()            => api.get('/testimonials')
export const fetchFaqs          = ()            => api.get('/faqs')
export const fetchLegalPage     = (slug)        => api.get(`/legal/${slug}`)
export const fetchSharedProducts = (token)      => api.get(`/share/${token}`)

// Coupons
export const validateCoupon = (code, subtotal) => api.post('/coupons/validate', { code, subtotal })
export const fetchFeaturedCoupon = () => api.get('/coupons/featured')
export const subscribeNewsletter = (data) => api.post('/newsletter/subscribe', data)

// Reviews
export const fetchProductReviews = (productId, params = {}) => api.get(`/products/${productId}/reviews`, { params })
export const submitReview        = (productId, data)        => api.post(`/products/${productId}/reviews`, data)

// Wishlist
export const fetchWishlist = ()           => api.get('/wishlist')
export const toggleWishlist = (productId) => api.post(`/wishlist/${productId}`)
export const syncWishlist  = (productIds) => api.post('/wishlist/sync', { product_ids: productIds })
