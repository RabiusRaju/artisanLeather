<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyResponse extends Model
{
    protected $fillable = [
        'survey_id','session_token','ip_address',
        'respondent_name','respondent_email','respondent_country','completed_at',
    ];

    protected $casts = ['completed_at' => 'datetime'];

    public function survey(): BelongsTo { return $this->belongsTo(Survey::class); }
    public function answers(): HasMany  { return $this->hasMany(SurveyAnswer::class); }

    public function isComplete(): bool  { return $this->completed_at !== null; }
}
