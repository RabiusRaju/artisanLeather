<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vat_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('rate', 5, 2)->default(5.00);   // 5% Oman VAT
            $table->string('registration_number')->nullable();
            $table->date('effective_from')->nullable();
            $table->boolean('is_registered')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('vat_settings'); }
};
