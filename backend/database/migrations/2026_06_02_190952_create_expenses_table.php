<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount_omr', 10, 3);
            $table->date('expense_date');
            $table->enum('payment_method', ['cash','bank_transfer','cheque','card','other'])->default('bank_transfer');
            $table->string('reference')->nullable()->comment('Receipt or invoice number');
            $table->string('receipt_image')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_period', ['weekly','monthly','quarterly','yearly'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('expenses'); }
};
