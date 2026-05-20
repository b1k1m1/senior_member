<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $fillable = [
        'receipt_no',
        'receipt_type_id',
        'received_from',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'county',
        'bank_name',
        'check_date',
        'check_number',
        'payment_mode',
        'amount',
        'remarks',
        'member_id',
        'membership_type_id',
        'has_spouse',
        'event_id',
        'donor_name',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'check_date' => 'date',
        'has_spouse' => 'boolean',
        'amount' => 'decimal:2',
    ];

    public function receiptType(): BelongsTo
    {
        return $this->belongsTo(ReceiptType::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function generateReceiptNo(): string
    {
        $lastReceipt = self::orderBy('id', 'desc')->first();
        $lastNumber = $lastReceipt ? (int) substr($lastReceipt->receipt_no, 3) : 0;
        return 'RCP' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }
}
