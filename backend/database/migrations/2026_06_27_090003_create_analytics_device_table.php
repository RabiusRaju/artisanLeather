<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_device', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('device_category');
            $table->unsignedInteger('sessions')->default(0);
            $table->unsignedInteger('users')->default(0);
            $table->timestamps();

            $table->unique(['date', 'device_category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_device');
    }
};
