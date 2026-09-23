<?php

namespace App\Filament\Resources\CostPages\Schemas;

use App\Filament\Support\TranslatedTabs;
use App\Models\CostPage;
use App\Models\GraftZone;
use App\Models\ServiceCategory;
use App\Support\Locale;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CostPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Page')
                    ->description('The headline and URL. Section texts, packages, zones and the rest are edited in the tabs below once the page is saved.')
                    ->columnSpan(2)
                    ->components([
                        TranslatedTabs::make('Title', fn (string $code, bool $isDefault): array => [
                            TextInput::make("title.{$code}")
                                ->label('Page title (H1)')
                                ->required($isDefault)
                                ->maxLength(160)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $operation, ?string $state, Set $set, Get $get) use ($code): void {
                                    if ($operation === 'create' && $code === Locale::DEFAULT && blank($get('slug'))) {
                                        $set('slug', Str::slug($state ?? ''));
                                    }
                                }),
                        ]),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(200)
                            ->unique(ignoreRecord: true)
                            ->prefix('/pricing/')
                            ->helperText('Same for every language. Changing it on a live page breaks its links, so add a redirect.'),
                    ]),

                Section::make('Publishing')
                    ->columnSpan(1)
                    ->components([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(false),

                        Toggle::make('is_unlisted')
                            ->label('Unlisted (private link)')
                            ->default(false)
                            ->live()
                            ->helperText('Not in the footer, sitemap or llms.txt, and marked noindex for search and AI crawlers. Share it by link only.'),

                        TextInput::make('access_key')
                            ->label('Access key')
                            ->maxLength(64)
                            ->alphaNum()
                            ->visible(fn (Get $get): bool => (bool) $get('is_unlisted'))
                            ->live(onBlur: true)
                            ->helperText('Optional. With a key, the page only opens through the link below; without the key it is a 404.')
                            ->suffixAction(
                                Action::make('generateKey')
                                    ->icon(Heroicon::OutlinedArrowPath)
                                    ->tooltip('Generate a key')
                                    ->action(fn (Set $set) => $set('access_key', Str::random(24))),
                            ),

                        Placeholder::make('share_link')
                            ->label('Share link')
                            ->visible(fn (Get $get, ?CostPage $record): bool => (bool) $get('is_unlisted') && $record !== null)
                            ->content(function (Get $get, ?CostPage $record): HtmlString {
                                $url = $record->url(Locale::DEFAULT);
                                $key = $get('access_key');

                                if (filled($key)) {
                                    $url .= '?key='.$key;
                                }

                                return new HtmlString('<code class="select-all break-all text-xs">'.e($url).'</code>');
                            }),

                        Select::make('service_category_id')
                            ->label('Treatment category')
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->orderBy('sort_order'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (ServiceCategory $record): ?string => $record->translate('name', Locale::DEFAULT))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Used for the breadcrumb, the related articles and the before/after results.'),
                    ]),

                Section::make('Prices')
                    ->columnSpan(1)
                    ->columns(2)
                    ->components([
                        Select::make('currency')
                            ->options(['EUR' => 'EUR €', 'USD' => 'USD $', 'GBP' => 'GBP £'])
                            ->default('EUR')
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),

                        TextInput::make('price_range_min')
                            ->label('Range from')
                            ->numeric()
                            ->minValue(0),

                        TextInput::make('price_range_max')
                            ->label('Range to')
                            ->numeric()
                            ->minValue(0)
                            ->gte('price_range_min'),
                    ])
                    ->description('The "Turkey" price range shown in the comparison. Leave empty to use the cheapest and dearest package price.'),

                Section::make('Graft calculator settings')
                    ->description('The numbers behind the estimate. Zone names and graft ranges are edited in the "Graft zones" tab; the buttons in "Quick picks".')
                    ->columnSpanFull()
                    ->collapsed()
                    ->columns(4)
                    ->components([
                        TextInput::make('hairs_per_graft')
                            ->label('Hairs per graft')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(1)
                            ->maxValue(5)
                            ->default(2.2),

                        TextInput::make('session_cap_grafts')
                            ->label('Two sessions above')
                            ->numeric()
                            ->minValue(500)
                            ->default(4500)
                            ->suffix('grafts')
                            ->helperText('Estimates above this are shown as two sessions.'),

                        TextInput::make('two_session_price_from')
                            ->label('Two-session price from')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Shown as "from …" for two-session cases.'),

                        TextInput::make('meter_max_grafts')
                            ->label('Meter scale max')
                            ->numeric()
                            ->minValue(1000)
                            ->default(5000)
                            ->suffix('grafts'),

                        CheckboxList::make('default_zone_numbers')
                            ->label('Zones selected when the page opens')
                            ->options(fn (): array => collect(GraftZone::NUMBERS)->mapWithKeys(fn (int $n): array => [$n => 'Zone '.$n])->all())
                            ->columns(6)
                            ->columnSpanFull(),

                        Repeater::make('duration_rules')
                            ->label('Operation time by graft count')
                            ->columnSpanFull()
                            ->columns(3)
                            ->reorderableWithButtons()
                            ->addActionLabel('Add rule')
                            ->helperText('Rules are checked top to bottom: the first rule whose "up to" is above the estimate wins. Leave "up to" empty for the last rule.')
                            ->schema([
                                TextInput::make('max_grafts')
                                    ->label('Up to (grafts)')
                                    ->numeric()
                                    ->minValue(1),
                                ...collect(Locale::codes())->map(fn (string $code) => TextInput::make("label.{$code}")
                                    ->label('Label ('.strtoupper($code).')')
                                    ->maxLength(40)
                                    ->required($code === Locale::DEFAULT))->all(),
                            ]),
                    ]),

                Section::make('SEO')
                    ->columnSpanFull()
                    ->columns(3)
                    ->components([
                        TranslatedTabs::make('Meta', fn (string $code): array => [
                            TextInput::make("meta_title.{$code}")
                                ->label('Meta title')
                                ->maxLength(160)
                                ->helperText('Leave empty to use the page title.'),
                            Textarea::make("meta_description.{$code}")
                                ->label('Meta description')
                                ->rows(3)
                                ->maxLength(320)
                                ->helperText('Leave empty to use the hero intro text.'),
                        ])->columnSpan(2),

                        FileUpload::make('og_image')
                            ->label('Share image')
                            ->image()
                            ->disk('r2')
                            ->directory('pricing')
                            ->visibility('private')
                            ->maxSize(4096)
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
