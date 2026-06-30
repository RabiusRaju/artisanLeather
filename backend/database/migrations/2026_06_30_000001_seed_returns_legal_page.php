<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('legal_pages')->insert([
            'slug'         => 'returns',
            'title'        => 'Returns & Exchanges',
            'title_ar'     => 'الإرجاع والاستبدال',
            'last_updated' => 'June 2026',
            'sections'     => json_encode([
                [
                    'heading'    => 'Our Return Policy',
                    'heading_ar' => 'سياسة الإرجاع',
                    'body'       => "We want you to be completely satisfied with your purchase. If for any reason you are not happy, we accept returns within 7 days of the delivery date.\n\nTo be eligible for a return, items must be:\n- Unused, unworn, and unaltered\n- In their original condition and packaging\n- Accompanied by your order confirmation or order number",
                    'body_ar'    => '',
                ],
                [
                    'heading'    => 'How to Initiate a Return',
                    'heading_ar' => 'كيفية بدء طلب الإرجاع',
                    'body'       => "To start a return or exchange, please contact us within 7 days of receiving your order:\n\n1. Reach us via WhatsApp or email with your order number and the reason for the return.\n2. Our team will respond within 24 hours to confirm eligibility and arrange collection or drop-off.\n3. Once we receive and inspect the item, we will process your refund or exchange promptly.",
                    'body_ar'    => '',
                ],
                [
                    'heading'    => 'Exchanges',
                    'heading_ar' => 'الاستبدال',
                    'body'       => "We are happy to exchange an item for a different colour or style of equal or lesser value. If the replacement item is of higher value, the difference will be charged at the time of exchange.\n\nFor exchanges due to a manufacturing defect or an error on our part, we will cover all associated shipping or collection costs.",
                    'body_ar'    => '',
                ],
                [
                    'heading'    => 'Non-Returnable Items',
                    'heading_ar' => 'المنتجات غير القابلة للإرجاع',
                    'body'       => "The following items cannot be returned or exchanged:\n- Personalised or custom-engraved items\n- Items purchased on sale or clearance\n- Items that show signs of use, wear, alteration, or damage caused after delivery\n- Gift cards",
                    'body_ar'    => '',
                ],
                [
                    'heading'    => 'Damaged or Defective Items',
                    'heading_ar' => 'المنتجات التالفة أو المعيبة',
                    'body'       => "If your item arrives damaged or has a manufacturing defect, please contact us within 24 hours of receipt with clear photographs of the issue. We will arrange a replacement or full refund at no cost to you, including return shipping or collection.",
                    'body_ar'    => '',
                ],
                [
                    'heading'    => 'Refunds',
                    'heading_ar' => 'المبالغ المستردة',
                    'body'       => "Once your return is received and inspected, we will notify you of the approval or rejection of your refund.\n\n- Approved refunds are processed within 5–7 business days.\n- Refunds are issued to the original payment method (card, bank transfer, or wallet as applicable).\n- Original shipping fees are non-refundable unless the return is due to our error or a manufacturing defect.\n- Return shipping costs are the customer's responsibility unless otherwise agreed.",
                    'body_ar'    => '',
                ],
            ]),
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('legal_pages')->where('slug', 'returns')->delete();
    }
};
