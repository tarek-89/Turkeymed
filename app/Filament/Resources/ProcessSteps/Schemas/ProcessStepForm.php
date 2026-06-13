<?php

namespace App\Filament\Resources\ProcessSteps\Schemas;

use App\Support\Locale;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ProcessStepForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Step')
                    ->description('The step number is set automatically from the order below.')
                    ->columnSpan(2)
                    ->components([
                        Tabs::make('Translations')->tabs(
                            collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                                ->schema([
                                    TextInput::make("title.{$code}")
                                        ->label('Title')
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(120),
                                    Textarea::make("description.{$code}")
                                        ->label('Description')
                                        ->rows(3)
                                        ->maxLength(500),
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
