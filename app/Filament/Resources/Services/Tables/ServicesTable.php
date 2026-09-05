<?php

namespace App\Filament\Resources\Services\Tables;

use App\Filament\Resources\Services\ServiceResource;
use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Locale;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort(fn (Builder $query): Builder => $query->orderBy('sort_order')->orderBy('title'))
            ->reorderable('sort_order')
            ->afterReordering(function (array $order): void {
                // Drag-reorder is a bulk SQL update (no model events), so mirror
                // each new position to the row's other-language translations.
                Service::query()
                    ->whereIn('id', $order)
                    ->whereNotNull('translation_group_id')
                    ->get()
                    ->each(fn (Service $service) => $service->syncSortOrderToTranslations());
            })
            ->description('Order controls the header menu and category page. Filter by a category, then use the ▲ ▼ buttons (or "Reorder" to drag). Changing the order in one language applies to all languages.')
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('')
                    ->disk('r2')
                    ->imageSize(40),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->description(fn (Service $record): string => '/'.($record->language === Post::DEFAULT_LANGUAGE ? '' : $record->language.'/').$record->slug),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->state(fn (Service $record): ?string => $record->category?->translate('name', Locale::DEFAULT))
                    ->badge()
                    ->placeholder('Uncategorized'),

                TextColumn::make('language')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->sortable(),

                TextColumn::make('translations')
                    ->label('Languages')
                    ->state(function (Service $record): ?string {
                        if (! $record->translation_group_id) {
                            return null;
                        }

                        return Service::query()
                            ->where('translation_group_id', $record->translation_group_id)
                            ->orderBy('language')
                            ->pluck('language')
                            ->map(fn (string $code): string => strtoupper($code))
                            ->implode(' · ');
                    })
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'publish' ? 'success' : 'warning')
                    ->formatStateUsing(fn (string $state): string => $state === 'publish' ? 'Published' : 'Draft'),

                IconColumn::make('is_elementor')
                    ->label('Elementor')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('published_at')
                    ->dateTime('M j, Y')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('service_category_id')
                    ->label('Category')
                    ->options(fn (): array => ServiceCategory::query()
                        ->orderBy('sort_order')
                        ->get()
                        ->mapWithKeys(fn (ServiceCategory $category): array => [
                            $category->getKey() => $category->translate('name', Locale::DEFAULT),
                        ])
                        ->all()),

                SelectFilter::make('language')
                    ->options(fn (): array => Post::languageOptions()),

                SelectFilter::make('status')
                    ->options([
                        'publish' => 'Published',
                        'draft' => 'Draft',
                    ]),

                TernaryFilter::make('is_elementor')
                    ->label('Elementor-built'),
            ])
            ->recordActions([
                Action::make('moveUp')
                    ->label('Move up')
                    ->icon(Heroicon::OutlinedChevronUp)
                    ->iconButton()
                    ->color('gray')
                    ->action(fn (Service $record) => $record->moveBy(-1)),
                Action::make('moveDown')
                    ->label('Move down')
                    ->icon(Heroicon::OutlinedChevronDown)
                    ->iconButton()
                    ->color('gray')
                    ->action(fn (Service $record) => $record->moveBy(1)),
                Action::make('view')
                    ->label('View')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Service $record): string => $record->url())
                    ->openUrlInNewTab(),
                ServiceResource::addTranslationAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
