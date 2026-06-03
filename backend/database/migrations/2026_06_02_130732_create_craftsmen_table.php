<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('craftsmen', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('phone')->nullable();
            $table->enum('speciality', ['cutting','stitching','finishing','monogramming','all'])->default('all');
            $table->decimal('hourly_rate_omr', 8, 3)->default(1.500);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('craftsmen'); }
};
