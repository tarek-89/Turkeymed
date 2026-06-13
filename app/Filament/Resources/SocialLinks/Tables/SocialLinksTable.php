<?php

namespace App\Filament\Resources\SocialLinks\Tables;

use App\Models\SocialLink;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SocialLinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('platform')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => SocialLink::PLATFORMS[$state] ?? ucfirst($state)),

                TextColumn::make('url')
                    ->url(fn (SocialLink $record): string => $record->url, shouldOpenInNewTab: true)
                    ->limit(50)
                    ->color('primary'),

                TextColumn::make('label')
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
