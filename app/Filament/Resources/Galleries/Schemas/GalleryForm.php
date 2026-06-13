<?php

namespace App\Filament\Resources\Galleries\Schemas;

use App\Models\Gallery;
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

class GalleryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Gallery')
                    ->columnSpan(2)
                    ->components([
                        Tabs::make('Translations')->tabs(
                            collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                                ->schema([
                                    TextInput::make("title.{$code}")
                                        ->label('Title')
                                        ->required($code === Locale::DEFAULT)
                                        ->maxLength(150),
                                    Textarea::make("description.{$code}")
                                        ->label('Description')
                                        ->rows(2)
                                        ->maxLength(400),
                                ]))->all(),
                        ),

                        FileUpload::make('images')
                            ->label('Images')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->panelLayout('grid')
                            ->maxFiles(20)
                            ->disk('r2')
                            ->directory('galleries')
                            ->visibility('private')
                            ->maxSize(4096)
                            ->helperText('Drag to reorder. Shown as a slider or grid depending on the layout setting.'),
                    ]),

                Section::make('Display')
                    ->columnSpan(1)
                    ->components([
                        Select::make('layout')
                            ->options(Gallery::LAYOUTS)
                            ->default('grid')
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
