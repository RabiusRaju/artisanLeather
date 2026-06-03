<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $b = 'https://images.unsplash.com/photo-';
        $q = '?w=1200&q=80&fit=crop';

        // ── Define 4 collections ──────────────────────────────────────────────
        $brands = [
            [
                'name'        => 'Heritage Collection',
                'name_ar'     => 'مجموعة التراث',
                'slug'        => 'heritage-collection',
                'tagline'     => 'Timeless craftsmanship, rooted in tradition.',
                'tagline_ar'  => 'حرفية خالدة، متجذرة في الموروث.',
                'description' => 'The Heritage Collection honours centuries of leather craft. Each piece is cut, stitched and finished entirely by hand using techniques passed down through generations. If you value things that improve with age, this is your collection.',
                'description_ar' => 'تُجسّد مجموعة التراث قروناً من صناعة الجلود الأصيلة. كل قطعة مقطوعة ومخيطة ومصقولة يدوياً باستخدام تقنيات توارثناها عبر الأجيال. إن كنت ممن يُقدّرون الأشياء التي تزداد جمالاً مع الوقت، فهذه مجموعتك.',
                'logo'        => null,
                'banner'      => $b . '1627123424574-724758594e93' . $q,
                'is_active'   => true,
                'is_featured' => true,
                'sort_order'  => 1,
                // Products: Heritage Bifold, Passport Folio, Classic Belt No.1, Heritage Key Fob
                'product_slugs' => ['heritage-bifold', 'passport-folio', 'classic-belt-no-1', 'heritage-key-fob'],
            ],
            [
                'name'        => 'Executive Line',
                'name_ar'     => 'الخط التنفيذي',
                'slug'        => 'executive-line',
                'tagline'     => 'Engineered for those who lead.',
                'tagline_ar'  => 'مصمم لمن يقودون.',
                'description' => 'The Executive Line is built for the boardroom and beyond. Structured silhouettes, full-grain leather, solid brass hardware — every detail communicates authority. Carry less, achieve more.',
                'description_ar' => 'الخط التنفيذي مصمم لقاعة الاجتماعات وما هو أبعد. تصاميم هيكلية وجلد كامل الحبوب وتجهيزات نحاسية صلبة — كل تفصيل يُجسّد الحضور والسلطة.',
                'logo'        => null,
                'banner'      => $b . '1598532163257-ae3c6b2524b6' . $q,
                'is_active'   => true,
                'is_featured' => true,
                'sort_order'  => 2,
                // Products: Executive Tote, Zip-Around Wallet, Cardslim Pro, Dress Belt
                'product_slugs' => ['executive-tote', 'zip-around-wallet', 'cardslim-pro', 'dress-belt'],
            ],
            [
                'name'        => 'Travel Series',
                'name_ar'     => 'سلسلة المسافر',
                'slug'        => 'travel-series',
                'tagline'     => 'Crafted for the journey, not the destination.',
                'tagline_ar'  => 'صُنعت للرحلة، لا للوجهة.',
                'description' => 'The Travel Series is built for those who move between cities without losing an ounce of elegance. Heavy-duty construction, thoughtful organisation, leather that only looks better after ten thousand miles.',
                'description_ar' => 'سلسلة المسافر مصنوعة لمن يتنقلون بين المدن دون أن يتنازلوا عن أناقتهم. بناء متين وتنظيم مدروس وجلد يزداد جمالاً مع كل رحلة.',
                'logo'        => null,
                'banner'      => $b . '1705909237050-7a7625b47fac' . $q,
                'is_active'   => true,
                'is_featured' => true,
                'sort_order'  => 3,
                // Products: Weekender Duffel, Slim Messenger, Reversible Belt
                'product_slugs' => ['weekender-duffel', 'slim-messenger', 'reversible-belt'],
            ],
            [
                'name'        => 'Signature Edition',
                'name_ar'     => 'الإصدار المميز',
                'slug'        => 'signature-edition',
                'tagline'     => 'Minimal form. Maximum statement.',
                'tagline_ar'  => 'شكل بسيط. تأثير أقصى.',
                'description' => 'The Signature Edition is for those who believe the finest details make the loudest statement. Ultra-slim profiles, clean lines and premium calfskin — the art of restraint, perfected.',
                'description_ar' => 'الإصدار المميز لمن يؤمنون بأن أدق التفاصيل تصنع أبلغ العبارات. أشكال نحيلة وخطوط نقية وجلد عجل فاخر — فن الاتزان في أبهى صوره.',
                'logo'        => null,
                'banner'      => $b . '1620109176813-e91290f6c795' . $q,
                'is_active'   => true,
                'is_featured' => false,
                'sort_order'  => 4,
                // Products: Slim Card Case
                'product_slugs' => ['slim-card-case'],
            ],
        ];

        foreach ($brands as $data) {
            $slugs = $data['product_slugs'];
            unset($data['product_slugs']);

            $brand = Brand::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            // Assign products to this brand
            Product::whereIn('slug', $slugs)->update(['brand_id' => $brand->id]);

            $this->command->info("✓ {$brand->name} — " . count($slugs) . ' products assigned');
        }

        $this->command->info('');
        $this->command->info('Brand assignment summary:');
        Product::with('brand')->get()->each(function ($p) {
            $this->command->line("  {$p->name}  →  " . ($p->brand?->name ?? 'No brand'));
        });
    }
}
