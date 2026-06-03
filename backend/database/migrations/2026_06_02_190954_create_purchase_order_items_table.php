<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');       // Free text product/item description
            $table->string('sku')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('pcs');   // pcs, sq_ft, meters, kg etc.
            $table->decimal('unit_cost_omr', 10, 3);
            $table->decimal('total_cost_omr', 10, 3);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('purchase_order_items'); }
};
