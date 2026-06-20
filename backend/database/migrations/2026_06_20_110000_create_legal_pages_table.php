<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('last_updated')->nullable();
            $table->json('sections')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('legal_pages')->insert([
            [
                'slug'         => 'privacy',
                'title'        => 'Privacy Policy',
                'title_ar'     => 'سياسة الخصوصية',
                'last_updated' => 'June 2026',
                'sections'     => json_encode([
                    [
                        'heading' => 'Introduction',
                        'heading_ar' => '',
                        'body' => "Artisan Leather (\"we\", \"us\", \"our\") respects your privacy and is committed to protecting the personal information you share with us when you visit our website or place an order. This policy explains what information we collect, how we use it, and the choices you have.",
                        'body_ar' => '',
                    ],
                    [
                        'heading' => 'Information We Collect',
                        'heading_ar' => '',
                        'body' => "When you browse our site, create an account, or place an order, we may collect:\n- Name, email address, phone number, and delivery address\n- Order history and items added to your cart or wishlist\n- Payment confirmation details (we do not store full card numbers)\n- Device, browser, and usage information collected via cookies and analytics tools",
                        'body_ar' => '',
                    ],
                    [
                        'heading' => 'How We Use Your Information',
                        'heading_ar' => '',
                        'body' => "We use the information we collect to:\n- Process and deliver your orders, including coordination with delivery partners\n- Communicate with you about your orders, account, or enquiries\n- Improve our products, website, and customer experience\n- Send promotional offers, where you have opted in to receive them\n- Comply with legal and regulatory obligations",
                        'body_ar' => '',
                    ],
                    [
                        'heading' => 'Cookies',
                        'heading_ar' => '',
                        'body' => "We use cookies and similar technologies to keep you signed in, remember your cart and wishlist, remember your language and currency preferences, and understand how visitors use our site. You can disable cookies in your browser settings, though some features of the site may not work correctly as a result.",
                        'body_ar' => '',
                    ],
                    [
                        'heading' => 'Sharing Your Information',
                        'heading_ar' => '',
                        'body' => "We do not sell your personal information. We may share it with trusted third parties who help us operate our business, such as payment processors, delivery and courier partners, and IT service providers — solely for the purpose of fulfilling your order and operating our website. These partners are required to keep your information secure and use it only for the services they provide to us.",
                        'body_ar' => '',
                    ],
                    [
                        'heading' => 'Data Retention',
                        'heading_ar' => '',
                        'body' => "We retain your personal information for as long as necessary to fulfil the purposes described in this policy, including any legal, accounting, or reporting requirements.",
                        'body_ar' => '',
                    ],
                    [
                        'heading' => 'Your Rights',
                        'heading_ar' => '',
                        'body' => "You may request access to, correction of, or deletion of your personal information, and you may opt out of marketing communications at any time. To exercise these rights, please contact us using the details below.",
                        'body_ar' => '',
                    ],
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug'         => 'terms',
                'title'        => 'Terms of Service',
                'title_ar'     => 'شروط الخدمة',
                'last_updated' => 'June 2026',
                'sections'     => json_encode([
                    [
                        'heading' => 'Agreement to Terms',
                        'heading_ar' => '',
                        'body' => "By accessing or using the Artisan Leather website and placing an order, you agree to be bound by these Terms of Service. If you do not agree with any part of these terms, please do not use our website or services.",
                        'body_ar' => '',
                    ],
                    [
                        'heading' => 'Products & Pricing',
                        'heading_ar' => '',
                        'body' => "We make every effort to display our products and prices accurately. Prices are shown in your selected currency for convenience, but orders are processed and charged in Omani Rial (OMR) unless stated otherwise. We reserve the right to correct pricing errors and to change prices at any time without prior notice.",
                        'body_ar' => '',
                    ],
                    [
                        'heading' => 'Orders & Payment',
                        'heading_ar' => '',
                        'body' => "When you place an order, you are making an offer to purchase the selected products. We reserve the right to accept or decline any order, including in cases of suspected fraud, pricing errors, or stock unavailability. Coupon codes and discounts are subject to the terms displayed at the time of use and cannot be combined unless stated.",
                        'body_ar' => '',
                    ],
                    [
                        'heading' => 'Delivery',
                        'heading_ar' => '',
                        'body' => "We aim to deliver orders within the estimated timeframes provided at checkout. Delivery times are estimates only and may vary due to courier delays, customs processing for international shipments, or circumstances beyond our control.",
                        'body_ar' => '',
                    ],
                    [
                        'heading' => 'Product Reviews',
                        'heading_ar' => '',
                        'body' => "Customers who have created an account may submit reviews for products they have purchased. Reviews are moderated and published at our discretion. Reviews must be honest, relevant, and free of offensive or unlawful content. We reserve the right to remove any review that violates these guidelines.",
                        'body_ar' => '',
                    ],
                    [
                        'heading' => 'Intellectual Property',
                        'heading_ar' => '',
                        'body' => "All content on this website — including text, images, logos, and designs — is the property of Artisan Leather or its licensors and is protected by applicable intellectual property laws. You may not reproduce, distribute, or use this content without our prior written consent.",
                        'body_ar' => '',
                    ],
                    [
                        'heading' => 'Limitation of Liability',
                        'heading_ar' => '',
                        'body' => "To the fullest extent permitted by law, Artisan Leather shall not be liable for any indirect, incidental, or consequential damages arising from your use of our website or products.",
                        'body_ar' => '',
                    ],
                    [
                        'heading' => 'Governing Law',
                        'heading_ar' => '',
                        'body' => "These Terms of Service are governed by the laws of the Sultanate of Oman, and any disputes shall be subject to the exclusive jurisdiction of the Omani courts.",
                        'body_ar' => '',
                    ],
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_pages');
    }
};
