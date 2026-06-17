<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account')
                ->columns(2)
                ->components([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, ?string $state, Set $set, Get $get): void {
                            if ($operation === 'create' && blank($get('slug'))) {
                                $set('slug', Str::slug($state ?? ''));
                            }
                        }),

                    TextInput::make('email')
                        ->label('Email address')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->maxLength(255)
                        // Hashed by the model's 'password' => 'hashed' cast.
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->helperText('Leave blank to keep the current password when editing.'),
                ]),

            Section::make('Author profile')
                ->description('Public, credentialed byline shown on posts and services. Publish to expose the /authors profile page.')
                ->columns(2)
                ->components([
                    TextInput::make('slug')
                        ->maxLength(150)
                        ->unique(ignoreRecord: true)
                        ->helperText('URL path of the public profile: /authors/{slug}.'),

                    TextInput::make('credentials')
                        ->maxLength(150)
                        ->placeholder('MD, Hair Transplant Surgeon')
                        ->helperText('Real, verifiable qualifications only.'),

                    TextInput::make('title')
                        ->label('Role / position')
                        ->maxLength(150),

                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),

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
                        ->label('Published author profile')
                        ->default(false),
                ]),

            Section::make('Biography')
                ->description('A short professional bio per language, shown on the author profile page.')
                ->components([
                    Tabs::make('Bio translations')->tabs(
                        collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                            ->schema([
                                Textarea::make("bio.{$code}")
                                    ->label('Bio')
                                    ->rows(4),
                            ]))->all(),
                    ),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record): ?string => $record->credentials),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                IconColumn::make('is_published')
                    ->label('Author')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
        ];
    }
}
