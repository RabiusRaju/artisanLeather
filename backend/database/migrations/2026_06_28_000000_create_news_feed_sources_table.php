<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_feed_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('feed_url')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('news_feed_sources')->insert([
            ['name' => 'International Leather Maker', 'feed_url' => 'https://internationalleathermaker.com/feed', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Tandy Leather Blog', 'feed_url' => 'https://www.tandyleather.com/blogs/tandy-blog.atom', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'The Super Belt', 'feed_url' => 'https://thesuperbelt.com/feed', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Fine Leatherworking', 'feed_url' => 'https://www.fineleatherworking.com/feed', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'WWD', 'feed_url' => 'https://wwd.com/feed', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'The Business of Fashion', 'feed_url' => 'https://www.businessoffashion.com/feed', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Leather News', 'feed_url' => 'https://leathernews.org/feed/', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Von Baer Blog', 'feed_url' => 'https://vonbaer.com/blogs/blog.atom', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Trayvax Articles', 'feed_url' => 'https://www.trayvax.com/blogs/news.atom', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Trusador Wallets', 'feed_url' => 'https://www.trusador.com/blogs/wallets.atom', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('news_feed_sources');
    }
};
