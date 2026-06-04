<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    protected $fillable = [
        'title','title_ar','slug','description','description_ar',
        'status','starts_at','ends_at','response_limit','allow_multiple_responses',
        'show_progress','is_anonymous','thank_you_message','thank_you_message_ar','redirect_url',
    ];

    protected $casts = [
        'starts_at'               => 'datetime',
        'ends_at'                 => 'datetime',
        'allow_multiple_responses'=> 'boolean',
        'show_progress'           => 'boolean',
        'is_anonymous'            => 'boolean',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('sort_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->starts_at === null || $this->starts_at->lte(now()))
            && ($this->ends_at   === null || $this->ends_at->gte(now()))
            && ($this->response_limit === null || $this->responses()->count() < $this->response_limit);
    }

    public function getResponseCountAttribute(): int
    {
        return $this->responses()->count();
    }

    public function getCompletionRateAttribute(): float
    {
        $total     = $this->responses()->count();
        $completed = $this->responses()->whereNotNull('completed_at')->count();
        return $total > 0 ? round(($completed / $total) * 100, 1) : 0;
    }
}
