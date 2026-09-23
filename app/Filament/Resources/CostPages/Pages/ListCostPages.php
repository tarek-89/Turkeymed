<?php

namespace App\Filament\Resources\CostPages\Pages;

use App\Filament\Resources\CostPages\CostPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCostPages extends ListRecords
{
    protected static string $resource = CostPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
