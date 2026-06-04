<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Business Settings (key-value) ─────────────────────────────────
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group')->default('general')->index();
            $table->text('value')->nullable();
            $table->string('type')->default('text')
                ->comment('text, textarea, url, email, phone, boolean, number, image');
            $table->string('label');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // ── Governorates ──────────────────────────────────────────────────
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('code', 10)->nullable();
            $table->string('country_code', 3)->default('OM');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Cities ────────────────────────────────────────────────────────
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Countries ─────────────────────────────────────────────────────
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('code', 3)->unique();
            $table->string('dial_code', 10)->nullable();
            $table->string('flag_emoji', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_gcc')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Seed: Default Business Settings ──────────────────────────────
        $now = now();
        DB::table('settings')->insert([
            // Business Info
            ['key'=>'business.name',          'group'=>'business','type'=>'text',    'label'=>'Business Name',       'value'=>'Artisan Leather',                      'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'business.tagline',       'group'=>'business','type'=>'text',    'label'=>'Tagline',             'value'=>'Luxury Handcrafted Leather Goods',     'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'business.email',         'group'=>'business','type'=>'email',   'label'=>'Business Email',      'value'=>'info@artisanleatherom.com',             'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'business.phone',         'group'=>'business','type'=>'phone',   'label'=>'Business Phone',      'value'=>'+968 XXXX XXXX',                       'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'business.whatsapp',      'group'=>'business','type'=>'phone',   'label'=>'WhatsApp Number',     'value'=>'96812345678',                          'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'business.address',       'group'=>'business','type'=>'textarea','label'=>'Business Address',    'value'=>'Muscat, Sultanate of Oman',             'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'business.city',          'group'=>'business','type'=>'text',    'label'=>'City',                'value'=>'Muscat',                               'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'business.country',       'group'=>'business','type'=>'text',    'label'=>'Country',             'value'=>'Oman',                                 'created_at'=>$now,'updated_at'=>$now],
            // Social Media
            ['key'=>'social.instagram',       'group'=>'social',  'type'=>'url',     'label'=>'Instagram URL',       'value'=>'https://www.instagram.com/artisanleather', 'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'social.facebook',        'group'=>'social',  'type'=>'url',     'label'=>'Facebook URL',        'value'=>'https://www.facebook.com/artisanleather',  'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'social.tiktok',          'group'=>'social',  'type'=>'url',     'label'=>'TikTok URL',          'value'=>null,                                   'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'social.twitter',         'group'=>'social',  'type'=>'url',     'label'=>'Twitter / X URL',     'value'=>null,                                   'created_at'=>$now,'updated_at'=>$now],
            // Website
            ['key'=>'website.url',            'group'=>'website', 'type'=>'url',     'label'=>'Website URL',         'value'=>'https://artisanleatherom.com',          'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'website.support_email',  'group'=>'website', 'type'=>'email',   'label'=>'Support Email',       'value'=>'support@artisanleatherom.com',          'created_at'=>$now,'updated_at'=>$now],
            // Order Settings
            ['key'=>'orders.free_delivery_threshold','group'=>'orders','type'=>'number','label'=>'Free Delivery Above (OMR)', 'value'=>'0',                            'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'orders.default_currency','group'=>'orders',  'type'=>'text',    'label'=>'Default Currency',    'value'=>'OMR',                                  'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'orders.whatsapp_message','group'=>'orders',  'type'=>'textarea','label'=>'WhatsApp Order Message Template', 'value'=>'Hello Artisan Leather, I would like to place an order.', 'created_at'=>$now,'updated_at'=>$now],
            // SEO
            ['key'=>'seo.meta_title',         'group'=>'seo',     'type'=>'text',    'label'=>'Default SEO Title',   'value'=>'Artisan Leather — Luxury Handcrafted Leather Goods, Muscat Oman', 'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'seo.meta_description',   'group'=>'seo',     'type'=>'textarea','label'=>'Default SEO Description', 'value'=>'Premium handcrafted leather wallets, bags, belts and accessories. Made by artisans in Muscat, Oman. Free delivery across Oman and GCC.', 'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'seo.google_analytics',   'group'=>'seo',     'type'=>'text',    'label'=>'Google Analytics ID (GA4)', 'value'=>null,                            'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'seo.google_tag_manager', 'group'=>'seo',     'type'=>'text',    'label'=>'Google Tag Manager ID',    'value'=>null,                            'created_at'=>$now,'updated_at'=>$now],
        ]);

        // ── Seed: Oman Governorates ───────────────────────────────────────
        $govs = [
            ['Muscat','مسقط','MC',1], ['Dhofar','ظفار','ZU',2], ['Musandam','مسندم','MU',3],
            ['Al Buraimi','البريمي','BU',4], ['Al Dakhiliyah','الداخلية','DA',5],
            ['Al Batinah North','الباطنة شمال','BN',6], ['Al Batinah South','الباطنة جنوب','BS',7],
            ['Al Sharqiyah North','الشرقية شمال','SN',8], ['Al Sharqiyah South','الشرقية جنوب','SS',9],
            ['Al Dhahirah','الظاهرة','DH',10], ['Al Wusta','الوسطى','WU',11],
        ];
        foreach ($govs as [$name, $nameAr, $code, $sort]) {
            DB::table('governorates')->insert(['name'=>$name,'name_ar'=>$nameAr,'code'=>$code,'country_code'=>'OM','sort_order'=>$sort,'is_active'=>true,'created_at'=>$now,'updated_at'=>$now]);
        }

        // ── Seed: Major Oman Cities ───────────────────────────────────────
        $muscatId = DB::table('governorates')->where('code','MC')->value('id');
        $cities = [
            [$muscatId,'Muscat','مسقط',1], [$muscatId,'Al Khuwair','الخوير',2],
            [$muscatId,'Ruwi','الروي',3], [$muscatId,'Qurum','قرم',4],
            [$muscatId,'Madinat Al Sultan Qaboos','مدينة السلطان قابوس',5],
            [$muscatId,'Bausher','بوشر',6], [$muscatId,'Al Amerat','العامرات',7],
            [$muscatId,'Seeb','السيب',8], [$muscatId,'Mutrah','مطرح',9],
            [$muscatId,'Al Ghubra','الغبرة',10],
        ];
        foreach ($cities as [$govId, $name, $nameAr, $sort]) {
            DB::table('cities')->insert(['governorate_id'=>$govId,'name'=>$name,'name_ar'=>$nameAr,'sort_order'=>$sort,'is_active'=>true,'created_at'=>$now,'updated_at'=>$now]);
        }

        // ── Seed: Key Countries (GCC first) ──────────────────────────────
        $countries = [
            ['Oman','عُمان','OMR','+968','🇴🇲',true,1], ['UAE','الإمارات','AED','+971','🇦🇪',true,2],
            ['Saudi Arabia','المملكة العربية السعودية','SAR','+966','🇸🇦',true,3],
            ['Kuwait','الكويت','KWD','+965','🇰🇼',true,4], ['Bahrain','البحرين','BHD','+973','🇧🇭',true,5],
            ['Qatar','قطر','QAR','+974','🇶🇦',true,6], ['Jordan','الأردن','JOD','+962','🇯🇴',false,7],
            ['Egypt','مصر','EGP','+20','🇪🇬',false,8], ['India','الهند','INR','+91','🇮🇳',false,9],
            ['United Kingdom','المملكة المتحدة','GBP','+44','🇬🇧',false,10],
            ['United States','الولايات المتحدة','USD','+1','🇺🇸',false,11],
            ['Other','أخرى','---',null,'🌍',false,99],
        ];
        foreach ($countries as [$name,$nameAr,$code,$dial,$flag,$gcc,$sort]) {
            DB::table('countries')->insert(['name'=>$name,'name_ar'=>$nameAr,'code'=>$code,'dial_code'=>$dial,'flag_emoji'=>$flag,'is_gcc'=>$gcc,'sort_order'=>$sort,'is_active'=>true,'created_at'=>$now,'updated_at'=>$now]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('governorates');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('settings');
    }
};
