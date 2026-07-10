<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'image_alt')) {
                $table->string('image_alt', 125)->nullable()->after('image');
            }
        });

        Schema::table('brands', function (Blueprint $table) {
            if (!Schema::hasColumn('brands', 'logo_alt')) {
                $table->string('logo_alt', 125)->nullable()->after('logo');
            }

            if (!Schema::hasColumn('brands', 'banner_alt')) {
                $table->string('banner_alt', 125)->nullable()->after('banner');
            }
        });

        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'featured_image_alt')) {
                $table->string('featured_image_alt', 125)->nullable()->after('featured_image');
            }
        });

        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'popup_image_alt')) {
                $table->string('popup_image_alt', 125)->nullable()->after('popup_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (Schema::hasColumn('coupons', 'popup_image_alt')) {
                $table->dropColumn('popup_image_alt');
            }
        });

        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'featured_image_alt')) {
                $table->dropColumn('featured_image_alt');
            }
        });

        Schema::table('brands', function (Blueprint $table) {
            $columns = array_filter(['logo_alt', 'banner_alt'], fn (string $column) => Schema::hasColumn('brands', $column));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'image_alt')) {
                $table->dropColumn('image_alt');
            }
        });
    }
};
