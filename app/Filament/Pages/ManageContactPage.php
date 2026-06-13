<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\Locale;
use BackedEnum;
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

class ManageContactPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Contact';

    protected static ?string $title = 'Contact page';

    protected string $view = 'filament.pages.manage-contact-page';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'hero_eyebrow' => Setting::get('contact.hero_eyebrow', []),
            'hero_title' => Setting::get('contact.hero_title', []),
            'hero_text' => Setting::get('contact.hero_text', []),
            'method_whatsapp_desc' => Setting::get('contact.method_whatsapp_desc', []),
            'method_phone_desc' => Setting::get('contact.method_phone_desc', []),
            'method_email_desc' => Setting::get('contact.method_email_desc', []),
            'hours' => Setting::get('contact.hours', []),
            'form_embed' => Setting::get('contact.form_embed'),
            'map_embed' => Setting::get('contact.map_embed'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hero')
                    ->description('Top of the contact page.')
                    ->components([
                        $this->localeTabs('Hero translations', [
                            fn (string $code) => TextInput::make("hero_eyebrow.{$code}")
                                ->label('Eyebrow')
                                ->maxLength(100),
                            fn (string $code) => TextInput::make("hero_title.{$code}")
                                ->label('Title')
                                ->required($code === Locale::DEFAULT)
                                ->maxLength(200),
                            fn (string $code) => Textarea::make("hero_text.{$code}")
                                ->label('Intro text')
                                ->rows(3)
                                ->maxLength(600),
                        ]),
                    ]),

                Section::make('Contact method descriptions')
                    ->description('The phone, WhatsApp and email values come from the site settings. Here you edit the small description under each method card.')
                    ->components([
                        $this->localeTabs('Method translations', [
                            fn (string $code) => TextInput::make("method_whatsapp_desc.{$code}")
                                ->label('WhatsApp description')
                                ->maxLength(120),
                            fn (string $code) => TextInput::make("method_phone_desc.{$code}")
                                ->label('Call description')
                                ->maxLength(120),
                            fn (string $code) => TextInput::make("method_email_desc.{$code}")
                                ->label('Email description')
                                ->maxLength(120),
                        ]),
                    ]),

                Section::make('Office hours')
                    ->description('One line per row. Use "Label | Value" to show two columns (e.g. "Mon – Fri | 09:00 – 19:00"); a line without "|" renders as a full-width note.')
                    ->components([
                        $this->localeTabs('Hours translations', [
                            fn (string $code) => Textarea::make("hours.{$code}")
                                ->label('Hours')
                                ->rows(4),
                        ]),
                    ]),

                Section::make('Send us a message (embed)')
                    ->description('Paste the embed code for your contact form (e.g. an iframe or a form-provider snippet). Leave empty to hide the section.')
                    ->components([
                        Textarea::make('form_embed')
                            ->hiddenLabel()
                            ->rows(6)
                            ->extraAttributes(['class' => 'font-mono text-xs']),
                    ]),

                Section::make('Map (embed)')
                    ->description('Paste a Google Maps (or other) embed iframe. Leave empty to hide the map banner.')
                    ->components([
                        Textarea::make('map_embed')
                            ->hiddenLabel()
                            ->rows(6)
                            ->extraAttributes(['class' => 'font-mono text-xs']),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Build a per-locale Tabs component from a set of field factories.
     *
     * @param  array<int, \Closure>  $fields
     */
    private function localeTabs(string $label, array $fields): Tabs
    {
        return Tabs::make($label)->tabs(
            collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                ->schema(array_map(fn (\Closure $field) => $field($code), $fields)))->all(),
        );
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ([
            'hero_eyebrow', 'hero_title', 'hero_text',
            'method_whatsapp_desc', 'method_phone_desc', 'method_email_desc',
            'hours', 'form_embed', 'map_embed',
        ] as $key) {
            Setting::set('contact.'.$key, $state[$key] ?? null);
        }

        Notification::make()
            ->title('Contact page saved')
            ->success()
            ->send();
    }
}
