<?php

namespace App\Filament\Resources\CostPages\RelationManagers;

use App\Filament\Support\TranslatedTabs;
use App\Models\PricingTechnique;
use App\Support\Locale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Techniques (FUE / DHI / VIP): the tabs above the package cards. Each
 * package has one price per technique, edited on the package.
 */
class TechniquesRelationManager extends RelationManager
{
    protected static string $relationship = 'techniques';

    protected static ?string $title = 'Techniques';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TranslatedTabs::make('Texts', fn (string $code, bool $isDefault): array => [
                    TextInput::make("name.{$code}")
                        ->label('Name')
                        ->required($isDefault)
                        ->maxLength(40)
                        ->placeholder('FUE'),
                    TextInput::make("tagline.{$code}")
                        ->label('Tagline')
                        ->maxLength(60)
                        ->placeholder('Max grafts'),
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
                TextColumn::make('name')
                    ->state(fn (PricingTechnique $record): ?string => $record->translate('name', Locale::DEFAULT))
                    ->weight('bold'),

                TextColumn::make('tagline')
                    ->state(fn (PricingTechnique $record): ?string => $record->translate('tagline', Locale::DEFAULT))
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
