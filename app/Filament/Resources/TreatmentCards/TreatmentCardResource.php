<?php

namespace App\Filament\Resources\TreatmentCards;

use App\Filament\Resources\TreatmentCards\Pages\CreateTreatmentCard;
use App\Filament\Resources\TreatmentCards\Pages\EditTreatmentCard;
use App\Filament\Resources\TreatmentCards\Pages\ListTreatmentCards;
use App\Filament\Resources\TreatmentCards\Schemas\TreatmentCardForm;
use App\Filament\Resources\TreatmentCards\Tables\TreatmentCardsTable;
use App\Models\TreatmentCard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TreatmentCardResource extends Resource
{
    protected static ?string $model = TreatmentCard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Components';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'treatment card';

    protected static ?string $navigationLabel = 'Treatments';

    public static function form(Schema $schema): Schema
    {
        return TreatmentCardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TreatmentCardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTreatmentCards::route('/'),
            'create' => CreateTreatmentCard::route('/create'),
            'edit' => EditTreatmentCard::route('/{record}/edit'),
        ];
    }
}
