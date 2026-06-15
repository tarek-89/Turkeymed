<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\Images\ResponsiveImageGenerator;
use App\Support\Locale;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class ManageHomePage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Homepage';

    protected static ?string $title = 'Homepage';

    protected string $view = 'filament.pages.manage-home-page';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'hero_badge' => Setting::get('home.hero_badge', []),
            'hero_title' => Setting::get('home.hero_title', []),
            'hero_title_accent' => Setting::get('home.hero_title_accent', []),
            'hero_lead' => Setting::get('home.hero_lead', []),
            'hero_images' => Setting::get('home.hero_images', []),
            'hero_stat_value' => Setting::get('home.hero_stat_value'),
            'hero_stat_label' => Setting::get('home.hero_stat_label', []),
            'cta_title' => Setting::get('home.cta_title', []),
            'cta_text' => Setting::get('home.cta_text', []),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hero')
                    ->description('Top of the homepage: badge, headline, intro, the photo slider and the highlighted stat (e.g. "98% graft survival").')
                    ->components([
                        Tabs::make('Hero translations')->tabs(
                            collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                                ->schema([
                                    TextInput::make("hero_badge.{$code}")
                                        ->label('Badge')
                                        ->maxLength(120)
                                        ->placeholder('Istanbul · 15,000+ patients treated'),
                                    TextInput::make("hero_title.{$code}")
                                        ->label('Title')
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(160)
                                        ->placeholder('World-class care,'),
                                    TextInput::make("hero_title_accent.{$code}")
                                        ->label('Title (accent line)')
                                        ->maxLength(160)
                                        ->placeholder('handled for you.')
                                        ->helperText('Shown in the cyan gradient, on its own line.'),
                                    Textarea::make("hero_lead.{$code}")
                                        ->label('Intro text')
                                        ->rows(2)
                                        ->maxLength(400),
                                    TextInput::make("hero_stat_label.{$code}")
                                        ->label('Stat label')
                                        ->maxLength(60)
                                        ->placeholder('graft survival'),
                                ]))->all(),
                        ),

                        TextInput::make('hero_stat_value')
                            ->label('Stat value')
                            ->maxLength(20)
                            ->placeholder('98%')
                            ->helperText('Language-neutral figure shown on the floating card.'),

                        FileUpload::make('hero_images')
                            ->label('Hero images (slider)')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->panelLayout('grid')
                            ->maxFiles(6)
                            ->disk('r2')
                            ->directory('home/hero')
                            ->visibility('private')
                            ->maxSize(4096)
                            ->helperText('One or more photos. With several, the hero shows a slider. Drag to reorder.'),
                    ]),

                Section::make('Bottom call to action')
                    ->components([
                        Tabs::make('CTA translations')->tabs(
                            collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                                ->schema([
                                    TextInput::make("cta_title.{$code}")
                                        ->label('Title')
                                        ->maxLength(160)
                                        ->placeholder('Start with a free consultation'),
                                    Textarea::make("cta_text.{$code}")
                                        ->label('Text')
                                        ->rows(2)
                                        ->maxLength(400),
                                ]))->all(),
                        ),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ([
            'hero_badge', 'hero_title', 'hero_title_accent', 'hero_lead',
            'hero_stat_value', 'hero_stat_label', 'cta_title', 'cta_text',
        ] as $key) {
            Setting::set('home.'.$key, $state[$key] ?? null);
        }

        $heroImages = array_values($state['hero_images'] ?? []);
        Setting::set('home.hero_images', $heroImages);
        $this->generateHeroImageMeta($heroImages);

        Notification::make()
            ->title('Homepage saved')
            ->success()
            ->send();
    }

    /**
     * Generate responsive WebP variants + intrinsic dimensions for the hero
     * images and cache the metadata as a setting, so the homepage can render
     * width/height and a srcset for its LCP image. Mirrors FeaturedImageObserver
     * and only runs when R2 storage is configured (local/test stay untouched).
     *
     * @param  list<string>  $paths
     */
    private function generateHeroImageMeta(array $paths): void
    {
        if (blank(config('filesystems.disks.r2.key'))) {
            return;
        }

        $generator = app(ResponsiveImageGenerator::class);
        $disk = Storage::disk('r2');
        $meta = [];

        foreach ($paths as $path) {
            $result = $generator->generate($disk, $path);

            if ($result !== null) {
                $meta[$path] = $result;
            }
        }

        Setting::set('home.hero_images_meta', $meta);
    }
}
