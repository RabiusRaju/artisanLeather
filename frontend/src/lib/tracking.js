// Central event tracking — fans every conversion event out to Meta Pixel,
// GA4 (gtag), and the GTM dataLayer in one call. Safe to call before the
// pixel/gtag scripts finish loading (Analytics.jsx injects them async based
// on admin settings) — each call is a no-op if the global isn't ready yet.
const CURRENCY = 'OMR'

function pushDataLayer(payload) {
  if (typeof window === 'undefined') return
  window.dataLayer = window.dataLayer || []
  window.dataLayer.push(payload)
}

function fbqTrack(event, params) {
  if (typeof window !== 'undefined' && typeof window.fbq === 'function') {
    window.fbq('track', event, params)
  }
}

// Meta only lets you fire arbitrary event names (anything not in its fixed
// "standard events" list — Lead, Purchase, etc.) through 'trackCustom'.
function fbqTrackCustom(event, params) {
  if (typeof window !== 'undefined' && typeof window.fbq === 'function') {
    window.fbq('trackCustom', event, params)
  }
}

function gtagEvent(event, params) {
  if (typeof window !== 'undefined' && typeof window.gtag === 'function') {
    window.gtag('event', event, params)
  }
}

// Maps a route to a short key used to build a distinctly-named event for
// that specific page (e.g. 'homepage' -> page_view_homepage / PageViewHomepage),
// on top of the standard page_view — so each of these pages gets its own
// easy-to-find event in GA4/Meta instead of being lumped under one generic
// pageview. Sub-paths (e.g. /collections/bags) still match their parent.
const NAMED_PAGES = [
  { match: (p) => p === '/',        key: 'homepage' },
  { match: (p) => p === '/blog',    key: 'journal' },
  { match: (p) => p === '/about',   key: 'our_story' },
  { match: (p) => p === '/contact', key: 'contact' },
  { match: (p) => p === '/privacy', key: 'privacy_policy' },
  { match: (p) => p === '/terms',   key: 'terms_of_condition' },
  { match: (p) => p.startsWith('/collections'), key: 'collections' },
]

function toPascalCase(key) {
  return key.split('_').map((w) => w.charAt(0).toUpperCase() + w.slice(1)).join('')
}

export function trackPageView(pathname, title) {
  const pageLocation = typeof window !== 'undefined' ? window.location.href : pathname

  gtagEvent('page_view', { page_path: pathname, page_location: pageLocation, page_title: title })
  fbqTrack('PageView', {})
  pushDataLayer({ event: 'page_view', page_path: pathname, page_title: title })

  const namedPage = NAMED_PAGES.find((p) => p.match(pathname))
  if (namedPage) {
    const eventName = `page_view_${namedPage.key}`
    gtagEvent(eventName, { page_path: pathname, page_title: title })
    fbqTrackCustom(`PageView${toPascalCase(namedPage.key)}`, { page_path: pathname })
    pushDataLayer({ event: eventName, page_path: pathname, page_title: title })
  }
}

export function trackViewContent(product) {
  const value = parseFloat(product.price) || 0
  fbqTrack('ViewContent', {
    content_name: product.name,
    content_ids: [String(product.id)],
    content_type: 'product',
    value,
    currency: CURRENCY,
  })
  gtagEvent('view_item', {
    currency: CURRENCY,
    value,
    items: [{ item_id: String(product.id), item_name: product.name, price: value }],
  })
  pushDataLayer({ event: 'view_item', ecommerce: { currency: CURRENCY, value, item_id: product.id, item_name: product.name } })
}

export function trackAddToCart(item) {
  const unitPrice = parseFloat(item.price) || 0
  const value = unitPrice * (item.quantity || 1)
  fbqTrack('AddToCart', {
    content_name: item.name,
    content_ids: [String(item.id)],
    content_type: 'product',
    value,
    currency: CURRENCY,
  })
  gtagEvent('add_to_cart', {
    currency: CURRENCY,
    value,
    items: [{ item_id: String(item.id), item_name: item.name, price: unitPrice, quantity: item.quantity || 1 }],
  })
  pushDataLayer({ event: 'add_to_cart', ecommerce: { currency: CURRENCY, value, item_id: item.id, item_name: item.name, quantity: item.quantity } })
}

export function trackAddToWishlist(product) {
  const value = parseFloat(product.price) || 0
  fbqTrack('AddToWishlist', {
    content_name: product.name,
    content_ids: [String(product.id)],
    content_type: 'product',
    value,
    currency: CURRENCY,
  })
  gtagEvent('add_to_wishlist', {
    currency: CURRENCY,
    value,
    items: [{ item_id: String(product.id), item_name: product.name, price: value }],
  })
  pushDataLayer({ event: 'add_to_wishlist', ecommerce: { currency: CURRENCY, value, item_id: product.id, item_name: product.name } })
}

export function trackInitiateCheckout({ value, numItems, contentIds = [] }) {
  fbqTrack('InitiateCheckout', {
    value,
    currency: CURRENCY,
    num_items: numItems,
    content_ids: contentIds.map(String),
    content_type: 'product',
  })
  gtagEvent('begin_checkout', { currency: CURRENCY, value })
  pushDataLayer({ event: 'begin_checkout', ecommerce: { currency: CURRENCY, value, num_items: numItems } })
}

export function trackPurchase({ orderId, value, items = [] }) {
  const contentIds = items.map((i) => String(i.id ?? i.product_id ?? ''))
  fbqTrack('Purchase', {
    value,
    currency: CURRENCY,
    content_type: 'product',
    content_ids: contentIds,
  })
  gtagEvent('purchase', {
    transaction_id: String(orderId),
    currency: CURRENCY,
    value,
    items: items.map((i) => ({
      item_id: String(i.id ?? i.product_id ?? ''),
      item_name: i.name ?? i.product_name ?? '',
      price: parseFloat(i.price ?? i.unit_price ?? 0),
      quantity: i.quantity ?? 1,
    })),
  })
  pushDataLayer({ event: 'purchase', ecommerce: { transaction_id: String(orderId), currency: CURRENCY, value } })
}

// source: a short label like 'whatsapp_button', 'whatsapp_product_enquiry', 'contact_form'
export function trackLead(source) {
  fbqTrack('Lead', { content_name: source })
  gtagEvent('generate_lead', { lead_source: source })
  pushDataLayer({ event: 'generate_lead', lead_source: source })
}

// method: 'whatsapp' | 'facebook' | 'x' | 'linkedin' | 'copy_link'
export function trackShare(method, contentName) {
  gtagEvent('share', { method, content_type: 'article', item_id: contentName })
  pushDataLayer({ event: 'share', method, content_name: contentName })
}
