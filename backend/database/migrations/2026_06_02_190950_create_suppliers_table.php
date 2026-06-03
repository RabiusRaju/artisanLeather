<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('country')->default('Oman');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->enum('category', ['leather_goods','hardware','packaging','accessories','services','other'])->default('leather_goods');
            $table->enum('payment_terms', ['prepaid','cod','net_15','net_30','net_60','net_90'])->default('prepaid');
            $table->string('currency', 3)->default('OMR');
            $table->decimal('credit_limit_omr', 10, 3)->nullable();
            $table->integer('lead_time_days')->nullable()->comment('Average delivery days');
            $table->integer('rating')->nullable()->comment('1-5 supplier rating');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('suppliers'); }
};
