<?php

namespace App\Filament\Resources\Promises\Pages;

use App\Filament\Resources\Promises\PromiseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPromise extends EditRecord
{
    protected static string $resource = PromiseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
