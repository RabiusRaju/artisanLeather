<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Products ──────────────────────────────────────────────────────
        Schema::table('products', function (Blueprint $table) {
            $table->string('meta_title', 70)->nullable()->after('badge')
                ->comment('Custom SEO title (max 60 chars). Falls back to product name.');
            $table->string('meta_description', 170)->nullable()->after('meta_title')
                ->comment('Custom SEO description (max 160 chars). Falls back to tagline.');
        });

        // ── Brands/Collections ────────────────────────────────────────────
        Schema::table('brands', function (Blueprint $table) {
            $table->string('meta_title', 70)->nullable()->after('is_featured')
                ->comment('Custom SEO title for the collection page.');
            $table->string('meta_description', 170)->nullable()->after('meta_title')
                ->comment('Custom SEO description for the collection page.');
        });

        // ── Product Images ────────────────────────────────────────────────
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('alt_text', 125)->nullable()->after('label')
                ->comment('Descriptive alt text for image SEO and accessibility.');
        });
    }

    public function down(): void
    {
        Schema::table('products',      fn(Blueprint $t) => $t->dropColumn(['meta_title', 'meta_description']));
        Schema::table('brands',        fn(Blueprint $t) => $t->dropColumn(['meta_title', 'meta_description']));
        Schema::table('product_images',fn(Blueprint $t) => $t->dropColumn('alt_text'));
    }
};
