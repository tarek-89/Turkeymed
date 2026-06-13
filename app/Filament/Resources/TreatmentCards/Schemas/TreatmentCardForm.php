<?php

namespace App\Filament\Resources\TreatmentCards\Schemas;

use App\Models\Promise;
use App\Models\TreatmentCard;
use App\Support\Locale;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class TreatmentCardForm
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
                                        ->maxLength(120),
                                    Textarea::make("description.{$code}")
                                        ->label('Description')
                                        ->rows(2)
                                        ->maxLength(300),
                                    TextInput::make("badge.{$code}")
                                        ->label('Badge (optional)')
                                        ->maxLength(40)
                                        ->placeholder('Most popular'),
                                    TextInput::make("footnote.{$code}")
                                        ->label('Footnote (optional)')
                                        ->maxLength(60)
                                        ->placeholder('From €1,500'),
                                ]))->all(),
                        ),
                    ]),

                Section::make('Display')
                    ->columnSpan(1)
                    ->components([
                        Select::make('variant')
                            ->options(TreatmentCard::VARIANTS)
                            ->default('default')
                            ->required()
                            ->native(false),

                        Select::make('icon')
                            ->options(array_combine(Promise::ICONS, array_map('ucfirst', Promise::ICONS)))
                            ->native(false)
                            ->helperText('Optional. Not shown on the call-to-action variant.'),

                        TextInput::make('url')
                            ->label('Link URL')
                            ->maxLength(500)
                            ->placeholder('/category/hair-transplant-surgery'),

                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true),
                    ]),
            ]);
    }
}
