<?php

namespace App\Filament\Admin\Resources\MedicineResource\Pages;

use App\Filament\Admin\Resources\MedicineResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicine extends CreateRecord
{
    protected static string $resource = MedicineResource::class;

    protected static bool $canCreateAnother = false;
} 