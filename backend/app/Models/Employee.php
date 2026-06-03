<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model {
    protected $fillable = [
        'user_id',
        'department_id','name','name_ar','photo','date_of_birth','gender',
        'nationality','national_id','passport_number','visa_type','visa_expiry',
        'phone','whatsapp','email','governorate','city','address',
        'emergency_contact_name','emergency_contact_phone',
        'job_title','employment_type','date_hired','date_terminated',
        'monthly_salary_omr','hourly_rate_omr','skills','status','notes',
    ];
    protected $casts = [
        'skills' => 'array', 'date_of_birth' => 'date',
        'date_hired' => 'date', 'date_terminated' => 'date', 'visa_expiry' => 'date',
        'monthly_salary_omr' => 'decimal:3', 'hourly_rate_omr' => 'decimal:3',
    ];

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function user(): BelongsTo       { return $this->belongsTo(User::class); }

    public function hasAdminAccess(): bool  { return !is_null($this->user_id); }

    public function getAgeAttribute(): ?int {
        return $this->date_of_birth?->age;
    }
    public function getYearsOfServiceAttribute(): ?float {
        return $this->date_hired ? round($this->date_hired->diffInDays(now()) / 365, 1) : null;
    }
}
