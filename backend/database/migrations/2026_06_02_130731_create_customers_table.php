<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('country')->default('Oman');
            $table->string('governorate')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->enum('preferred_category', ['wallets','bags','belts','accessories'])->nullable();
            $table->string('preferred_color')->nullable();
            $table->json('tags')->nullable();          // ['vip','wholesale','gift_buyer']
            $table->enum('status', ['active','inactive','vip'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('customers'); }
};
