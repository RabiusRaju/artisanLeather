<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Seeds bilingual (English + Arabic) testimonials so they are editable
     * from the Filament back office (Testimonials resource).
     */
    public function run(): void
    {
        if (Testimonial::count() > 0) {
            return;
        }

        $testimonials = [
            [
                'quote' => 'The most exquisite wallet I have ever owned. The leather is buttery smooth and the craftsmanship is simply unmatched. Worth every Baisa.',
                'quote_ar' => 'إنها أروع محفظة امتلكتها على الإطلاق. الجلد ناعم كالحرير والحرفية لا مثيل لها. تستحق كل بيسة.',
                'author' => 'Mohammed Al Rashidi',
                'author_ar' => 'محمد الراشدي',
                'location' => 'Muscat, Oman',
                'location_ar' => 'مسقط، عُمان',
                'product' => 'Heritage Bifold Wallet',
                'product_ar' => 'محفظة هيريتدج القابلة للطي',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'quote' => 'I gifted an Artisan Leather bag to my wife for our anniversary. She was speechless. The quality speaks before the price.',
                'quote_ar' => 'أهديت زوجتي حقيبة من آرتيزان ليذر بمناسبة ذكرى زواجنا، فأُصيبت بالذهول. الجودة تتحدث عن نفسها قبل السعر.',
                'author' => 'Khalid Al Harthi',
                'author_ar' => 'خالد الحارثي',
                'location' => 'Dubai, UAE',
                'location_ar' => 'دبي، الإمارات',
                'product' => 'Signature Tote Bag',
                'product_ar' => 'حقيبة توتس سيجنتشر',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'quote' => 'These are not just leather goods — they are heirlooms in the making. I have had my belt for three years and it only looks better with age.',
                'quote_ar' => 'هذه ليست مجرد منتجات جلدية، بل قطع أثرية في طور التكوين. أمتلك حزامي منذ ثلاث سنوات وهو يزداد جمالاً مع الوقت.',
                'author' => 'Salim Al Balushi',
                'author_ar' => 'سالم البلوشي',
                'location' => 'Salalah, Oman',
                'location_ar' => 'صلالة، عُمان',
                'product' => 'Classic Leather Belt',
                'product_ar' => 'حزام جلدي كلاسيكي',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::create($testimonial);
        }
    }
}
