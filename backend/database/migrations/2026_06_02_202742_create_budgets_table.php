<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');           // 1-12
            $table->decimal('revenue_target', 10, 3)->default(0);
            $table->decimal('expense_budget', 10, 3)->default(0);
            $table->decimal('purchase_budget', 10, 3)->default(0);
            $table->text('notes')->nullable();
            $table->unique(['year','month']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('budgets'); }
};
