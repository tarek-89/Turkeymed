<?php

namespace App\Filament\Resources\TreatmentCards\Tables;

use App\Models\TreatmentCard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TreatmentCardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->state(fn (TreatmentCard $record): ?string => $record->translate('title', 'en'))
                    ->searchable(query: fn ($query, string $search) => $query->where('title', 'like', "%{$search}%")),

                TextColumn::make('variant')
                    ->badge(),

                TextColumn::make('icon')
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
