// Captures utm_source/medium/campaign/term/content (+ gclid/fbclid) from the
// landing URL and persists them so they can be attached to an order placed
// later in the same browser, even after several page views with no query
// string. Last-touch model: a fresh utm_* on a later visit overwrites the
// stored one; visits with no utm params leave the stored attribution intact.
const STORAGE_KEY = 'al_utm'
const UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid']

export function captureUtmParams() {
  if (typeof window === 'undefined') return

  const params = new URLSearchParams(window.location.search)
  const found = {}
  for (const key of UTM_KEYS) {
    const value = params.get(key)
    if (value) found[key] = value
  }

  if (Object.keys(found).length === 0) return

  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify({ ...found, captured_at: new Date().toISOString() }))
  } catch {
    // localStorage unavailable (private mode etc.) — silently skip
  }
}

export function getUtmParams() {
  if (typeof window === 'undefined') return {}
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    return raw ? JSON.parse(raw) : {}
  } catch {
    return {}
  }
}
