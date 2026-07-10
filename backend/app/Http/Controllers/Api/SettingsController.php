<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    // GET /api/v1/settings — only expose whitelisted public settings
    public function index()
    {
        // Load all settings by key (group column is never reliably set by Setting::set())
        $settings = Cache::remember('public_settings', 3600, fn() =>
            Setting::pluck('value', 'key')->toArray()
        );

        $locale = strtolower(substr(request()->header('Accept-Language', 'en'), 0, 2));

        $safe = [
            // Business
            'business.name'                  => $settings['business.name']             ?? 'Artisan Leather',
            'business.tagline'               => $settings['business.tagline']           ?? '',
            'business.email'                 => $settings['business.email']             ?? '',
            'business.phone'                 => $settings['business.phone']             ?? '',
            'business.whatsapp'              => $settings['business.whatsapp']          ?? '',
            'business.address'               => $settings['business.address']           ?? ($locale === 'ar' ? 'مسقط، سلطنة عُمان' : 'Muscat, Sultanate of Oman'),
            'business.address_2'             => $settings['business.address_2']         ?? '',
            'business.whatsapp_hours'        => $settings['business.whatsapp_hours']    ?? '',
            'business.email_response_time'   => $settings['business.email_response_time'] ?? '',
            'business.city'                  => $settings['business.city']              ?? 'Muscat',

            // Social
            'social.instagram'               => $settings['social.instagram']           ?? '',
            'social.facebook'                => $settings['social.facebook']            ?? '',
            'social.tiktok'                  => $settings['social.tiktok']              ?? '',
            'social.twitter'                 => $settings['social.twitter']             ?? '',

            // Appearance
            'theme.default'                  => $settings['theme.default']             ?? 'warm-leather',
            'theme.lock_theme'                => $settings['theme.lock_theme']           ?? '0',

            // Website
            'website.url'                    => $settings['website.url']                ?? 'https://artisanleatherom.com',
            'website.support_email'          => $settings['website.support_email']      ?? '',

            // Orders
            'orders.free_delivery_threshold' => $settings['orders.free_delivery_threshold'] ?? '0',
            'orders.whatsapp_message'        => $settings['orders.whatsapp_message']    ?? '',
            'orders.return_policy'           => $settings['orders.return_policy']       ?? 'We accept returns within 14 days of delivery for unused items in original packaging.',

            // SEO
            'seo.meta_title'                 => $settings['seo.meta_title']             ?? '',
            'seo.meta_description'           => $settings['seo.meta_description']       ?? '',
            'seo.google_analytics'           => $settings['seo.google_analytics']       ?? '',
            'seo.google_tag_manager'         => $settings['seo.google_tag_manager']     ?? '',
            'seo.search_console'             => $settings['seo.search_console']         ?? '',
            'seo.meta_pixel'                 => $settings['seo.meta_pixel']             ?? '',
            'seo.clarity'                    => $settings['seo.clarity']                ?? '',
            'seo.google_business'            => $settings['seo.google_business']        ?? '',

            // Homepage Hero
            'hero.eyebrow'                   => $settings['hero.eyebrow']               ?? '',
            'hero.headline'                  => $settings['hero.headline']               ?? '',
            'hero.headline_accent'           => $settings['hero.headline_accent']        ?? '',
            'hero.subtitle'                  => $settings['hero.subtitle']               ?? '',
            'hero.cta_primary'               => $settings['hero.cta_primary']            ?? '',
            'hero.cta_primary_url'           => $settings['hero.cta_primary_url']        ?? '',
            'hero.cta_secondary'             => $settings['hero.cta_secondary']          ?? '',
            'hero.cta_secondary_url'         => $settings['hero.cta_secondary_url']      ?? '',
            'hero.scroll_label'              => $settings['hero.scroll_label']           ?? '',

            // Homepage Stats
            'stats.1.value'                  => $settings['stats.1.value']              ?? '',
            'stats.1.label'                  => $settings['stats.1.label']              ?? '',
            'stats.2.value'                  => $settings['stats.2.value']              ?? '',
            'stats.2.label'                  => $settings['stats.2.label']              ?? '',
            'stats.3.value'                  => $settings['stats.3.value']              ?? '',
            'stats.3.label'                  => $settings['stats.3.label']              ?? '',
            'stats.4.value'                  => $settings['stats.4.value']              ?? '',
            'stats.4.label'                  => $settings['stats.4.label']              ?? '',

            // Homepage Sections
            'home.collections.eyebrow'       => $settings['home.collections.eyebrow']       ?? '',
            'home.collections.title'         => $settings['home.collections.title']         ?? '',
            'home.collections.card_cta'      => $settings['home.collections.card_cta']      ?? '',
            'home.products.eyebrow'          => $settings['home.products.eyebrow']          ?? '',
            'home.products.title'            => $settings['home.products.title']            ?? '',
            'home.products.view_all_label'   => $settings['home.products.view_all_label']   ?? '',
            'home.products.view_all_url'     => $settings['home.products.view_all_url']     ?? '',
            'home.products.card_cta'         => $settings['home.products.card_cta']         ?? '',
            'home.brands.eyebrow'            => $settings['home.brands.eyebrow']            ?? '',
            'home.brands.title'              => $settings['home.brands.title']              ?? '',
            'home.brands.view_all_label'     => $settings['home.brands.view_all_label']     ?? '',
            'home.brands.view_all_url'       => $settings['home.brands.view_all_url']       ?? '',
            'home.brands.card_cta'           => $settings['home.brands.card_cta']           ?? '',
            'home.brands.pieces_label'       => $settings['home.brands.pieces_label']       ?? '',
            'home.testimonials.eyebrow'      => $settings['home.testimonials.eyebrow']      ?? '',
            'home.testimonials.title'        => $settings['home.testimonials.title']        ?? '',

            // Homepage Story
            'home.story.image'               => !empty($settings['home.story.image']) ? asset('storage/' . $settings['home.story.image']) : null,
            'home.story.image_alt'           => $settings['home.story.image_alt']      ?? '',
            'home.story.eyebrow'             => $settings['home.story.eyebrow']         ?? '',
            'home.story.title1'              => $settings['home.story.title1']          ?? '',
            'home.story.title2'              => $settings['home.story.title2']          ?? '',
            'home.story.p1'                  => $settings['home.story.p1']              ?? '',
            'home.story.p2'                  => $settings['home.story.p2']              ?? '',
            'home.story.years'               => $settings['home.story.years']           ?? '',
            'home.story.years_label'         => $settings['home.story.years_label']     ?? '',
            'home.story.button_label'        => $settings['home.story.button_label']    ?? '',
            'home.story.button_url'          => $settings['home.story.button_url']      ?? '',

            // Footer
            'footer.tagline'                 => $settings['footer.tagline']             ?? ($locale === 'ar'
                ? 'منتجات جلدية فاخرة مصنوعة يدوياً. صُنعت في عُمان. تُوصَّل إلى دول الخليج.'
                : 'Premium handcrafted leather goods. Made in Oman. Delivered across the GCC.'),
            'footer.copyright'               => $settings['footer.copyright']           ?? ($locale === 'ar'
                ? '© 2025 آرتيزان ليذر · artisanleatherom.com · جميع الحقوق محفوظة'
                : '© 2025 Artisan Leather · artisanleatherom.com · All rights reserved'),

            // About — Hero
            'about.hero.eyebrow'             => $settings['about.hero.eyebrow']         ?? 'Muscat · Oman · Est. 2009',
            'about.hero.headline'            => $settings['about.hero.headline']        ?? 'A Story Written',
            'about.hero.headline_accent'     => $settings['about.hero.headline_accent'] ?? 'in Leather',
            'about.hero.subtitle'            => $settings['about.hero.subtitle']        ?? 'Sixteen years of craft. One unwavering standard.',

            // About — Story
            'about.story.image'              => !empty($settings['about.story.image']) ? asset('storage/' . $settings['about.story.image']) : null,
            'about.story.image_alt'          => $settings['about.story.image_alt']          ?? 'Artisan Leather craftsmanship and workshop detail',
            'about.story.eyebrow'            => $settings['about.story.eyebrow']            ?? 'Our Story',
            'about.story.headline'           => $settings['about.story.headline']           ?? 'Born from a Love',
            'about.story.headline_accent'    => $settings['about.story.headline_accent']    ?? 'of the Craft',
            'about.story.years'              => $settings['about.story.years']              ?? '16+',
            'about.story.years_label'        => $settings['about.story.years_label']        ?? 'Years of Craft',
            'about.story.p1'                 => $settings['about.story.p1']                 ?? 'Artisan Leather began not as a business plan, but as an obsession. Our founder spent years studying leatherwork — in Italy, in Morocco, and eventually in Oman — learning what makes a piece truly last.',
            'about.story.p2'                 => $settings['about.story.p2']                 ?? 'The first workshop was a single room in Muscat. Three craftsmen. One set of tools. No shortcuts. That ethos has never changed, even as the brand has grown across the GCC.',
            'about.story.p3'                 => $settings['about.story.p3']                 ?? 'Today, every piece that leaves our workshop is still inspected by hand, still stitched by hand, and still conditioned by hand — because the day we stop caring is the day we stop being Artisan Leather.',

            // About — Craft Steps
            'about.craft.section_eyebrow'    => $settings['about.craft.section_eyebrow'] ?? 'The Process',
            'about.craft.section_title'      => $settings['about.craft.section_title']   ?? 'The Art of Making',
            'about.craft.1.num'              => $settings['about.craft.1.num']   ?? '01',
            'about.craft.1.title'            => $settings['about.craft.1.title'] ?? 'Select the Hide',
            'about.craft.1.body'             => $settings['about.craft.1.body']  ?? 'Every hide is hand-inspected for natural grain, firmness, and character. Only the top 15% passes our standard — the rest is returned.',
            'about.craft.2.num'              => $settings['about.craft.2.num']   ?? '02',
            'about.craft.2.title'            => $settings['about.craft.2.title'] ?? 'Cut & Shape',
            'about.craft.2.body'             => $settings['about.craft.2.body']  ?? 'Each pattern is traced and cut by hand using solid steel templates. No laser cutters — only a steady hand and decades of muscle memory.',
            'about.craft.3.num'              => $settings['about.craft.3.num']   ?? '03',
            'about.craft.3.title'            => $settings['about.craft.3.title'] ?? 'Hand Stitch',
            'about.craft.3.body'             => $settings['about.craft.3.body']  ?? 'We use the saddle-stitch technique — two needles, one thread, pulled in opposite directions — creating a lock stitch that holds even if one side breaks.',
            'about.craft.4.num'              => $settings['about.craft.4.num']   ?? '04',
            'about.craft.4.title'            => $settings['about.craft.4.title'] ?? 'Finish & Age',
            'about.craft.4.body'             => $settings['about.craft.4.body']  ?? 'Edges are bevelled, burnished, and hand-painted. The piece is conditioned with natural beeswax and left to settle — becoming truly itself.',

            // About — Materials
            'about.material.section_eyebrow' => $settings['about.material.section_eyebrow'] ?? 'What We Use',
            'about.material.section_title'   => $settings['about.material.section_title']   ?? 'Only the Finest Materials',
            'about.material.1.name'          => $settings['about.material.1.name']     ?? 'Full Grain',
            'about.material.1.subtitle'      => $settings['about.material.1.subtitle'] ?? 'The Pinnacle of Leather',
            'about.material.1.desc'          => $settings['about.material.1.desc']     ?? 'The outermost layer of the hide — untouched by sanding or buffing. Full grain retains every natural mark, developing a rich unique patina over decades.',
            'about.material.1.image'         => !empty($settings['about.material.1.image']) ? asset('storage/' . $settings['about.material.1.image']) : null,
            'about.material.1.image_alt'     => $settings['about.material.1.image_alt'] ?? 'Full grain leather texture used by Artisan Leather Oman',
            'about.material.2.name'          => $settings['about.material.2.name']     ?? 'Vegetable Tanned',
            'about.material.2.subtitle'      => $settings['about.material.2.subtitle'] ?? 'Slow-Made & Sustainable',
            'about.material.2.desc'          => $settings['about.material.2.desc']     ?? 'Tanned using plant extracts — bark, leaves, roots — over 30–60 days. The result is leather with remarkable firmness that softens and deepens with age.',
            'about.material.2.image'         => !empty($settings['about.material.2.image']) ? asset('storage/' . $settings['about.material.2.image']) : null,
            'about.material.2.image_alt'     => $settings['about.material.2.image_alt'] ?? 'Vegetable tanned leather material for handcrafted leather goods',
            'about.material.3.name'          => $settings['about.material.3.name']     ?? 'Italian Calfskin',
            'about.material.3.subtitle'      => $settings['about.material.3.subtitle'] ?? 'Silken & Refined',
            'about.material.3.desc'          => $settings['about.material.3.desc']     ?? 'Sourced from the finest Italian tanneries. Calfskin offers an unmatched surface — fine-grained, almost silk-like, ideal for slim wallets and dress pieces.',
            'about.material.3.image'         => !empty($settings['about.material.3.image']) ? asset('storage/' . $settings['about.material.3.image']) : null,
            'about.material.3.image_alt'     => $settings['about.material.3.image_alt'] ?? 'Italian calfskin leather material used in Artisan Leather products',

            // About — Values
            'about.value.section_eyebrow'    => $settings['about.value.section_eyebrow'] ?? 'What We Stand For',
            'about.value.section_title'      => $settings['about.value.section_title']   ?? 'Our Four Pillars',
            'about.value.1.number'           => $settings['about.value.1.number'] ?? 'I',
            'about.value.1.title'            => $settings['about.value.1.title']  ?? 'Heritage',
            'about.value.1.desc'             => $settings['about.value.1.desc']   ?? 'Rooted in centuries of leather tradition. Every technique we use can be traced back further than any trend.',
            'about.value.2.number'           => $settings['about.value.2.number'] ?? 'II',
            'about.value.2.title'            => $settings['about.value.2.title']  ?? 'Precision',
            'about.value.2.desc'             => $settings['about.value.2.desc']   ?? 'Every millimeter is intentional. Every edge, stitch, and finish is measured and placed with care.',
            'about.value.3.number'           => $settings['about.value.3.number'] ?? 'III',
            'about.value.3.title'            => $settings['about.value.3.title']  ?? 'Longevity',
            'about.value.3.desc'             => $settings['about.value.3.desc']   ?? 'We do not design for seasons. We design for decades. Our pieces are made to outlast the person who owns them first.',
            'about.value.4.number'           => $settings['about.value.4.number'] ?? 'IV',
            'about.value.4.title'            => $settings['about.value.4.title']  ?? 'Authenticity',
            'about.value.4.desc'             => $settings['about.value.4.desc']   ?? 'No shortcuts. No synthetic blends. No compromise. What you hold is exactly what it claims to be.',

            // About — Timeline
            'about.timeline.section_eyebrow' => $settings['about.timeline.section_eyebrow'] ?? 'Our Journey',
            'about.timeline.section_title'   => $settings['about.timeline.section_title']   ?? 'Sixteen Years in the Making',
            'about.timeline.1.year'          => $settings['about.timeline.1.year']  ?? '2009',
            'about.timeline.1.title'         => $settings['about.timeline.1.title'] ?? 'First Workshop',
            'about.timeline.1.desc'          => $settings['about.timeline.1.desc']  ?? 'A small atelier opened in the heart of Muscat. Three craftsmen. One mission.',
            'about.timeline.2.year'          => $settings['about.timeline.2.year']  ?? '2013',
            'about.timeline.2.title'         => $settings['about.timeline.2.title'] ?? 'First Collection',
            'about.timeline.2.desc'          => $settings['about.timeline.2.desc']  ?? 'The Heritage Collection — six wallets and two belts — sold out in three weeks.',
            'about.timeline.3.year'          => $settings['about.timeline.3.year']  ?? '2018',
            'about.timeline.3.title'         => $settings['about.timeline.3.title'] ?? 'GCC Expansion',
            'about.timeline.3.desc'          => $settings['about.timeline.3.desc']  ?? 'Artisan Leather pieces reached Dubai, Riyadh, and Kuwait through word of mouth alone.',
            'about.timeline.4.year'          => $settings['about.timeline.4.year']  ?? '2023',
            'about.timeline.4.title'         => $settings['about.timeline.4.title'] ?? 'Flagship Identity',
            'about.timeline.4.desc'          => $settings['about.timeline.4.desc']  ?? 'The gold-and-black mark became recognised across the Gulf.',
            'about.timeline.5.year'          => $settings['about.timeline.5.year']  ?? '2025',
            'about.timeline.5.title'         => $settings['about.timeline.5.title'] ?? 'Online Launch',
            'about.timeline.5.desc'          => $settings['about.timeline.5.desc']  ?? 'Bringing our full collection online — crafted in Oman, delivered to the world.',

            // About — CTA
            'about.cta.eyebrow'              => $settings['about.cta.eyebrow'] ?? 'Start Your Journey',
            'about.cta.heading'              => $settings['about.cta.heading'] ?? 'Own a Piece of the Craft',
            'about.cta.text'                 => $settings['about.cta.text']    ?? 'Every wallet, bag, and belt we make is a promise — that the hands behind it cared as much as the hands that will carry it.',
            'about.cta.shop_label'           => $settings['about.cta.shop_label']    ?? 'Shop Collection',
            'about.cta.shop_url'             => $settings['about.cta.shop_url']      ?? '/collections',
            'about.cta.contact_label'        => $settings['about.cta.contact_label'] ?? 'Get in Touch',
            'about.cta.contact_url'          => $settings['about.cta.contact_url']   ?? '/contact',

            // About — SEO
            'about.seo.meta_title'           => $settings['about.seo.meta_title']       ?? '',
            'about.seo.meta_description'     => $settings['about.seo.meta_description'] ?? '',

            // Homepage — SEO
            'homepage.seo.meta_title'        => $settings['homepage.seo.meta_title']       ?? '',
            'homepage.seo.meta_description'  => $settings['homepage.seo.meta_description'] ?? '',
        ];

        // Expose Arabic (_ar) counterparts for any whitelisted key that has one saved
        foreach ($safe as $key => $value) {
            $arKey = $key . '_ar';
            if (array_key_exists($arKey, $settings)) {
                $safe[$arKey] = $settings[$arKey];
            }
        }

        return response()->json(['data' => $safe]);
    }
}
