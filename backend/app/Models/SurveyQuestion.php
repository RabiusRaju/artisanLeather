<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyQuestion extends Model
{
    protected $fillable = [
        'survey_id','sort_order','type','question','question_ar',
        'description','options','options_ar','is_required','settings',
    ];

    protected $casts = [
        'options'     => 'array',
        'options_ar'  => 'array',
        'settings'    => 'array',
        'is_required' => 'boolean',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    public function isChoiceType(): bool
    {
        return in_array($this->type, ['single_choice', 'multiple_choice', 'yes_no', 'dropdown']);
    }

    public function isRatingType(): bool
    {
        return in_array($this->type, ['rating', 'nps']);
    }

    public function isTextType(): bool
    {
        return in_array($this->type, ['text_short', 'text_long']);
    }
}
