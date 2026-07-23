<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('cta_type')->default('add_to_cart')->after('price');
            $table->string('cta_label')->nullable()->after('cta_type');
            $table->string('cta_label_ar')->nullable()->after('cta_label');
            $table->text('cta_note')->nullable()->after('cta_label_ar');
            $table->text('cta_note_ar')->nullable()->after('cta_note');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('is_preorder')->default(false)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'cta_type',
                'cta_label',
                'cta_label_ar',
                'cta_note',
                'cta_note_ar',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('is_preorder');
        });
    }
};
