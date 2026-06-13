<?php

namespace App\Filament\Resources\Promises;

use App\Filament\Resources\Promises\Pages\CreatePromise;
use App\Filament\Resources\Promises\Pages\EditPromise;
use App\Filament\Resources\Promises\Pages\ListPromises;
use App\Filament\Resources\Promises\Schemas\PromiseForm;
use App\Filament\Resources\Promises\Tables\PromisesTable;
use App\Models\Promise;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PromiseResource extends Resource
{
    protected static ?string $model = Promise::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Components';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'promise';

    protected static ?string $navigationLabel = 'What we stand for';

    public static function form(Schema $schema): Schema
    {
        return PromiseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromisesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromises::route('/'),
            'create' => CreatePromise::route('/create'),
            'edit' => EditPromise::route('/{record}/edit'),
        ];
    }
}
