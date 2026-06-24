<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('badge');
            $table->string('dimensions')->nullable()->after('sku');
            $table->string('dimensions_ar')->nullable()->after('dimensions');
            $table->json('bulk_pricing')->nullable()->after('dimensions_ar');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sku', 'dimensions', 'dimensions_ar', 'bulk_pricing']);
        });
    }
};
