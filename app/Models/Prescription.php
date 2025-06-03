<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PrescriptionItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Prescription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'symptom',
        'prescription_status',
        'payment_status',
        'consultation_fee',
        'ppn_rate_applied',
        'total_amount',
        'paid_amount',
        'payment_method',
        'notes_pharmacist',
        'submitted_at',
        'completed_at',
        'paid_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'consultation_fee' => 'decimal:2',
            'ppn_rate_applied' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the patient that owns the prescription.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor that created the prescription.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the prescription items for the prescription.
     */
    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    /**
     * Get the invoice associated with the prescription.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
