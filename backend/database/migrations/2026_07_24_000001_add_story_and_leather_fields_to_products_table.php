<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('leather_type')->nullable()->after('material_ar');
            $table->string('leather_type_ar')->nullable()->after('leather_type');
            $table->string('story_title')->nullable()->after('description_ar');
            $table->string('story_title_ar')->nullable()->after('story_title');
            $table->text('story_body')->nullable()->after('story_title_ar');
            $table->text('story_body_ar')->nullable()->after('story_body');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'leather_type',
                'leather_type_ar',
                'story_title',
                'story_title_ar',
                'story_body',
                'story_body_ar',
            ]);
        });
    }
};
