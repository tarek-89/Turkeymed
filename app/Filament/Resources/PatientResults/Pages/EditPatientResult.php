<?php

namespace App\Filament\Resources\PatientResults\Pages;

use App\Filament\Resources\PatientResults\PatientResultResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPatientResult extends EditRecord
{
    protected static string $resource = PatientResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
