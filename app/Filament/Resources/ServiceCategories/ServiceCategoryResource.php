<?php

namespace App\Filament\Resources\ServiceCategories;

use App\Filament\Resources\ServiceCategories\Pages\ListServiceCategories;
use App\Models\ServiceCategory;
use App\Support\Locale;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class ServiceCategoryResource extends Resource
{
    protected static ?string $model = ServiceCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Translations')
                ->tabs(
                    collect(Locale::codes())->map(fn (string $code): Tab => Tab::make(strtoupper($code))
                        ->schema([
                            TextInput::make("name.{$code}")
                                ->label('Name')
                                ->required($code === Locale::DEFAULT)
                                ->maxLength(200)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $operation, ?string $state, Get $get, Set $set) use ($code): void {
                                    if ($operation === 'create' && $code === Locale::DEFAULT && blank($get('slug'))) {
                                        $set('slug', Str::slug($state ?? ''));
                                    }
                                }),
                        ]))->all(),
                ),

            TextInput::make('slug')
                ->required()
                ->maxLength(200)
                ->unique(ignoreRecord: true),

            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->helperText('Lower numbers show first in the header menu. Rows can also be drag-reordered in the list.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->state(fn (ServiceCategory $record): ?string => $record->translate('name', 'en'))
                    ->searchable(query: fn ($query, string $search) => $query->where('name', 'like', "%{$search}%")),

                TextColumn::make('slug')
                    ->color('gray'),

                TextColumn::make('services_count')
                    ->label('Services')
                    ->counts('services')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->recordActions([
                Action::make('moveUp')
                    ->label('Move up')
                    ->icon(Heroicon::OutlinedChevronUp)
                    ->iconButton()
                    ->color('gray')
                    ->action(fn (ServiceCategory $record) => $record->moveBy(-1)),
                Action::make('moveDown')
                    ->label('Move down')
                    ->icon(Heroicon::OutlinedChevronDown)
                    ->iconButton()
                    ->color('gray')
                    ->action(fn (ServiceCategory $record) => $record->moveBy(1)),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceCategories::route('/'),
        ];
    }
}
