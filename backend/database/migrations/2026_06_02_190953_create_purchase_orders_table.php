<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();    // PO-2026-XXXXX
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->date('order_date');
            $table->date('expected_delivery')->nullable();
            $table->date('actual_delivery')->nullable();
            $table->enum('status', ['draft','ordered','partial','received','cancelled'])->default('draft');
            $table->string('currency', 3)->default('OMR');
            $table->decimal('exchange_rate', 10, 6)->default(1);   // rate to OMR at order time
            $table->decimal('subtotal_omr', 10, 3)->default(0);
            $table->decimal('shipping_cost_omr', 10, 3)->default(0);
            $table->decimal('customs_duty_omr', 10, 3)->default(0);
            $table->decimal('other_costs_omr', 10, 3)->default(0);
            $table->decimal('total_omr', 10, 3)->default(0);
            $table->enum('payment_status', ['unpaid','partial','paid'])->default('unpaid');
            $table->decimal('paid_amount_omr', 10, 3)->default(0);
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('purchase_orders'); }
};
