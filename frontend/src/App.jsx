import { useEffect } from 'react'
import { BrowserRouter, Routes, Route, useLocation } from 'react-router-dom'
import { HelmetProvider } from 'react-helmet-async'
import { useTranslation } from 'react-i18next'
import './i18n'
import { AuthProvider }      from './context/AuthContext'
import { CartProvider }      from './context/CartContext'
import { CurrencyProvider }  from './context/CurrencyContext'
import { ThemeProvider }     from './context/ThemeContext'
import { SettingsProvider, useSetting } from './hooks/useSettings'
import ThemeSelector        from './components/ThemeSelector'
import Analytics            from './components/Analytics'
import Navbar               from './components/Navbar'
import Footer               from './components/Footer'
import WhatsAppButton       from './components/WhatsAppButton'
import Home                 from './pages/Home'
import CollectionsPage      from './pages/CollectionsPage'
import ProductPage          from './pages/ProductPage'
import About                from './pages/About'
import Contact              from './pages/Contact'
import CartPage             from './pages/CartPage'
import CheckoutPage         from './pages/CheckoutPage'
import OrderConfirmation    from './pages/OrderConfirmation'
import LoginPage            from './pages/LoginPage'
import RegisterPage         from './pages/RegisterPage'
import AccountPage          from './pages/AccountPage'
import TrackOrderPage       from './pages/TrackOrderPage'
import BlogPage             from './pages/BlogPage'
import BlogPostPage         from './pages/BlogPostPage'
import SurveyPage           from './pages/SurveyPage'

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
      </Routes>
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
            <CurrencyProvider>
              <CartProvider>
                <Layout />
              </CartProvider>
            </CurrencyProvider>
          </AuthProvider>
        </ThemeProvider>
      </SettingsProvider>
    </BrowserRouter>
    </HelmetProvider>
  )
}
