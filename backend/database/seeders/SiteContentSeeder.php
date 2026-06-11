<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SiteContentSeeder extends Seeder
{
    /**
     * Seeds bilingual (English + Arabic) homepage, about page and footer content
     * into the settings table so it is editable from the Filament back office
     * (Homepage / About Page / Business Settings) instead of being hardcoded.
     */
    public function run(): void
    {
        $values = [
            // Hero
            'hero.eyebrow'                => 'Muscat · Sultanate of Oman',
            'hero.eyebrow_ar'             => 'مسقط · سلطنة عُمان',
            'hero.headline'               => 'Where Leather',
            'hero.headline_ar'            => 'حيث يصبح الجلد',
            'hero.headline_accent'        => 'Becomes Legacy',
            'hero.headline_accent_ar'     => 'إرثاً خالداً',
            'hero.subtitle'               => 'Handcrafted premium leather goods for those who appreciate the art of timeless elegance.',
            'hero.subtitle_ar'            => 'منتجات جلدية فاخرة مصنوعة يدوياً لمن يُقدّر فن الأناقة الخالدة.',
            'hero.cta_primary'            => 'Explore Collection',
            'hero.cta_primary_ar'         => 'استعرض المجموعة',
            'hero.cta_secondary'          => 'Our Story',
            'hero.cta_secondary_ar'       => 'قصتنا',

            // Stats Bar
            'stats.1.value'               => '100%',
            'stats.1.label'               => 'Handcrafted',
            'stats.1.label_ar'            => 'صناعة يدوية',
            'stats.2.value'               => '15+',
            'stats.2.label'               => 'Years of Excellence',
            'stats.2.label_ar'            => 'سنوات من التميز',
            'stats.3.value'               => '50+',
            'stats.3.label'               => 'Unique Designs',
            'stats.3.label_ar'            => 'تصميم فريد',
            'stats.4.value'               => 'GCC',
            'stats.4.label'               => 'Wide Delivery',
            'stats.4.label_ar'            => 'توصيل واسع',

            // Footer
            'footer.tagline'              => 'Premium handcrafted leather goods. Made in Oman. Delivered across the GCC.',
            'footer.tagline_ar'           => 'منتجات جلدية فاخرة مصنوعة يدوياً. صُنعت في عُمان. تُوصَّل إلى دول الخليج.',
            'footer.copyright'            => '© 2025 Artisan Leather · artisanleatherom.com · All rights reserved',
            'footer.copyright_ar'         => '© 2025 آرتيزان ليذر · artisanleatherom.com · جميع الحقوق محفوظة',

            // About — Hero
            'about.hero.eyebrow'          => 'Muscat · Oman · Est. 2009',
            'about.hero.eyebrow_ar'       => 'مسقط · عُمان · تأسست 2009',
            'about.hero.headline'         => 'A Story Written',
            'about.hero.headline_ar'      => 'قصة مكتوبة',
            'about.hero.headline_accent'  => 'in Leather',
            'about.hero.headline_accent_ar' => 'بالجلد الأصيل',
            'about.hero.subtitle'         => 'Sixteen years of craft. One unwavering standard.',
            'about.hero.subtitle_ar'      => 'ستة عشر عاماً من الحرفية. معيار واحد لا يتزعزع.',

            // About — Story
            'about.story.headline'        => 'Born from a Love',
            'about.story.headline_ar'     => 'وُلدت من شغف',
            'about.story.headline_accent' => 'of the Craft',
            'about.story.headline_accent_ar' => 'بالحرفة',
            'about.story.years'           => '16+',
            'about.story.p1'              => 'Artisan Leather began not as a business plan, but as an obsession. Our founder spent years studying leatherwork — in Italy, in Morocco, and eventually in Oman — learning what makes a piece truly last.',
            'about.story.p1_ar'           => 'لم تبدأ آرتيزان ليذر كخطة عمل، بل كشغف. أمضى مؤسسنا سنوات في دراسة صناعة الجلود — في إيطاليا والمغرب، وأخيراً في عُمان — متعلماً ما الذي يجعل القطعة تدوم حقاً.',
            'about.story.p2'              => 'The first workshop was a single room in Muscat. Three craftsmen. One set of tools. No shortcuts. That ethos has never changed, even as the brand has grown across the GCC.',
            'about.story.p2_ar'           => 'كانت الورشة الأولى عبارة عن غرفة واحدة في مسقط. ثلاثة حرفيين. مجموعة أدوات واحدة. بلا اختصارات. لم تتغير هذه الروح أبداً، حتى مع نمو العلامة التجارية في جميع أنحاء الخليج.',
            'about.story.p3'              => 'Today, every piece that leaves our workshop is still inspected by hand, still stitched by hand, and still conditioned by hand — because the day we stop caring is the day we stop being Artisan Leather.',
            'about.story.p3_ar'           => 'اليوم، كل قطعة تغادر ورشتنا لا تزال تُفحص يدوياً، وتُخاط يدوياً، وتُعالج يدوياً — لأن اليوم الذي نتوقف فيه عن الاهتمام هو اليوم الذي نتوقف فيه عن كوننا آرتيزان ليذر.',

            // About — Craft Steps
            'about.craft.1.num'           => '01',
            'about.craft.1.title'         => 'Select the Hide',
            'about.craft.1.title_ar'      => 'اختيار الجلد',
            'about.craft.1.body'          => 'Every hide is hand-inspected for natural grain, firmness, and character. Only the top 15% passes our standard — the rest is returned.',
            'about.craft.1.body_ar'       => 'يتم فحص كل جلد يدوياً للتأكد من نسيجه الطبيعي وصلابته وشخصيته. فقط أفضل 15% يجتاز معاييرنا — والباقي يُعاد.',
            'about.craft.2.num'           => '02',
            'about.craft.2.title'         => 'Cut & Shape',
            'about.craft.2.title_ar'      => 'القص والتشكيل',
            'about.craft.2.body'          => 'Each pattern is traced and cut by hand using solid steel templates. No laser cutters — only a steady hand and decades of muscle memory.',
            'about.craft.2.body_ar'       => 'يتم رسم وقص كل نمط يدوياً باستخدام قوالب من الفولاذ الصلب. لا قواطع ليزر — فقط يد ثابتة وعقود من الخبرة.',
            'about.craft.3.num'           => '03',
            'about.craft.3.title'         => 'Hand Stitch',
            'about.craft.3.title_ar'      => 'الخياطة اليدوية',
            'about.craft.3.body'          => 'We use the saddle-stitch technique — two needles, one thread, pulled in opposite directions — creating a lock stitch that holds even if one side breaks.',
            'about.craft.3.body_ar'       => 'نستخدم تقنية الخياطة السرجية — إبرتان وخيط واحد يُسحبان في اتجاهين متعاكسين — مما ينتج غرزة قفل تصمد حتى لو انقطع أحد الجانبين.',
            'about.craft.4.num'           => '04',
            'about.craft.4.title'         => 'Finish & Age',
            'about.craft.4.title_ar'      => 'التشطيب والتعتيق',
            'about.craft.4.body'          => 'Edges are bevelled, burnished, and hand-painted. The piece is conditioned with natural beeswax and left to settle — becoming truly itself.',
            'about.craft.4.body_ar'       => 'تُشطّب الحواف وتُصقل وتُطلى يدوياً. تُعالج القطعة بشمع العسل الطبيعي وتُترك لتستقر — لتصبح على طبيعتها الحقيقية.',

            // About — Materials
            'about.material.1.name'       => 'Full Grain',
            'about.material.1.name_ar'    => 'الجلد الكامل (فُل جرين)',
            'about.material.1.subtitle'   => 'The Pinnacle of Leather',
            'about.material.1.subtitle_ar' => 'قمة فخامة الجلود',
            'about.material.1.desc'       => 'The outermost layer of the hide — untouched by sanding or buffing. Full grain retains every natural mark, developing a rich unique patina over decades.',
            'about.material.1.desc_ar'    => 'الطبقة الخارجية من الجلد — دون أي صنفرة أو معالجة سطحية. يحتفظ الفل جرين بكل علاماته الطبيعية، ويكتسب طابعاً فريداً مع مرور العقود.',
            'about.material.2.name'       => 'Vegetable Tanned',
            'about.material.2.name_ar'    => 'الدباغة النباتية',
            'about.material.2.subtitle'   => 'Slow-Made & Sustainable',
            'about.material.2.subtitle_ar' => 'صناعة بطيئة ومستدامة',
            'about.material.2.desc'       => 'Tanned using plant extracts — bark, leaves, roots — over 30–60 days. The result is leather with remarkable firmness that softens and deepens with age.',
            'about.material.2.desc_ar'    => 'تُدبغ باستخدام مستخلصات نباتية — اللحاء والأوراق والجذور — على مدى 30-60 يوماً. والنتيجة جلد متين بشكل ملحوظ يزداد نعومة وعمقاً مع مرور الزمن.',
            'about.material.3.name'       => 'Italian Calfskin',
            'about.material.3.name_ar'    => 'جلد العجل الإيطالي',
            'about.material.3.subtitle'   => 'Silken & Refined',
            'about.material.3.subtitle_ar' => 'حريري ورفيع',
            'about.material.3.desc'       => 'Sourced from the finest Italian tanneries. Calfskin offers an unmatched surface — fine-grained, almost silk-like, ideal for slim wallets and dress pieces.',
            'about.material.3.desc_ar'    => 'مستورد من أرقى المدابغ الإيطالية. يتميز جلد العجل بسطح لا مثيل له — ناعم الحبيبات يكاد يكون حريرياً، مثالي للمحافظ النحيفة والقطع الرسمية.',

            // About — Values
            'about.value.1.number'        => 'I',
            'about.value.1.title'         => 'Heritage',
            'about.value.1.title_ar'      => 'الإرث',
            'about.value.1.desc'          => 'Rooted in centuries of leather tradition. Every technique we use can be traced back further than any trend.',
            'about.value.1.desc_ar'       => 'متجذر في قرون من تقاليد صناعة الجلود. كل تقنية نستخدمها تعود جذورها إلى ما هو أبعد من أي موضة عابرة.',
            'about.value.2.number'        => 'II',
            'about.value.2.title'         => 'Precision',
            'about.value.2.title_ar'      => 'الدقة',
            'about.value.2.desc'          => 'Every millimeter is intentional. Every edge, stitch, and finish is measured and placed with care.',
            'about.value.2.desc_ar'       => 'كل مليمتر مقصود. كل حافة وغرزة ولمسة نهائية تُقاس وتُوضع بعناية.',
            'about.value.3.number'        => 'III',
            'about.value.3.title'         => 'Longevity',
            'about.value.3.title_ar'      => 'الديمومة',
            'about.value.3.desc'          => 'We do not design for seasons. We design for decades. Our pieces are made to outlast the person who owns them first.',
            'about.value.3.desc_ar'       => 'نحن لا نصمم للمواسم، بل للعقود. قطعنا مصممة لتدوم أطول من مالكها الأول.',
            'about.value.4.number'        => 'IV',
            'about.value.4.title'         => 'Authenticity',
            'about.value.4.title_ar'      => 'الأصالة',
            'about.value.4.desc'          => 'No shortcuts. No synthetic blends. No compromise. What you hold is exactly what it claims to be.',
            'about.value.4.desc_ar'       => 'بلا اختصارات. بلا خلطات صناعية. بلا تنازلات. ما تحمله هو بالضبط ما يُفترض أن يكون.',

            // About — Timeline
            'about.timeline.1.year'       => '2009',
            'about.timeline.1.title'      => 'First Workshop',
            'about.timeline.1.title_ar'   => 'الورشة الأولى',
            'about.timeline.1.desc'       => 'A small atelier opened in the heart of Muscat. Three craftsmen. One mission.',
            'about.timeline.1.desc_ar'    => 'افتُتح مرسم صغير في قلب مسقط. ثلاثة حرفيين. مهمة واحدة.',
            'about.timeline.2.year'       => '2013',
            'about.timeline.2.title'      => 'First Collection',
            'about.timeline.2.title_ar'   => 'المجموعة الأولى',
            'about.timeline.2.desc'       => 'The Heritage Collection — six wallets and two belts — sold out in three weeks.',
            'about.timeline.2.desc_ar'    => 'مجموعة "هيريتدج" — ست محافظ وحزامان — نفدت في غضون ثلاثة أسابيع.',
            'about.timeline.3.year'       => '2018',
            'about.timeline.3.title'      => 'GCC Expansion',
            'about.timeline.3.title_ar'   => 'التوسع الخليجي',
            'about.timeline.3.desc'       => 'Artisan Leather pieces reached Dubai, Riyadh, and Kuwait through word of mouth alone.',
            'about.timeline.3.desc_ar'    => 'وصلت منتجات آرتيزان ليذر إلى دبي والرياض والكويت عبر التوصية الشفهية فقط.',
            'about.timeline.4.year'       => '2023',
            'about.timeline.4.title'      => 'Flagship Identity',
            'about.timeline.4.title_ar'   => 'الهوية الرئيسية',
            'about.timeline.4.desc'       => 'The gold-and-black mark became recognised across the Gulf.',
            'about.timeline.4.desc_ar'    => 'أصبح الشعار الذهبي والأسود معروفاً في جميع أنحاء الخليج.',
            'about.timeline.5.year'       => '2025',
            'about.timeline.5.title'      => 'Online Launch',
            'about.timeline.5.title_ar'   => 'الإطلاق الإلكتروني',
            'about.timeline.5.desc'       => 'Bringing our full collection online — crafted in Oman, delivered to the world.',
            'about.timeline.5.desc_ar'    => 'إطلاق مجموعتنا الكاملة عبر الإنترنت — صُنعت في عُمان، وتُوصَّل إلى العالم.',

            // About — CTA
            'about.cta.heading'           => 'Own a Piece of the Craft',
            'about.cta.heading_ar'        => 'امتلك قطعة من هذا الفن',
            'about.cta.text'              => 'Every wallet, bag, and belt we make is a promise — that the hands behind it cared as much as the hands that will carry it.',
            'about.cta.text_ar'           => 'كل محفظة وحقيبة وحزام نصنعه هو وعد — بأن الأيدي التي صنعته اهتمت بقدر اهتمام الأيدي التي ستحمله.',
        ];

        foreach ($values as $key => $value) {
            // Don't overwrite content an admin has already customised
            if (Setting::where('key', $key)->exists()) {
                continue;
            }
            Setting::set($key, $value);
        }

        Cache::flush();
    }
}
