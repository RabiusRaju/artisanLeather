<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // M-1: Orders — status + created_at are filtered on every dashboard widget
        Schema::table('orders', function (Blueprint $table) {
            if (!$this->hasIndex('orders', 'orders_status_created_at_index')) {
                $table->index(['status', 'created_at'], 'orders_status_created_at_index');
            }
            if (!$this->hasIndex('orders', 'orders_email_index')) {
                $table->index('email', 'orders_email_index');
            }
        });

        // M-1: Posts — is_published + published_at filtered on every API call
        Schema::table('posts', function (Blueprint $table) {
            if (!$this->hasIndex('posts', 'posts_published_index')) {
                $table->index(['is_published', 'published_at'], 'posts_published_index');
            }
        });

        // Custom orders — status is filtered frequently
        Schema::table('custom_orders', function (Blueprint $table) {
            if (!$this->hasIndex('custom_orders', 'custom_orders_status_index')) {
                $table->index('status', 'custom_orders_status_index');
            }
        });

        // Survey responses — survey_id + ip_address + completed_at for dedup queries
        Schema::table('survey_responses', function (Blueprint $table) {
            if (!$this->hasIndex('survey_responses', 'survey_responses_ip_survey_index')) {
                $table->index(['survey_id', 'ip_address', 'completed_at'], 'survey_responses_ip_survey_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders',          fn($t) => $t->dropIndexIfExists('orders_status_created_at_index'));
        Schema::table('orders',          fn($t) => $t->dropIndexIfExists('orders_email_index'));
        Schema::table('posts',           fn($t) => $t->dropIndexIfExists('posts_published_index'));
        Schema::table('custom_orders',   fn($t) => $t->dropIndexIfExists('custom_orders_status_index'));
        Schema::table('survey_responses',fn($t) => $t->dropIndexIfExists('survey_responses_ip_survey_index'));
    }

    private function hasIndex(string $table, string $name): bool
    {
        $indexes = \Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$name]
        );
        return count($indexes) > 0;
    }
};
