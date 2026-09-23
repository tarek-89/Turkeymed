<?php

namespace App\Filament\Resources\CostPages\RelationManagers;

use App\Filament\Support\TranslatedTabs;
use App\Models\RecoveryPhase;
use App\Support\Locale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Coloured bands under the recovery chart (e.g. "Healing & shedding 0–3").
 */
class RecoveryPhasesRelationManager extends RelationManager
{
    protected static string $relationship = 'recoveryPhases';

    protected static ?string $title = 'Recovery phases';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(3)->components([
                    TextInput::make('from_month')
                        ->label('From month')
                        ->numeric()
                        ->step(0.5)
                        ->minValue(0)
                        ->maxValue(24)
                        ->required(),

                    TextInput::make('to_month')
                        ->label('To month')
                        ->numeric()
                        ->step(0.5)
                        ->minValue(0)
                        ->maxValue(24)
                        ->required()
                        ->gt('from_month'),

                    Select::make('tone')
                        ->label('Colour')
                        ->options(RecoveryPhase::TONES)
                        ->default('navy')
                        ->required()
                        ->native(false),
                ]),

                TranslatedTabs::make('Texts', fn (string $code, bool $isDefault): array => [
                    TextInput::make("name.{$code}")
                        ->label('Name')
                        ->required($isDefault)
                        ->maxLength(40)
                        ->placeholder('Healing & shedding'),
                    TextInput::make("short_name.{$code}")
                        ->label('Short name')
                        ->maxLength(16)
                        ->placeholder('Healing')
                        ->helperText('Shown on small screens.'),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->state(fn (RecoveryPhase $record): ?string => $record->translate('name', Locale::DEFAULT))
                    ->weight('bold'),

                TextColumn::make('from_month')
                    ->label('From')
                    ->numeric(decimalPlaces: 1),

                TextColumn::make('to_month')
                    ->label('To')
                    ->numeric(decimalPlaces: 1),

                TextColumn::make('tone')
                    ->label('Colour')
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
