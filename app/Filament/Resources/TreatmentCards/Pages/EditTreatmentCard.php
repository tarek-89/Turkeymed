<?php

namespace App\Filament\Resources\TreatmentCards\Pages;

use App\Filament\Resources\TreatmentCards\TreatmentCardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTreatmentCard extends EditRecord
{
    protected static string $resource = TreatmentCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
