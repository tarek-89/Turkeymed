<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Resources\Redirects\Pages\ListRedirects;
use App\Models\Redirect;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'from_path';

    /**
     * Badge: the number of redirects still awaiting a destination (inactive).
     */
    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::query()->where('is_active', false)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('from_path')
                ->label('Old path')
                ->required()
                ->maxLength(500)
                ->unique(ignoreRecord: true)
                ->helperText('The old URL path, without host or surrounding slashes — e.g. "category/hair-transplant".'),

            TextInput::make('to_path')
                ->label('Destination')
                ->required()
                ->maxLength(500)
                ->helperText('A site path ("/hair-transplant-surgery") or a full URL.'),

            Select::make('status_code')
                ->label('Type')
                ->options([
                    301 => '301 — Permanent',
                    302 => '302 — Temporary',
                ])
                ->default(301)
                ->required()
                ->native(false),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->helperText('Inactive redirects are ignored until you switch them on.'),

            TextInput::make('source')
                ->maxLength(50)
                ->helperText('Where this redirect came from (e.g. "wp-import"). Optional.'),

            Textarea::make('notes')
                ->rows(2)
                ->maxLength(500)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('is_active')
            ->columns([
                TextColumn::make('from_path')
                    ->label('Old path')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->copyable(),

                TextColumn::make('to_path')
                    ->label('Destination')
                    ->searchable()
                    ->limit(50)
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('status_code')
                    ->label('Type')
                    ->badge()
                    ->color(fn (int $state): string => $state === 301 ? 'success' : 'warning')
                    ->formatStateUsing(fn (int $state): string => $state === 301 ? '301' : '302'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('hits')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('last_hit_at')
                    ->label('Last hit')
                    ->dateTime('M j, Y')
                    ->placeholder('Never')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('source')
                    ->badge()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('notes')
                    ->limit(40)
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),

                SelectFilter::make('status_code')
                    ->label('Type')
                    ->options([
                        301 => '301 — Permanent',
                        302 => '302 — Temporary',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('gray')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRedirects::route('/'),
        ];
    }
}
