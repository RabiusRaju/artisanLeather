<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('show_as_popup')->default(false)->after('is_active');
            $table->dateTime('expires_at')->nullable()->after('show_as_popup');
            $table->string('popup_title')->nullable()->after('expires_at');
            $table->string('popup_image')->nullable()->after('popup_title');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['show_as_popup', 'expires_at', 'popup_title', 'popup_image']);
        });
    }
};
