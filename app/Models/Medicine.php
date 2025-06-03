<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\PrescriptionItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'stock',
        'dosages',
        'price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dosages' => 'array',
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get the prescription items for the medicine.
     */
    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}
