<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_daily_summary', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedInteger('sessions')->default(0);
            $table->unsignedInteger('users')->default(0);
            $table->unsignedInteger('new_users')->default(0);
            $table->unsignedInteger('page_views')->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->unsignedInteger('avg_engagement_time')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_summary');
    }
};
