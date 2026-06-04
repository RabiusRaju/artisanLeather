<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Surveys ───────────────────────────────────────────────────────
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('response_limit')->nullable()->comment('Max responses, null = unlimited');
            $table->boolean('allow_multiple_responses')->default(false);
            $table->boolean('show_progress')->default(true);
            $table->boolean('is_anonymous')->default(true);
            $table->text('thank_you_message')->nullable();
            $table->text('thank_you_message_ar')->nullable();
            $table->string('redirect_url')->nullable()->comment('URL to redirect after completion');
            $table->timestamps();
        });

        // ── Survey Questions ──────────────────────────────────────────────
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('type', [
                'single_choice',    // radio buttons
                'multiple_choice',  // checkboxes
                'rating',           // 1-5 star rating
                'nps',              // Net Promoter Score 0-10
                'text_short',       // single line text
                'text_long',        // multi-line text
                'yes_no',           // Yes / No
                'dropdown',         // dropdown select
            ]);
            $table->text('question');
            $table->text('question_ar')->nullable();
            $table->text('description')->nullable()->comment('Helper text shown below question');
            $table->json('options')->nullable()->comment('Array of options for choice/dropdown types');
            $table->json('options_ar')->nullable();
            $table->boolean('is_required')->default(true);
            $table->json('settings')->nullable()->comment('Type-specific settings: {min, max, placeholder}');
            $table->timestamps();
        });

        // ── Survey Responses (one per submission) ─────────────────────────
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('session_token', 64)->index()->comment('Prevent duplicate submissions');
            $table->string('ip_address', 45)->nullable();
            $table->string('respondent_name')->nullable();
            $table->string('respondent_email')->nullable();
            $table->string('respondent_country')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // ── Survey Answers (one per question per response) ─────────────────
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_question_id')->constrained()->cascadeOnDelete();
            $table->json('answer')->comment('String, array, or number depending on type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('surveys');
    }
};
