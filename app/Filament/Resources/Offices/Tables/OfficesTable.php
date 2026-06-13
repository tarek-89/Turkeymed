<?php

namespace App\Filament\Resources\Offices\Tables;

use App\Models\Office;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class OfficesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('country')
                    ->state(fn (Office $record): ?string => $record->translate('country', 'en'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('name')
                    ->state(fn (Office $record): ?string => $record->translate('name', 'en'))
                    ->searchable(query: fn ($query, string $search) => $query->where('name', 'like', "%{$search}%")),

                TextColumn::make('phone')
                    ->placeholder('—')
                    ->toggleable(),

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
