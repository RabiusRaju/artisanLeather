<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('meta_title_ar', 70)->nullable()->after('meta_description');
            $table->string('meta_description_ar', 170)->nullable()->after('meta_title_ar');
            $table->string('meta_title_bn', 70)->nullable()->after('meta_description_ar');
            $table->string('meta_description_bn', 170)->nullable()->after('meta_title_bn');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'meta_title_ar',
                'meta_description_ar',
                'meta_title_bn',
                'meta_description_bn',
            ]);
        });
    }
};
