<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_top_pages', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('page_path');
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('users')->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->unsignedInteger('avg_engagement_time')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->decimal('revenue_omr', 10, 3)->default(0);
            $table->timestamps();

            $table->unique(['date', 'page_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_top_pages');
    }
};
