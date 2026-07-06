<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('news_staging_items')) {
            return;
        }

        Schema::create('news_staging_items', function (Blueprint $table) {
            $table->id();
            $table->string('source_name');
            $table->string('source_url');
            $table->string('article_url')->unique();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->enum('status', ['new', 'generated', 'dismissed'])->default('new')->index();
            $table->foreignId('generated_post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_staging_items');
    }
};
