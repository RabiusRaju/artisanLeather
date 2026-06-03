import { Helmet } from 'react-helmet-async'

const SITE_NAME  = 'Artisan Leather'
const SITE_URL   = 'https://artisanleatherom.com'
const DEFAULT_DESC = 'Premium handcrafted leather goods — wallets, bags, belts and accessories. Made in Muscat, Oman. Delivered across the GCC.'
const DEFAULT_IMG  = `${SITE_URL}/og-image.jpg`

export default function SEO({
  title,
  description = DEFAULT_DESC,
  image       = DEFAULT_IMG,
  url,
  type        = 'website',
  noIndex     = false,
}) {
  const fullTitle = title ? `${title} | ${SITE_NAME}` : `${SITE_NAME} — Luxury Leather Goods, Muscat Oman`
  const canonical = url ? `${SITE_URL}${url}` : SITE_URL

  return (
    <Helmet>
      {/* Basic */}
      <title>{fullTitle}</title>
      <meta name="description" content={description} />
      <link rel="canonical" href={canonical} />
      {noIndex && <meta name="robots" content="noindex,nofollow" />}

      {/* Open Graph */}
      <meta property="og:type"        content={type} />
      <meta property="og:title"       content={fullTitle} />
      <meta property="og:description" content={description} />
      <meta property="og:image"       content={image} />
      <meta property="og:url"         content={canonical} />
      <meta property="og:site_name"   content={SITE_NAME} />
      <meta property="og:locale"      content="en_OM" />

      {/* Twitter Card */}
      <meta name="twitter:card"        content="summary_large_image" />
      <meta name="twitter:title"       content={fullTitle} />
      <meta name="twitter:description" content={description} />
      <meta name="twitter:image"       content={image} />

      {/* Geo / region tags for Oman */}
      <meta name="geo.region"   content="OM" />
      <meta name="geo.placename" content="Muscat" />
    </Helmet>
  )
}
