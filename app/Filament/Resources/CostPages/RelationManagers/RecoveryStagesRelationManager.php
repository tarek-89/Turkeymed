<?php

namespace App\Filament\Resources\CostPages\RelationManagers;

use App\Filament\Support\TranslatedTabs;
use App\Models\RecoveryStage;
use App\Support\Locale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Milestones of the recovery timeline: when, how much result is visible,
 * and what to expect.
 */
class RecoveryStagesRelationManager extends RelationManager
{
    protected static string $relationship = 'recoveryStages';

    protected static ?string $title = 'Recovery stages';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(2)->components([
                    TextInput::make('month')
                        ->label('Month')
                        ->numeric()
                        ->step(0.5)
                        ->minValue(0)
                        ->maxValue(24)
                        ->required()
                        ->helperText('0.5 = two weeks. Sets the point on the chart.'),

                    TextInput::make('percent')
                        ->label('Visible result')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->required()
                        ->suffix('%'),
                ]),

                TranslatedTabs::make('Texts', fn (string $code, bool $isDefault): array => [
                    TextInput::make("when_label.{$code}")
                        ->label('When')
                        ->required($isDefault)
                        ->maxLength(40)
                        ->placeholder('After 3 months'),
                    TextInput::make("short_label.{$code}")
                        ->label('Short label')
                        ->required($isDefault)
                        ->maxLength(6)
                        ->placeholder('3m')
                        ->helperText('Shown on the chart on small screens.'),
                    TextInput::make("title.{$code}")
                        ->label('Title')
                        ->required($isDefault)
                        ->maxLength(80),
                    Textarea::make("body.{$code}")
                        ->label('What happens')
                        ->rows(3)
                        ->maxLength(400),
                    TextInput::make("tip.{$code}")
                        ->label('Tip ("your part")')
                        ->maxLength(160),
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
                TextColumn::make('month')
                    ->label('Month')
                    ->numeric(decimalPlaces: 1),

                TextColumn::make('percent')
                    ->label('Visible')
                    ->suffix('%'),

                TextColumn::make('title')
                    ->state(fn (RecoveryStage $record): ?string => $record->translate('title', Locale::DEFAULT))
                    ->weight('bold'),
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
