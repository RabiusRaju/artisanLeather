import { useEffect, useState } from 'react'
import { useLocation } from 'react-router-dom'
import { Helmet } from 'react-helmet-async'
import { useSettings } from '../hooks/useSettings'
import { trackPageView } from '../lib/tracking'

function injectScript(id, js) {
  if (document.getElementById(id)) return
  const el = document.createElement('script')
  el.id = id
  el.innerHTML = js
  document.head.appendChild(el)
}

function injectScriptSrc(id, src) {
  if (document.getElementById(id)) return
  const el = document.createElement('script')
  el.id    = id
  el.async = true
  el.src   = src
  document.head.appendChild(el)
}

export default function Analytics() {
  const s = useSettings()
  const { pathname } = useLocation()
  const [analyticsAllowed, setAnalyticsAllowed] = useState(() => {
    if (typeof window === 'undefined') return false
    return localStorage.getItem('al_cookie_consent') === 'accepted'
  })

  const ga4     = (s['seo.google_analytics']  || '').trim()
  const gtm     = (s['seo.google_tag_manager'] || '').trim()
  const pixel   = (s['seo.meta_pixel']        || '').trim()
  const clarity = (s['seo.clarity']           || '').trim()
  const gsc     = (s['seo.search_console']    || '').trim()

  useEffect(() => {
    const updateConsent = () => {
      setAnalyticsAllowed(localStorage.getItem('al_cookie_consent') === 'accepted')
    }
    window.addEventListener('al-cookie-consent', updateConsent)
    window.addEventListener('storage', updateConsent)
    return () => {
      window.removeEventListener('al-cookie-consent', updateConsent)
      window.removeEventListener('storage', updateConsent)
    }
  }, [])

  useEffect(() => {
    if (!analyticsAllowed) return

    // ── Google Analytics 4 ───────────────────────────────────────────────
    if (ga4) {
      injectScriptSrc('ga4-loader', `https://www.googletagmanager.com/gtag/js?id=${ga4}`)
      injectScript('ga4-init', `
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '${ga4}', { send_page_view: false });
      `)
    }

    // ── Google Tag Manager ───────────────────────────────────────────────
    if (gtm) {
      injectScript('gtm-init', `
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;
        f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','${gtm}');
      `)
    }

    // ── Meta (Facebook) Pixel ────────────────────────────────────────────
    if (pixel) {
      injectScript('meta-pixel', `
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
        (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init','${pixel}');
      `)
    }

    // ── Microsoft Clarity ────────────────────────────────────────────────
    if (clarity) {
      injectScript('ms-clarity', `
        (function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window,document,"clarity","script","${clarity}");
      `)
    }
  }, [analyticsAllowed, ga4, gtm, pixel, clarity])

  // ── Fire a page view on every route change (initial load + in-app nav) ──
  // SPA navigation never reloads the document, so without this, GA4/Meta only
  // ever see the very first URL the visitor landed on — every page after
  // that goes untracked. A short delay lets each page's <SEO>/Helmet title
  // commit first so page_title isn't stale from the previous page.
  useEffect(() => {
    if (!analyticsAllowed) return
    const timer = setTimeout(() => trackPageView(pathname, document.title), 50)
    return () => clearTimeout(timer)
  }, [analyticsAllowed, pathname])

  // Google Search Console only needs a meta tag — no script
  return gsc ? (
    <Helmet>
      <meta name="google-site-verification" content={gsc} />
    </Helmet>
  ) : null
}
