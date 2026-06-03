<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('minimum_alert')->default(3)->comment('Alert when stock falls below this');
            $table->integer('reorder_qty')->default(10)->comment('Suggested reorder quantity');
            $table->string('location')->nullable()->comment('e.g. Shelf A, Storage Room');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('product_stock'); }
};
