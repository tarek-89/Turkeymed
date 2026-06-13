<?php

namespace App\Filament\Pages;

use App\Models\Setting;
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
use UnitEnum;

class ManageAboutPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'About';

    protected static ?string $title = 'About page';

    protected string $view = 'filament.pages.manage-about-page';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'heading' => Setting::get('about.heading', []),
            'text' => Setting::get('about.text', []),
            'images' => Setting::get('about.images', []),
            'story_title' => Setting::get('about.story_title', []),
            'story_text' => Setting::get('about.story_text', []),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('About TurkeyMed')
                    ->description('The hero of the about page: heading, intro text and the photo slider.')
                    ->components([
                        Tabs::make('About translations')->tabs(
                            collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                                ->schema([
                                    TextInput::make("heading.{$code}")
                                        ->label('Heading')
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(200),

                                    Textarea::make("text.{$code}")
                                        ->label('Intro text')
                                        ->rows(4)
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(1000),
                                ]))->all(),
                        ),

                        FileUpload::make('images')
                            ->label('Photos (slider)')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(8)
                            ->disk('r2')
                            ->directory('pages/about')
                            ->visibility('private')
                            ->maxSize(4096)
                            ->panelLayout('grid')
                            ->helperText('Shown beside the heading. With more than one photo, visitors get a slider. Drag to reorder.'),
                    ]),

                Section::make('Our story')
                    ->components([
                        Tabs::make('Story translations')->tabs(
                            collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                                ->schema([
                                    TextInput::make("story_title.{$code}")
                                        ->label('Title')
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(200),

                                    Textarea::make("story_text.{$code}")
                                        ->label('Text')
                                        ->rows(8)
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(5000)
                                        ->helperText('Separate paragraphs with a blank line.'),
                                ]))->all(),
                        ),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::set('about.heading', $state['heading'] ?? []);
        Setting::set('about.text', $state['text'] ?? []);
        Setting::set('about.images', array_values($state['images'] ?? []));
        Setting::set('about.story_title', $state['story_title'] ?? []);
        Setting::set('about.story_text', $state['story_text'] ?? []);

        Notification::make()
            ->title('About page saved')
            ->success()
            ->send();
    }
}
