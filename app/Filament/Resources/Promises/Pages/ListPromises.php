<?php

namespace App\Filament\Resources\Promises\Pages;

use App\Filament\Resources\Promises\PromiseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPromises extends ListRecords
{
    protected static string $resource = PromiseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
