<?php

namespace App\Filament\Resources\Promises\Schemas;

use App\Models\Promise;
use App\Support\Locale;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PromiseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Content')
                    ->columnSpan(2)
                    ->components([
                        Tabs::make('Translations')->tabs(
                            collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                                ->schema([
                                    TextInput::make("title.{$code}")
                                        ->label('Title')
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(100),

                                    Textarea::make("text.{$code}")
                                        ->label('Text')
                                        ->rows(3)
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(300),
                                ]))->all(),
                        ),
                    ]),

                Section::make('Display')
                    ->columnSpan(1)
                    ->components([
                        Select::make('icon')
                            ->options(array_combine(Promise::ICONS, array_map('ucfirst', Promise::ICONS)))
                            ->default('shield')
                            ->required()
                            ->native(false),

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
