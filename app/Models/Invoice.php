<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prescription_id',
        'invoice_number',
        'issue_date',
        'subtotal_medicines',
        'consultation_fee',
        'ppn_amount',
        'grand_total',
        'payment_method',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'datetime',
            'subtotal_medicines' => 'decimal:2',
            'consultation_fee' => 'decimal:2',
            'ppn_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    /**
     * Get the prescription that owns the invoice.
     */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }
}
