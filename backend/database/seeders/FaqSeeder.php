<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        if (Faq::count() > 0) {
            return;
        }

        $faqs = [
            [
                'question' => 'Do you ship internationally?',
                'question_ar' => 'هل تشحنون دولياً؟',
                'answer' => 'Yes. We deliver across all GCC countries — Oman, UAE, Saudi Arabia, Kuwait, Bahrain, and Qatar — with complimentary shipping on every order. International shipping beyond the GCC is available on request.',
                'answer_ar' => 'نعم. نوصل إلى جميع دول مجلس التعاون الخليجي — عُمان، الإمارات، السعودية، الكويت، البحرين، وقطر — مع شحن مجاني لكل طلب. الشحن الدولي خارج دول الخليج متاح عند الطلب.',
                'sort_order' => 1,
            ],
            [
                'question' => 'Can I customise or monogram my order?',
                'question_ar' => 'هل يمكنني تخصيص طلبي أو إضافة نقش؟',
                'answer' => 'Absolutely. We offer debossed monogramming on most pieces (initials or a short name). Custom colour and stitching options are also available for orders above OMR 100. Contact us via WhatsApp to discuss.',
                'answer_ar' => 'بالتأكيد. نقدم نقشاً غائراً على معظم القطع (أحرف أولى أو اسم قصير). كما تتوفر خيارات لون وخياطة مخصصة للطلبات التي تزيد عن 100 ريال عُماني. تواصل معنا عبر واتساب للمناقشة.',
                'sort_order' => 2,
            ],
            [
                'question' => 'How long does delivery take?',
                'question_ar' => 'كم تستغرق مدة التوصيل؟',
                'answer' => 'Standard orders within Oman: 3–5 business days. GCC delivery: 5–7 business days. All orders are gift-wrapped in our signature black box at no extra charge.',
                'answer_ar' => 'الطلبات داخل عُمان: 3-5 أيام عمل. التوصيل لدول الخليج: 5-7 أيام عمل. تُغلَّف جميع الطلبات في صندوقنا الأسود المميز دون أي رسوم إضافية.',
                'sort_order' => 3,
            ],
            [
                'question' => 'What is your return and exchange policy?',
                'question_ar' => 'ما هي سياسة الإرجاع والاستبدال؟',
                'answer' => 'We accept returns within 14 days of delivery for unused items in their original packaging. Monogrammed or custom pieces are non-refundable. Exchanges are always welcome.',
                'answer_ar' => 'نقبل الإرجاع خلال 14 يوماً من التسليم للقطع غير المستخدمة وبتغليفها الأصلي. القطع المنقوشة أو المخصصة غير قابلة للاسترداد. الاستبدال متاح دائماً.',
                'sort_order' => 4,
            ],
            [
                'question' => 'How do I care for my leather piece?',
                'question_ar' => 'كيف أعتني بقطعتي الجلدية؟',
                'answer' => 'Full grain and vegetable-tanned leather thrive with minimal intervention. Wipe clean with a dry cloth and apply a quality beeswax conditioner every 6–12 months. Avoid prolonged exposure to sunlight or water.',
                'answer_ar' => 'الجلد الطبيعي الكامل والمدبوغ نباتياً يحافظ على جودته بأقل تدخل. امسحه بقطعة قماش جافة وضع بلسم شمع العسل كل 6-12 شهراً. تجنب التعرض الطويل لأشعة الشمس أو الماء.',
                'sort_order' => 5,
            ],
            [
                'question' => 'Are your leathers ethically sourced?',
                'question_ar' => 'هل مصادر الجلود لديكم أخلاقية؟',
                'answer' => 'Yes. We source from tanneries that meet European ethical and environmental standards. Our vegetable-tanned leathers use no harmful chemicals — only natural plant-based tanning agents.',
                'answer_ar' => 'نعم. نستورد من مدابغ تلتزم بالمعايير الأخلاقية والبيئية الأوروبية. الجلود المدبوغة نباتياً لدينا لا تستخدم أي مواد كيميائية ضارة — فقط مواد دباغة طبيعية مستخلصة من النباتات.',
                'sort_order' => 6,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}
