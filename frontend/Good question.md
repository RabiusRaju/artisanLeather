Good question. Here are my recommendations — backoffice has significant SEO impact even though it's a private admin panel:

Backoffice SEO Recommendations
🔴 HIGH IMPACT — Must Do
1. Add SEO Fields to Products (in Filament)
Currently the products table has no meta_title, meta_description, or slug columns. The frontend uses product.name for the title and the numeric id in the URL (/product/3).

Problems:

URL /product/3 tells Google nothing. /product/heritage-bifold-wallet ranks for keywords
Admin can't customize SEO per product (a wallet and a bag get the same title template)
Recommendation: Add to products table:

slug (URL-friendly name, e.g. heritage-bifold-wallet)
meta_title (custom page title, max 60 chars)
meta_description (custom description, max 160 chars)
Then expose in Filament ProductResource form under a "SEO" tab.

2. Dynamic Sitemap from Laravel
The sitemap.xml we created is static — it doesn't include individual product URLs. Google won't discover /product/heritage-bifold-wallet, /product/slim-messenger etc.

Recommendation: Create a Laravel route /sitemap.xml that generates XML dynamically, including:

All active product URLs
All collection category URLs
Static pages (home, about, contact)
lastmod from updated_at
This auto-updates whenever you add/edit products in Filament.

3. Product Availability in API (Google Shopping)
The JSON-LD schema on product pages currently shows availability: InStock hardcoded. But stock is tracked in product_stock table.

Recommendation: The API response for products should include a computed in_stock boolean from product_stock.quantity > 0. This powers accurate structured data for Google Shopping.

🟡 MEDIUM IMPACT — Should Do
4. Add SEO Tab to Brands/Collections in Filament
Collections (/collections/wallets) rank for category keywords. Currently brands table has no meta_title or meta_description.

Recommendation: Add SEO fields to brands table and expose in the Brand resource in Filament.

5. Product Image Alt Text
Product images stored in the API have a url but likely no alt field. The frontend generates alt as ${product.name} - view 1 which is generic.

Recommendation: Add alt_text field to product_images table (or compute it from product + brand name) and return it in the API. Proper alt text helps image search ranking.

6. Google Analytics / Tag Manager Integration
The admin has no visibility into which products get the most traffic, where customers come from, or what they search. Without tracking, SEO improvements can't be measured.

Recommendation: Add Google Tag Manager ID field in admin Settings, so the frontend can inject GTM without code changes.

🟢 LONG-TERM — Strategic
7. Blog / Content Module
Content marketing drives organic traffic. "Best leather wallets in Oman", "How to care for leather goods" — these articles rank for long-tail keywords and build brand authority.

Recommendation: Add a simple posts table (title, slug, content, published_at, meta_title, meta_description) with a Filament editor. The frontend renders a /blog section.

8. OG Image per Product
Currently all product shares on WhatsApp/Instagram use the same generic og-image.jpg. Product-specific OG images dramatically increase click-through from social.

Recommendation: Use the first product image as OG image (already done in the frontend). But the API needs to return full, absolute image URLs (not relative paths), and images should be at least 1200×630px for ideal sharing.

Summary Table
Priority	Task	Effort	SEO Impact
🔴	Product slug + URL rewrite	Medium	Very High
🔴	meta_title / meta_description fields	Low	High
🔴	Dynamic Laravel sitemap	Low	High
🔴	Product availability from stock	Low	High
🟡	Brand/Collection SEO fields	Low	Medium
🟡	Image alt text in API	Low	Medium
🟡	Google Tag Manager admin setting	Low	Medium
🟢	Blog/content module	High	Long-term
The most important single change is replacing /product/3 with /product/heritage-bifold-wallet — this is a URL structure change that affects both the backend API (slugs), the frontend router, and all internal links. Want me to implement these when ready?