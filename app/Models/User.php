<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Prescription;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_DOCTOR = 'doctor';
    public const ROLE_PHARMACIST = 'pharmacist';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'date_of_birth',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Get the prescriptions for the user (doctor).
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'doctor_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Check if the user has the required role to access the panel
        return $this->hasRole(self::ROLE_ADMIN);
    }
}
