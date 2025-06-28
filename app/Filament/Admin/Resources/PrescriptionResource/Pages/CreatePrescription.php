<?php

namespace App\Filament\Admin\Resources\PrescriptionResource\Pages;

use App\Filament\Admin\Resources\PrescriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrescription extends CreateRecord
{
    protected static string $resource = PrescriptionResource::class;

    protected static bool $canCreateAnother = false;
} 