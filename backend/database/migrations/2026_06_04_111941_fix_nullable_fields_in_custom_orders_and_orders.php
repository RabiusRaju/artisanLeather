<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CustomOrder: agreed_price_omr should default 0 (inquiry has no price yet)
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->decimal('agreed_price_omr', 10, 3)->default(0)->change();
            $table->decimal('deposit_amount_omr', 10, 3)->default(0)->nullable()->change();
        });

        // Orders: governorate/city/address nullable (admin can create order without delivery details)
        Schema::table('orders', function (Blueprint $table) {
            $table->string('governorate')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->text('address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->decimal('agreed_price_omr', 10, 3)->change();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->string('governorate')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->text('address')->nullable(false)->change();
        });
    }
};
