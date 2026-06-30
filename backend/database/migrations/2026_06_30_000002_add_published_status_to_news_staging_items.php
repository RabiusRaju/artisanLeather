<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE news_staging_items MODIFY COLUMN status ENUM('new', 'generated', 'dismissed', 'published') NOT NULL DEFAULT 'new'");
    }

    public function down(): void
    {
        // Update any 'published' rows back to 'generated' before shrinking the enum
        DB::statement("UPDATE news_staging_items SET status = 'generated' WHERE status = 'published'");
        DB::statement("ALTER TABLE news_staging_items MODIFY COLUMN status ENUM('new', 'generated', 'dismissed') NOT NULL DEFAULT 'new'");
    }
};
