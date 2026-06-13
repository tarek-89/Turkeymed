<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

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
                ->badge(static fn (): int => Post::count())
                ->deferBadge(),
        ];

        $languages = Post::query()
            ->select('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language');

        foreach ($languages as $language) {
            $tabs[$language] = Tab::make(strtoupper($language))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('language', $language))
                ->badge(static fn (): int => Post::query()->where('language', $language)->count())
                ->deferBadge();
        }

        return $tabs;
    }
}
