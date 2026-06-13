<?php

namespace App\Filament\Resources\PatientResults\Pages;

use App\Filament\Resources\PatientResults\PatientResultResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPatientResults extends ListRecords
{
    protected static string $resource = PatientResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
