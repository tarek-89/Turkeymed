<?php

namespace App\Filament\Resources\Videos\Schemas;

use App\Models\Video;
use App\Support\Locale;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Video')
                    ->columnSpan(2)
                    ->components([
                        TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->required()
                            ->maxLength(500)
                            ->placeholder('https://www.youtube.com/watch?v=… or /shorts/…')
                            ->helperText('Paste a normal video or a Shorts link — the embed is built automatically.'),

                        Tabs::make('Translations')->tabs(
                            collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                                ->schema([
                                    TextInput::make("title.{$code}")
                                        ->label('Title (optional)')
                                        ->maxLength(150),
                                ]))->all(),
                        ),
                    ]),

                Section::make('Display')
                    ->columnSpan(1)
                    ->components([
                        Select::make('kind')
                            ->options(Video::KINDS)
                            ->default('video')
                            ->required()
                            ->native(false),

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
