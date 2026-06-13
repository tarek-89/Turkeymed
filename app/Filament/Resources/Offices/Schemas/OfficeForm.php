<?php

namespace App\Filament\Resources\Offices\Schemas;

use App\Support\Locale;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class OfficeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Location')
                    ->description('Country groups offices together on the public page. Use the same wording for offices in the same country.')
                    ->columnSpan(2)
                    ->components([
                        Tabs::make('Translations')->tabs(
                            collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                                ->schema([
                                    TextInput::make("country.{$code}")
                                        ->label('Country')
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(100),

                                    TextInput::make("name.{$code}")
                                        ->label('Office name')
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(150)
                                        ->placeholder('Istanbul · Şişli'),

                                    Textarea::make("address.{$code}")
                                        ->label('Address')
                                        ->rows(2)
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(300),

                                    TextInput::make("hours.{$code}")
                                        ->label('Hours')
                                        ->maxLength(150)
                                        ->placeholder('Mon–Sat · 09:00–19:00'),

                                    TextInput::make("badge.{$code}")
                                        ->label('Badge (optional)')
                                        ->maxLength(50)
                                        ->placeholder('Headquarters'),
                                ]))->all(),
                        ),
                    ]),

                Section::make('Details')
                    ->columnSpan(1)
                    ->components([
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(40),

                        TextInput::make('directions_url')
                            ->label('Directions URL')
                            ->url()
                            ->maxLength(1000)
                            ->helperText('Google Maps link for the "Get directions" button.'),

                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers show first. Rows can also be drag-reordered in the list.'),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true),
                    ]),
            ]);
    }
}
