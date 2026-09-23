<?php

namespace App\Filament\Resources\CostPages\RelationManagers;

use App\Filament\Support\TranslatedTabs;
use App\Models\CostPage;
use App\Models\GraftPreset;
use App\Models\GraftZone;
use App\Support\Locale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * "Quick pick" buttons under the zone list (e.g. Norwood stages).
 */
class PresetsRelationManager extends RelationManager
{
    protected static string $relationship = 'presets';

    protected static ?string $title = 'Quick picks';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TranslatedTabs::make('Label', fn (string $code, bool $isDefault): array => [
                    TextInput::make("label.{$code}")
                        ->label('Button label')
                        ->required($isDefault)
                        ->maxLength(40),
                ]),

                CheckboxList::make('zone_numbers')
                    ->label('Zones this button selects')
                    ->required()
                    ->options(function (): array {
                        /** @var CostPage $page */
                        $page = $this->getOwnerRecord();

                        return $page->zones
                            ->mapWithKeys(fn (GraftZone $zone): array => [$zone->number => $zone->number.' · '.$zone->translate('name', Locale::DEFAULT)])
                            ->all();
                    })
                    ->columns(3),

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
                    ->state(fn (GraftPreset $record): ?string => $record->translate('label', Locale::DEFAULT))
                    ->weight('bold'),

                TextColumn::make('zone_numbers')
                    ->label('Zones')
                    ->state(fn (GraftPreset $record): string => implode(', ', $record->zoneNumbers())),

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
