<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('other_income', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount_omr', 10, 3);
            $table->date('income_date');
            $table->enum('category', ['wholesale','gifting','rental','refund','other'])->default('other');
            $table->enum('payment_method', ['cash','bank_transfer','cheque','card','other'])->default('bank_transfer');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('other_income'); }
};
