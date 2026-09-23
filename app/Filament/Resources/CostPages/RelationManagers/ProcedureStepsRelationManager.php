<?php

namespace App\Filament\Resources\CostPages\RelationManagers;

use App\Filament\Support\TranslatedTabs;
use App\Models\CostProcedureStep;
use App\Support\Locale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Steps of the "how is it performed" section. Numbered by their order.
 */
class ProcedureStepsRelationManager extends RelationManager
{
    protected static string $relationship = 'procedureSteps';

    protected static ?string $title = 'Procedure steps';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TranslatedTabs::make('Texts', fn (string $code, bool $isDefault): array => [
                    TextInput::make("title.{$code}")
                        ->label('Step title')
                        ->required($isDefault)
                        ->maxLength(120),
                    Textarea::make("body.{$code}")
                        ->label('Description')
                        ->rows(3)
                        ->maxLength(400),
                    TextInput::make("duration.{$code}")
                        ->label('Duration')
                        ->maxLength(30)
                        ->placeholder('~45 min'),
                ]),

                Toggle::make('is_published')
                    ->label('Published')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated(false)
            ->columns([
                TextColumn::make('title')
                    ->state(fn (CostProcedureStep $record): ?string => $record->translate('title', Locale::DEFAULT))
                    ->weight('bold'),

                TextColumn::make('duration')
                    ->state(fn (CostProcedureStep $record): ?string => $record->translate('duration', Locale::DEFAULT))
                    ->color('gray'),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
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
