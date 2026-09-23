<?php

namespace App\Filament\Resources\CostPages;

use App\Filament\Resources\CostPages\Pages\CreateCostPage;
use App\Filament\Resources\CostPages\Pages\EditCostPage;
use App\Filament\Resources\CostPages\Pages\ListCostPages;
use App\Filament\Resources\CostPages\RelationManagers\ComparisonRowsRelationManager;
use App\Filament\Resources\CostPages\RelationManagers\CountriesRelationManager;
use App\Filament\Resources\CostPages\RelationManagers\FeaturesRelationManager;
use App\Filament\Resources\CostPages\RelationManagers\PresetsRelationManager;
use App\Filament\Resources\CostPages\RelationManagers\ProcedureStepsRelationManager;
use App\Filament\Resources\CostPages\RelationManagers\RecoveryPhasesRelationManager;
use App\Filament\Resources\CostPages\RelationManagers\RecoveryStagesRelationManager;
use App\Filament\Resources\CostPages\RelationManagers\SectionsRelationManager;
use App\Filament\Resources\CostPages\RelationManagers\TechniquesRelationManager;
use App\Filament\Resources\CostPages\RelationManagers\TiersRelationManager;
use App\Filament\Resources\CostPages\RelationManagers\ZonesRelationManager;
use App\Filament\Resources\CostPages\Schemas\CostPageForm;
use App\Filament\Resources\CostPages\Tables\CostPagesTable;
use App\Models\CostPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Pricing pages ("Hair transplant cost in Turkey"): the page record plus one
 * relation manager per editable list (sections now; zones, packages,
 * countries, timeline… in later phases).
 */
class CostPageResource extends Resource
{
    protected static ?string $model = CostPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyEuro;

    protected static string|UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'pricing page';

    protected static ?string $pluralModelLabel = 'pricing pages';

    protected static ?string $navigationLabel = 'Pricing pages';

    protected static ?string $recordTitleAttribute = 'slug';

    public static function form(Schema $schema): Schema
    {
        return CostPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CostPagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SectionsRelationManager::class,
            ZonesRelationManager::class,
            PresetsRelationManager::class,
            CountriesRelationManager::class,
            TechniquesRelationManager::class,
            TiersRelationManager::class,
            FeaturesRelationManager::class,
            ComparisonRowsRelationManager::class,
            ProcedureStepsRelationManager::class,
            RecoveryStagesRelationManager::class,
            RecoveryPhasesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostPages::route('/'),
            'create' => CreateCostPage::route('/create'),
            'edit' => EditCostPage::route('/{record}/edit'),
        ];
    }
}
