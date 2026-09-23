<?php

namespace App\Filament\Resources\CostPages\RelationManagers;

use App\Filament\Support\TranslatedTabs;
use App\Models\CostCountry;
use App\Support\Locale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Countries in the "prices compared worldwide" chart.
 */
class CountriesRelationManager extends RelationManager
{
    protected static string $relationship = 'countries';

    protected static ?string $title = 'Countries';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(3)->components([
                    TextInput::make('country_code')
                        ->label('Country code')
                        ->required()
                        ->length(2)
                        ->alpha()
                        ->placeholder('GB')
                        ->helperText('Two-letter ISO code, for the flag.')
                        ->dehydrateStateUsing(fn (?string $state): string => strtoupper((string) $state)),

                    TextInput::make('min_price')
                        ->label('Price from')
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    TextInput::make('max_price')
                        ->label('Price to')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->gte('min_price'),
                ]),

                TranslatedTabs::make('Texts', fn (string $code, bool $isDefault): array => [
                    TextInput::make("name.{$code}")
                        ->label('Country name')
                        ->required($isDefault)
                        ->maxLength(80),
                    TextInput::make("name_in_sentence.{$code}")
                        ->label('Name inside a sentence')
                        ->maxLength(80)
                        ->helperText('e.g. "the United Kingdom". Leave empty to reuse the name.'),
                    TextInput::make("note.{$code}")
                        ->label('Note under the bar')
                        ->maxLength(120)
                        ->placeholder('Surgery only · usually priced per graft'),
                ]),

                Grid::make(2)->components([
                    Toggle::make('is_default')
                        ->label('Selected when the page opens')
                        ->helperText('Only one country can be the default.'),

                    Toggle::make('is_published')
                        ->label('Published')
                        ->default(true),
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
                TextColumn::make('country_code')
                    ->label('Code')
                    ->badge(),

                TextColumn::make('name')
                    ->state(fn (CostCountry $record): ?string => $record->translate('name', Locale::DEFAULT))
                    ->weight('bold'),

                TextColumn::make('min_price')
                    ->label('From')
                    ->numeric(),

                TextColumn::make('max_price')
                    ->label('To')
                    ->numeric(),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

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
