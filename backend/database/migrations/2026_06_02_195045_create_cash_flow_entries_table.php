<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cash_flow_entries', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['in','out']);
            $table->enum('category', [
                'sales','custom_order','purchase','salary','rent','utility',
                'marketing','shipping','tax','loan','refund','other'
            ]);
            $table->string('description');
            $table->decimal('amount_omr', 10, 3);
            $table->date('entry_date');
            $table->enum('payment_method', ['cash','bank_transfer','card','cheque','other'])->default('bank_transfer');
            $table->string('bank_reference')->nullable();
            $table->boolean('is_reconciled')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('cash_flow_entries'); }
};
