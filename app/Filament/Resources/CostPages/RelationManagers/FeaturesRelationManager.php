<?php

namespace App\Filament\Resources\CostPages\RelationManagers;

use App\Filament\Support\TranslatedTabs;
use App\Models\CostPage;
use App\Models\PricingFeature;
use App\Models\PricingTier;
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
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;

/**
 * Package features (rows of the comparison list). Each feature has, per
 * package, an "included" switch and an optional text ("2 nights"). Those
 * inputs live in pricing_feature_values and are read/written explicitly.
 */
class FeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'features';

    protected static ?string $title = 'Package features';

    private const TIER_PREFIX = 'tier_';

    public function form(Schema $schema): Schema
    {
        /** @var CostPage $page */
        $page = $this->getOwnerRecord();

        return $schema
            ->columns(1)
            ->components([
                TranslatedTabs::make('Label', fn (string $code, bool $isDefault): array => [
                    TextInput::make("label.{$code}")
                        ->label('Feature')
                        ->required($isDefault)
                        ->maxLength(120)
                        ->placeholder('4★ hotel stay near the clinic'),
                ]),

                ...$page->tiers->map(fn (PricingTier $tier) => Section::make($tier->translate('name', Locale::DEFAULT) ?? 'Package')
                    ->compact()
                    ->columns(1)
                    ->components([
                        Toggle::make(self::TIER_PREFIX.$tier->id.'_included')
                            ->label('Included in this package')
                            ->live(),
                        Grid::make(count(Locale::codes()))
                            ->visible(fn ($get): bool => (bool) $get(self::TIER_PREFIX.$tier->id.'_included'))
                            ->components(collect(Locale::codes())->map(fn (string $code) => TextInput::make(self::TIER_PREFIX.$tier->id.'_value_'.$code)
                                ->label('Detail ('.strtoupper($code).')')
                                ->maxLength(80)
                                ->placeholder('2 nights'))->all()),
                    ]))->all(),

                Toggle::make('is_published')
                    ->label('Published')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        /** @var CostPage $page */
        $page = $this->getOwnerRecord();

        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated(false)
            ->columns([
                TextColumn::make('label')
                    ->state(fn (PricingFeature $record): ?string => $record->translate('label', Locale::DEFAULT))
                    ->weight('bold'),

                TextColumn::make('values')
                    ->label('Per package')
                    ->state(fn (PricingFeature $record): string => $page->tiers
                        ->map(function (PricingTier $tier) use ($record): string {
                            $value = $record->valueFor($tier);
                            $mark = $value?->is_included ? ($value->translate('value', Locale::DEFAULT) ?: '✓') : '—';

                            return $tier->translate('name', Locale::DEFAULT).': '.$mark;
                        })
                        ->implode(' · ')),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data): PricingFeature {
                        /** @var CostPage $page */
                        $page = $this->getOwnerRecord();

                        /** @var PricingFeature $feature */
                        $feature = $page->features()->create(self::withoutValues($data));
                        self::saveValues($feature, $data);

                        return $feature;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, PricingFeature $record): array {
                        foreach ($record->values as $value) {
                            $data[self::TIER_PREFIX.$value->pricing_tier_id.'_included'] = $value->is_included;

                            foreach ((array) $value->value as $code => $text) {
                                $data[self::TIER_PREFIX.$value->pricing_tier_id.'_value_'.$code] = $text;
                            }
                        }

                        return $data;
                    })
                    ->using(function (array $data, PricingFeature $record): PricingFeature {
                        $record->update(self::withoutValues($data));
                        self::saveValues($record, $data);

                        return $record;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function withoutValues(array $data): array
    {
        return Arr::where($data, fn ($value, string $key): bool => ! str_starts_with($key, self::TIER_PREFIX));
    }

    /** @param  array<string, mixed>  $data */
    private static function saveValues(PricingFeature $feature, array $data): void
    {
        $byTier = [];

        foreach ($data as $key => $value) {
            if (! preg_match('/^'.self::TIER_PREFIX.'(\d+)_(included|value_([a-z]{2}))$/', $key, $m)) {
                continue;
            }

            $tierId = (int) $m[1];
            $byTier[$tierId] ??= ['is_included' => false, 'value' => []];

            if ($m[2] === 'included') {
                $byTier[$tierId]['is_included'] = (bool) $value;
            } elseif (filled($value)) {
                $byTier[$tierId]['value'][$m[3]] = (string) $value;
            }
        }

        foreach ($byTier as $tierId => $attributes) {
            $feature->values()->updateOrCreate(
                ['pricing_tier_id' => $tierId],
                ['is_included' => $attributes['is_included'], 'value' => $attributes['value'] ?: null],
            );
        }

        $feature->unsetRelation('values');
    }
}
