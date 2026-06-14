<?php

namespace App\Filament\Resources\Authors;

use App\Filament\Resources\Authors\Pages\ListAuthors;
use App\Models\Author;
use App\Support\Locale;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Author')
                ->columns(2)
                ->components([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(150)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, ?string $state, Set $set, Get $get): void {
                            if ($operation === 'create' && blank($get('slug'))) {
                                $set('slug', Str::slug($state ?? ''));
                            }
                        }),

                    TextInput::make('slug')
                        ->required()
                        ->maxLength(150)
                        ->unique(ignoreRecord: true),

                    TextInput::make('credentials')
                        ->maxLength(150)
                        ->placeholder('MD, Hair Transplant Surgeon')
                        ->helperText('Real, verifiable qualifications only.'),

                    TextInput::make('title')
                        ->label('Role / position')
                        ->maxLength(150),

                    FileUpload::make('photo')
                        ->image()
                        ->disk('r2')
                        ->directory('authors')
                        ->visibility('private')
                        ->maxSize(2048)
                        ->columnSpanFull(),

                    TagsInput::make('same_as')
                        ->label('Profile links')
                        ->placeholder('https://linkedin.com/in/...')
                        ->helperText('Press Enter after each URL. Used for the Person sameAs in structured data.')
                        ->columnSpanFull(),

                    Toggle::make('is_published')
                        ->label('Published')
                        ->default(true),

                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                ]),

            Section::make('Biography')
                ->description('A short professional bio per language. The default language is required.')
                ->components([
                    Tabs::make('Bio translations')->tabs(
                        collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                            ->schema([
                                Textarea::make("bio.{$code}")
                                    ->label('Bio')
                                    ->rows(4)
                                    ->required($code === Locale::DEFAULT),
                            ]))->all(),
                    ),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Author $record): ?string => $record->credentials),

                TextColumn::make('slug')
                    ->color('gray'),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuthors::route('/'),
        ];
    }
}
