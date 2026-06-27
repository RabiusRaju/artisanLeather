<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_country', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('country');
            $table->unsignedInteger('sessions')->default(0);
            $table->unsignedInteger('users')->default(0);
            $table->timestamps();

            $table->unique(['date', 'country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_country');
    }
};
