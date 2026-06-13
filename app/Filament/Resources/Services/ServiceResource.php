<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Filament\Resources\Services\Tables\ServicesTable;
use App\Models\Service;
use BackedEnum;
use Filament\Actions\Action;
use UnitEnum;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * "Add translation" — same pattern as PostResource.
     */
    public static function addTranslationAction(): Action
    {
        return Action::make('addTranslation')
            ->label('Add translation')
            ->icon(Heroicon::OutlinedLanguage)
            ->schema([
                Select::make('language')
                    ->label('Translate to')
                    ->options(function (Service $record): array {
                        $taken = $record->translation_group_id
                            ? Service::query()
                                ->where('translation_group_id', $record->translation_group_id)
                                ->pluck('language')
                                ->all()
                            : [$record->language];

                        return collect(\App\Models\Post::languageOptions())->except($taken)->all();
                    })
                    ->required()
                    ->searchable()
                    ->native(false),
            ])
            ->action(function (Service $record, array $data) {
                if (! $record->translation_group_id) {
                    $record->update([
                        'translation_group_id' => ((int) Service::max('translation_group_id')) + 1,
                    ]);
                }

                return redirect(static::getUrl('create', [
                    'group' => $record->translation_group_id,
                    'lang' => $data['language'],
                    'from' => $record->getKey(),
                ]));
            });
    }

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
