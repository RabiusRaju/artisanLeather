<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->string('tagline_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('material')->nullable();
            $table->string('material_ar')->nullable();
            $table->string('origin')->nullable();
            $table->string('origin_ar')->nullable();
            $table->text('care')->nullable();
            $table->text('care_ar')->nullable();
            $table->text('shipping')->nullable();
            $table->text('shipping_ar')->nullable();
            $table->decimal('price', 10, 3);
            $table->enum('badge', ['bestseller', 'new'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
