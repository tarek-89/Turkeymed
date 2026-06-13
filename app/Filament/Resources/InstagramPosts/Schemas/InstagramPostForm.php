<?php

namespace App\Filament\Resources\InstagramPosts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InstagramPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Post')
                    ->description('On Instagram, open a post → "…" menu → Embed → Copy embed code, and paste it below. Works for both photo and video posts.')
                    ->components([
                        Textarea::make('embed_code')
                            ->label('Instagram embed code')
                            ->required()
                            ->rows(6)
                            ->extraAttributes(['class' => 'font-mono text-xs']),

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
