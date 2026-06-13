<?php

namespace App\Filament\Resources\Galleries\Tables;

use App\Models\Gallery;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GalleriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->state(fn (Gallery $record): ?string => $record->translate('title', 'en'))
                    ->searchable(query: fn ($query, string $search) => $query->where('title', 'like', "%{$search}%")),

                TextColumn::make('layout')
                    ->badge(),

                TextColumn::make('images')
                    ->label('Photos')
                    ->state(fn (Gallery $record): int => count((array) $record->images)),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Published'),
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
