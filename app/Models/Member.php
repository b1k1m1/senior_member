<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = [
        'member_no',
        'first_name',
        'last_name',
        'spouse_first_name',
        'spouse_last_name',
        'dateofbirth',
        'email',
        'phone',
        'cell_phone',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'county',
        'membership_type_id',
        'membership_start_date',
        'joining_year',
        'status',
        'status_reason',
        'notes',
        'photo_path',
        'receipt_no',
        'amount',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'membership_start_date' => 'date',
        'dateofbirth' => 'date',
        'joining_year' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($member) {
            if ($member->membership_start_date) {
                $member->joining_year = $member->membership_start_date->year;
            }

            if ($member->joining_year) {
                $member->joining_year = (int) $member->joining_year;
            }
        });
    }

    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getTotalPaymentsAttribute(): float
    {
        return $this->payments()->sum('amount');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'INACTIVE');
    }
}
