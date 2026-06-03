<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('custom_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();   // CUS-2026-XXXXX
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');                // denormalised for safety
            $table->string('customer_phone');
            $table->enum('product_type', ['wallet','bag','belt','accessory','other']);
            $table->string('product_name')->nullable();     // e.g. "Bifold Wallet"
            $table->text('description')->nullable();
            // Leather specifications
            $table->string('leather_color')->nullable();
            $table->string('leather_type')->nullable();     // Full Grain, Calfskin etc
            $table->string('stitching_color')->nullable();
            $table->enum('hardware_color', ['gold','silver','antique_brass','none'])->default('gold');
            $table->string('size')->nullable();
            $table->string('monogram')->nullable();         // e.g. "M.A.R"
            $table->text('personalisation_notes')->nullable();
            $table->json('reference_images')->nullable();   // array of file paths
            // Financials
            $table->decimal('agreed_price_omr', 10, 3);
            $table->decimal('deposit_amount_omr', 10, 3)->default(0);
            $table->boolean('deposit_paid')->default(false);
            $table->datetime('deposit_paid_at')->nullable();
            // Workflow
            $table->enum('status', [
                'inquiry','confirmed','in_production','quality_check','ready','delivered','cancelled'
            ])->default('inquiry');
            $table->date('promised_date')->nullable();
            $table->date('delivered_at')->nullable();
            $table->string('whatsapp_thread')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('custom_orders'); }
};
