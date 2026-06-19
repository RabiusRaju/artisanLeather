<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE survey_questions MODIFY COLUMN type ENUM(
            'single_choice','multiple_choice','rating','nps','text_short','text_long','yes_no','dropdown','image_choice'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE survey_questions MODIFY COLUMN type ENUM(
            'single_choice','multiple_choice','rating','nps','text_short','text_long','yes_no','dropdown'
        ) NOT NULL");
    }
};
