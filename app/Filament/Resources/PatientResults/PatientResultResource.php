<?php

namespace App\Filament\Resources\PatientResults;

use App\Filament\Resources\PatientResults\Pages\CreatePatientResult;
use App\Filament\Resources\PatientResults\Pages\EditPatientResult;
use App\Filament\Resources\PatientResults\Pages\ListPatientResults;
use App\Filament\Resources\PatientResults\Schemas\PatientResultForm;
use App\Filament\Resources\PatientResults\Tables\PatientResultsTable;
use App\Models\PatientResult;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PatientResultResource extends Resource
{
    protected static ?string $model = PatientResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Components';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'patient result';

    protected static ?string $pluralModelLabel = 'patient results';

    public static function form(Schema $schema): Schema
    {
        return PatientResultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatientResultsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPatientResults::route('/'),
            'create' => CreatePatientResult::route('/create'),
            'edit' => EditPatientResult::route('/{record}/edit'),
        ];
    }
}
