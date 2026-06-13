<?php

namespace App\Filament\Resources\PatientResults\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PatientResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('before_image')
                    ->label('Before')
                    ->disk('r2')
                    ->imageSize(40),

                ImageColumn::make('after_image')
                    ->label('After')
                    ->disk('r2')
                    ->imageSize(40),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->badge(),

                TextColumn::make('service.title')
                    ->label('Pinned to')
                    ->limit(35)
                    ->placeholder('Whole category')
                    ->toggleable(),

                TextColumn::make('grafts_count')
                    ->label('Grafts')
                    ->numeric()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('months_to_result')
                    ->label('Months')
                    ->placeholder('—')
                    ->toggleable(),

                IconColumn::make('consent_confirmed')
                    ->label('Consent')
                    ->boolean(),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('service_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),

                TernaryFilter::make('is_published')
                    ->label('Published'),

                TernaryFilter::make('consent_confirmed')
                    ->label('Consent confirmed'),
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
