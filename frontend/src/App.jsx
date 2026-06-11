import { useEffect, Suspense, lazy } from 'react'
import { BrowserRouter, Routes, Route, useLocation } from 'react-router-dom'
import { HelmetProvider } from 'react-helmet-async'
import { useTranslation } from 'react-i18next'
import './i18n'
import { AuthProvider }      from './context/AuthContext'
import { CartProvider }      from './context/CartContext'
import { CurrencyProvider }  from './context/CurrencyContext'
import { ThemeProvider }     from './context/ThemeContext'
import { WishlistProvider }  from './context/WishlistContext'
import { SettingsProvider, useSetting } from './hooks/useSettings'
import ThemeSelector        from './components/ThemeSelector'
import Analytics            from './components/Analytics'
import Navbar               from './components/Navbar'
import Footer               from './components/Footer'
import WhatsAppButton       from './components/WhatsAppButton'
import Home                 from './pages/Home'

const CollectionsPage   = lazy(() => import('./pages/CollectionsPage'))
const ProductPage       = lazy(() => import('./pages/ProductPage'))
const About             = lazy(() => import('./pages/About'))
const Contact           = lazy(() => import('./pages/Contact'))
const CartPage          = lazy(() => import('./pages/CartPage'))
const CheckoutPage      = lazy(() => import('./pages/CheckoutPage'))
const OrderConfirmation = lazy(() => import('./pages/OrderConfirmation'))
const LoginPage         = lazy(() => import('./pages/LoginPage'))
const RegisterPage      = lazy(() => import('./pages/RegisterPage'))
const AccountPage       = lazy(() => import('./pages/AccountPage'))
const TrackOrderPage    = lazy(() => import('./pages/TrackOrderPage'))
const BlogPage          = lazy(() => import('./pages/BlogPage'))
const BlogPostPage      = lazy(() => import('./pages/BlogPostPage'))
const SurveyPage        = lazy(() => import('./pages/SurveyPage'))
const WishlistPage      = lazy(() => import('./pages/WishlistPage'))

function RTLSyncer() {
  const { i18n } = useTranslation()
  useEffect(() => {
    const isAr = i18n.language === 'ar'
    document.documentElement.dir  = isAr ? 'rtl' : 'ltr'
    document.documentElement.lang = i18n.language
  }, [i18n.language])
  return null
}

function Layout() {
  const { pathname } = useLocation()
  const minimal = ['/checkout', '/order-confirmation', '/login', '/register'].includes(pathname)
  return (
    <>
      <RTLSyncer />
      <Analytics />
      <Navbar />
      <Suspense fallback={<div className="min-h-screen bg-dark" />}>
        <Routes>
          <Route path="/"                      element={<Home />} />
          <Route path="/collections"           element={<CollectionsPage />} />
          <Route path="/collections/:category" element={<CollectionsPage />} />
          <Route path="/product/:slug"         element={<ProductPage />} />
          <Route path="/blog"                  element={<BlogPage />} />
          <Route path="/blog/:slug"            element={<BlogPostPage />} />
          <Route path="/survey/:slug"          element={<SurveyPage />} />
          <Route path="/about"                 element={<About />} />
          <Route path="/contact"               element={<Contact />} />
          <Route path="/cart"                  element={<CartPage />} />
          <Route path="/checkout"              element={<CheckoutPage />} />
          <Route path="/order-confirmation"    element={<OrderConfirmation />} />
          <Route path="/login"                 element={<LoginPage />} />
          <Route path="/register"              element={<RegisterPage />} />
          <Route path="/account"              element={<AccountPage />} />
          <Route path="/track"                element={<TrackOrderPage />} />
          <Route path="/track/:orderNumber"   element={<TrackOrderPage />} />
          <Route path="/wishlist"             element={<WishlistPage />} />
        </Routes>
      </Suspense>
      {!minimal && <Footer />}
      {!minimal && <WhatsAppButton />}
      {!minimal && <ThemeSelector />}
    </>
  )
}

export default function App() {
  return (
    <HelmetProvider>
    <BrowserRouter>
      <SettingsProvider>
        <ThemeProvider>
          <AuthProvider>
            <WishlistProvider>
              <CurrencyProvider>
                <CartProvider>
                  <Layout />
                </CartProvider>
              </CurrencyProvider>
            </WishlistProvider>
          </AuthProvider>
        </ThemeProvider>
      </SettingsProvider>
    </BrowserRouter>
    </HelmetProvider>
  )
}
