<?php

namespace App\Filament\Resources\SocialLinks\Schemas;

use App\Models\SocialLink;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SocialLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('platform')
                    ->options(SocialLink::PLATFORMS)
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->helperText('Choose the network — this picks the icon. Use "Website / custom" for anything else.'),

                TextInput::make('url')
                    ->label('URL')
                    ->required()
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://instagram.com/yourpage'),

                TextInput::make('label')
                    ->label('Accessible label (optional)')
                    ->maxLength(80)
                    ->placeholder('Follow us on Instagram')
                    ->helperText('Used as the link\'s screen-reader label and tooltip. Defaults to the platform name.'),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers show first. Rows can also be drag-reordered in the list.'),

                Toggle::make('is_published')
                    ->label('Published')
                    ->default(true),
            ]);
    }
}
