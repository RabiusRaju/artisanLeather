<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('production_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('task_type', [
                'cut_hide','stitch','burnish_edges','dye','monogram',
                'hardware_fitting','quality_check','packaging','other'
            ])->default('other');
            // Linked to either a regular order OR a custom order
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('custom_order_id')->nullable()->constrained()->nullOnDelete();
            // Craftsman
            $table->foreignId('craftsman_id')->nullable()->constrained()->nullOnDelete();
            // Workflow
            $table->enum('priority', ['normal','rush','vip'])->default('normal');
            $table->enum('status', ['pending','in_progress','qc_check','done','redo'])->default('pending');
            $table->decimal('estimated_hours', 5, 2)->nullable();
            $table->decimal('actual_hours', 5, 2)->nullable();
            $table->date('due_date')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('production_tasks'); }
};
