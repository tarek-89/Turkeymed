<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageOrganizationPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Organization (SEO)';

    protected static ?string $title = 'Organization & trust';

    protected string $view = 'filament.pages.manage-organization-page';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'legal_name' => Setting::get('org.legal_name'),
            'founding_date' => Setting::get('org.founding_date'),
            'area_served' => Setting::get('org.area_served'),
            'logo' => Setting::get('org.logo'),
            'og_image' => Setting::get('org.og_image'),
            'accreditations' => self::linesToText(Setting::get('org.accreditations', [])),
            'medical_specialties' => self::linesToText(Setting::get('org.medical_specialties', [])),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identity')
                    ->description('Feeds the sitewide MedicalOrganization structured data that search engines and AI assistants use to recognise you.')
                    ->columns(2)
                    ->components([
                        TextInput::make('legal_name')
                            ->label('Registered legal name')
                            ->maxLength(200)
                            ->helperText('The official company name (may differ from the brand).'),

                        TextInput::make('founding_date')
                            ->label('Founded')
                            ->maxLength(20)
                            ->placeholder('2015'),

                        TextInput::make('area_served')
                            ->label('Area served')
                            ->maxLength(120)
                            ->placeholder('Worldwide'),

                        FileUpload::make('logo')
                            ->label('Square logo')
                            ->image()
                            ->disk('r2')
                            ->directory('branding')
                            ->visibility('private')
                            ->maxSize(2048)
                            ->helperText('Used as the organization logo in structured data. A square PNG works best.'),

                        FileUpload::make('og_image')
                            ->label('Social share image (Open Graph)')
                            ->image()
                            ->disk('r2')
                            ->directory('branding')
                            ->visibility('private')
                            ->maxSize(2048)
                            ->helperText('Default preview image when pages are shared on social/chat. 1200×630 PNG or JPG. Pages with their own featured image use that instead.'),
                    ]),

                Section::make('Trust signals')
                    ->description('One item per line. These are surfaced as credentials/specialties in structured data — enter only real, verifiable values.')
                    ->columns(2)
                    ->components([
                        Textarea::make('accreditations')
                            ->label('Accreditations & certifications')
                            ->rows(4)
                            ->placeholder("JCI Accredited\nISO 9001"),

                        Textarea::make('medical_specialties')
                            ->label('Medical specialties')
                            ->rows(4)
                            ->placeholder("Hair Transplant Surgery\nDentistry"),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::set('org.legal_name', $state['legal_name'] ?? null);
        Setting::set('org.founding_date', $state['founding_date'] ?? null);
        Setting::set('org.area_served', $state['area_served'] ?? null);
        Setting::set('org.logo', $state['logo'] ?? null);
        Setting::set('org.og_image', $state['og_image'] ?? null);
        Setting::set('org.accreditations', self::textToLines($state['accreditations'] ?? null));
        Setting::set('org.medical_specialties', self::textToLines($state['medical_specialties'] ?? null));

        Notification::make()
            ->title('Organization settings saved')
            ->success()
            ->send();
    }

    /**
     * @param  mixed  $value
     */
    private static function linesToText($value): string
    {
        return implode("\n", array_filter((array) $value, static fn ($item): bool => is_string($item) && $item !== ''));
    }

    /**
     * @return list<string>
     */
    private static function textToLines(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value) ?: [])
            ->map(static fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
