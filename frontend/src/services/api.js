import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1',
  headers: { 'Accept': 'application/json' },
})

// Attach language header from localStorage on every request
api.interceptors.request.use((config) => {
  const lang = localStorage.getItem('i18nextLng') || 'en'
  config.headers['Accept-Language'] = lang.startsWith('ar') ? 'ar' : 'en'
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
export const fetchSettings    = ()            => api.get('/settings')
