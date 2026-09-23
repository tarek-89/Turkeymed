<?php

namespace App\Filament\Resources\CostPages\Tables;

use App\Models\CostPage;
use App\Support\Locale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CostPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->state(fn (CostPage $record): ?string => $record->translate('title', Locale::DEFAULT))
                    ->searchable(query: fn ($query, string $search) => $query->where('title', 'like', "%{$search}%"))
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->prefix('/pricing/')
                    ->color('gray'),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->state(fn (CostPage $record): ?string => $record->category?->translate('name', Locale::DEFAULT)),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                IconColumn::make('is_unlisted')
                    ->label('Unlisted')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedEyeSlash)
                    ->falseIcon(Heroicon::OutlinedGlobeAlt)
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn (CostPage $record): string => $record->is_unlisted
                        ? 'Private link only — not in the footer, sitemap or search engines'
                        : 'Public — linked and indexable'),

                TextColumn::make('updated_at')
                    ->since()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Published'),

                TernaryFilter::make('is_unlisted')
                    ->label('Unlisted'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
