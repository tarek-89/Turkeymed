<?php

namespace App\Filament\Resources\CostPages\RelationManagers;

use App\Filament\Support\TranslatedTabs;
use App\Models\CostPage;
use App\Models\PricingTechnique;
use App\Models\PricingTier;
use App\Support\Locale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TagsInput;
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
 * Packages (Basic / Standard / Premium). The form shows one price input per
 * technique; those inputs are not columns, so the actions below read and
 * write them to pricing_tier_prices explicitly.
 */
class TiersRelationManager extends RelationManager
{
    protected static string $relationship = 'tiers';

    protected static ?string $title = 'Packages';

    private const PRICE_PREFIX = 'price_for_technique_';

    public function form(Schema $schema): Schema
    {
        /** @var CostPage $page */
        $page = $this->getOwnerRecord();
        $techniques = $page->techniques;

        return $schema
            ->columns(1)
            ->components([
                TranslatedTabs::make('Texts', fn (string $code, bool $isDefault): array => [
                    TextInput::make("name.{$code}")
                        ->label('Package name')
                        ->required($isDefault)
                        ->maxLength(60)
                        ->placeholder('Standard'),
                    TextInput::make("subtitle.{$code}")
                        ->label('Subtitle')
                        ->maxLength(80)
                        ->placeholder('Best for short trips'),
                    TagsInput::make("highlights.{$code}")
                        ->label('Highlight tags')
                        ->placeholder('Add a tag')
                        ->helperText('Short tags under the price, e.g. "2 nights", "1 consultation".'),
                    TextInput::make("cta_label.{$code}")
                        ->label('Button label')
                        ->maxLength(60)
                        ->helperText('Leave empty for the section default.'),
                ]),

                Section::make('Prices')
                    ->description($techniques->isEmpty()
                        ? 'Add a technique in the "Techniques" tab first.'
                        : 'One price per technique, in the page currency.')
                    ->columns(3)
                    ->components(
                        $techniques->map(fn (PricingTechnique $technique) => TextInput::make(self::PRICE_PREFIX.$technique->id)
                            ->label($technique->translate('name', Locale::DEFAULT))
                            ->numeric()
                            ->minValue(0)
                            ->prefix($page->currency))
                            ->all(),
                    ),

                Grid::make(2)->components([
                    Toggle::make('is_featured')
                        ->label('"Most popular" badge'),

                    Toggle::make('is_published')
                        ->label('Published')
                        ->default(true),
                ]),
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
                TextColumn::make('name')
                    ->state(fn (PricingTier $record): ?string => $record->translate('name', Locale::DEFAULT))
                    ->weight('bold'),

                TextColumn::make('prices')
                    ->label('Prices')
                    ->state(fn (PricingTier $record): string => $page->techniques
                        ->map(fn (PricingTechnique $technique): string => $technique->translate('name', Locale::DEFAULT).' '.($record->priceFor($technique) ?? '—'))
                        ->implode(' · ')),

                IconColumn::make('is_featured')
                    ->label('Popular')
                    ->boolean(),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data): PricingTier {
                        /** @var CostPage $page */
                        $page = $this->getOwnerRecord();

                        /** @var PricingTier $tier */
                        $tier = $page->tiers()->create(self::withoutPrices($data));
                        self::savePrices($tier, $data);

                        return $tier;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, PricingTier $record): array {
                        foreach ($record->prices as $price) {
                            $data[self::PRICE_PREFIX.$price->pricing_technique_id] = $price->price;
                        }

                        return $data;
                    })
                    ->using(function (array $data, PricingTier $record): PricingTier {
                        $record->update(self::withoutPrices($data));
                        self::savePrices($record, $data);

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
    private static function withoutPrices(array $data): array
    {
        return Arr::where($data, fn ($value, string $key): bool => ! str_starts_with($key, self::PRICE_PREFIX));
    }

    /** @param  array<string, mixed>  $data */
    private static function savePrices(PricingTier $tier, array $data): void
    {
        foreach ($data as $key => $value) {
            if (! str_starts_with($key, self::PRICE_PREFIX)) {
                continue;
            }

            $tier->prices()->updateOrCreate(
                ['pricing_technique_id' => (int) substr($key, strlen(self::PRICE_PREFIX))],
                ['price' => filled($value) ? (int) $value : null],
            );
        }

        $tier->unsetRelation('prices');
    }
}
