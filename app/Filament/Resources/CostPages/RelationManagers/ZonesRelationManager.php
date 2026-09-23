<?php

namespace App\Filament\Resources\CostPages\RelationManagers;

use App\Filament\Support\TranslatedTabs;
use App\Models\GraftZone;
use App\Support\Locale;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * The six scalp zones of the graft calculator. Their outlines are drawn on
 * the head illustration, so zones are edited (name, graft range) but never
 * added or removed.
 */
class ZonesRelationManager extends RelationManager
{
    protected static string $relationship = 'zones';

    protected static ?string $title = 'Graft zones';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TranslatedTabs::make('Name', fn (string $code, bool $isDefault): array => [
                    TextInput::make("name.{$code}")
                        ->label('Zone name')
                        ->required($isDefault)
                        ->maxLength(60),
                ]),

                Grid::make(2)->components([
                    TextInput::make('min_grafts')
                        ->label('Grafts from')
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    TextInput::make('max_grafts')
                        ->label('Grafts to')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->gte('min_grafts'),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('number')
            ->paginated(false)
            ->columns([
                TextColumn::make('number')
                    ->label('#')
                    ->weight('bold'),

                TextColumn::make('name')
                    ->state(fn (GraftZone $record): ?string => $record->translate('name', Locale::DEFAULT)),

                TextColumn::make('min_grafts')
                    ->label('From')
                    ->numeric(),

                TextColumn::make('max_grafts')
                    ->label('To')
                    ->numeric(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
