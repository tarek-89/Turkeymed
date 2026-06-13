<?php

namespace App\Filament\Resources\Stats\Schemas;

use App\Support\Locale;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class StatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Content')
                    ->columnSpan(2)
                    ->components([
                        TextInput::make('value')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('15k+')
                            ->helperText('Language-neutral figure, e.g. "15k+", "10", "40+".'),

                        Tabs::make('Translations')->tabs(
                            collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                                ->schema([
                                    TextInput::make("label.{$code}")
                                        ->label('Label')
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(100)
                                        ->placeholder('Patients treated'),
                                ]))->all(),
                        ),
                    ]),

                Section::make('Display')
                    ->columnSpan(1)
                    ->components([
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
