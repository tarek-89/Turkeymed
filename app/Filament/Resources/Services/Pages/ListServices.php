<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All')
                ->badge(static fn (): int => Service::count())
                ->deferBadge(),
        ];

        $languages = Service::query()
            ->select('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language');

        foreach ($languages as $language) {
            $tabs[$language] = Tab::make(strtoupper($language))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('language', $language))
                ->badge(static fn (): int => Service::query()->where('language', $language)->count())
                ->deferBadge();
        }

        return $tabs;
    }
}
