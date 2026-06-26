<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Customer extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name','name_ar','email','phone','whatsapp','date_of_birth',
        'country','governorate','city','address',
        'preferred_category','preferred_color','tags','status','notes',
    ];

    protected $casts = ['tags' => 'array', 'date_of_birth' => 'date'];

    // Custom/bespoke orders — proper FK customer_id
    public function customOrders(): HasMany
    {
        return $this->hasMany(CustomOrder::class);
    }

    // Regular shop orders — linked by email
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'email', 'email');
    }

    // Lifetime spend across both regular + custom orders
    public function getTotalSpendAttribute(): float
    {
        $customSpend  = $this->customOrders()->whereIn('status', ['ready', 'delivered'])->sum('agreed_price_omr');
        $regularSpend = $this->email ? Order::where('email', $this->email)->sum('total_omr') : 0;
        return (float) ($customSpend + $regularSpend);
    }

    // Total order count across both types
    public function getOrdersCountAttribute(): int
    {
        $custom  = $this->customOrders()->count();
        $regular = $this->email ? Order::where('email', $this->email)->count() : 0;
        return $custom + $regular;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => "New customer \"{$this->name}\" added",
                'updated' => "Customer \"{$this->name}\" updated",
                'deleted' => "Customer \"{$this->name}\" deleted",
                default => "Customer \"{$this->name}\" {$eventName}",
            });
    }
}
