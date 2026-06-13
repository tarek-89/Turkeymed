<?php

namespace App\Filament\Resources\TreatmentCards\Pages;

use App\Filament\Resources\TreatmentCards\TreatmentCardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTreatmentCards extends ListRecords
{
    protected static string $resource = TreatmentCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
