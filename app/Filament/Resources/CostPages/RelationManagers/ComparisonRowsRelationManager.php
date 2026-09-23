<?php

namespace App\Filament\Resources\CostPages\RelationManagers;

use App\Filament\Support\TranslatedTabs;
use App\Models\CostComparisonRow;
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
 * Rows of the "Turkey vs abroad" table.
 */
class ComparisonRowsRelationManager extends RelationManager
{
    protected static string $relationship = 'comparisonRows';

    protected static ?string $title = 'Comparison table';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TranslatedTabs::make('Texts', fn (string $code, bool $isDefault): array => [
                    TextInput::make("label.{$code}")
                        ->label('Row label')
                        ->required($isDefault)
                        ->maxLength(120)
                        ->placeholder('Typical price'),
                    Textarea::make("ours.{$code}")
                        ->label('Our column')
                        ->required($isDefault)
                        ->rows(2)
                        ->maxLength(300),
                    Textarea::make("theirs.{$code}")
                        ->label('Other column')
                        ->required($isDefault)
                        ->rows(2)
                        ->maxLength(300),
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
                TextColumn::make('label')
                    ->state(fn (CostComparisonRow $record): ?string => $record->translate('label', Locale::DEFAULT))
                    ->weight('bold'),

                TextColumn::make('ours')
                    ->label('Ours')
                    ->state(fn (CostComparisonRow $record): ?string => $record->translate('ours', Locale::DEFAULT))
                    ->limit(50),

                TextColumn::make('theirs')
                    ->label('Theirs')
                    ->state(fn (CostComparisonRow $record): ?string => $record->translate('theirs', Locale::DEFAULT))
                    ->limit(50),

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
