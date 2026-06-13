<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use App\Support\Locale;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Quote')
                    ->columnSpan(2)
                    ->components([
                        Tabs::make('Translations')->tabs(
                            collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                                ->schema([
                                    Textarea::make("quote.{$code}")
                                        ->label('Quote')
                                        ->rows(3)
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(500),
                                    TextInput::make("author_meta.{$code}")
                                        ->label('Author meta')
                                        ->maxLength(80)
                                        ->placeholder('FUE · Germany'),
                                ]))->all(),
                        ),
                    ]),

                Section::make('Author & display')
                    ->columnSpan(1)
                    ->components([
                        TextInput::make('author_name')
                            ->required()
                            ->maxLength(120),

                        FileUpload::make('avatar')
                            ->image()
                            ->avatar()
                            ->disk('r2')
                            ->directory('testimonials')
                            ->visibility('private')
                            ->maxSize(2048),

                        Select::make('rating')
                            ->options([5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★'])
                            ->default(5)
                            ->required()
                            ->native(false),

                        Toggle::make('is_featured')
                            ->label('Featured (gradient card)'),

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
