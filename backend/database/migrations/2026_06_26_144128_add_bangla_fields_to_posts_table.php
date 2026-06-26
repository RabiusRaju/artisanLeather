<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('title_bn')->nullable()->after('title_ar');
            $table->text('excerpt_bn')->nullable()->after('excerpt_ar');
            $table->longText('content_bn')->nullable()->after('content_ar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['title_bn', 'excerpt_bn', 'content_bn']);
        });
    }
};
