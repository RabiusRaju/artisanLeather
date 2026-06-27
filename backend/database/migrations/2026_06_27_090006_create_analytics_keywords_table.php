<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_keywords', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('query');
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->decimal('ctr', 6, 3)->default(0);
            $table->decimal('position', 6, 2)->default(0);
            $table->timestamps();

            $table->unique(['date', 'query']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_keywords');
    }
};
