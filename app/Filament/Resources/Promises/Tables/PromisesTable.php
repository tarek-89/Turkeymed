<?php

namespace App\Filament\Resources\Promises\Tables;

use App\Models\Promise;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PromisesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('icon')
                    ->badge(),

                TextColumn::make('title')
                    ->state(fn (Promise $record): ?string => $record->translate('title', 'en'))
                    ->searchable(query: fn ($query, string $search) => $query->where('title', 'like', "%{$search}%")),

                TextColumn::make('text')
                    ->state(fn (Promise $record): ?string => $record->translate('text', 'en'))
                    ->limit(60)
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
