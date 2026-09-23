<?php

namespace App\Filament\Resources\CostPages\RelationManagers;

use App\Filament\Support\TranslatedTabs;
use App\Models\CostPageSection;
use App\Support\Cost\SectionKey;
use App\Support\Locale;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * The page's sections: show/hide, drag to reorder, and edit the headings plus
 * each section's own text fields and bullet lists. The set of sections is
 * fixed (see SectionKey), so rows can't be created or deleted here.
 */
class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $title = 'Sections';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Toggle::make('is_visible')
                    ->label('Show this section on the page'),

                TranslatedTabs::make('Heading', fn (string $code, bool $isDefault): array => [
                    TextInput::make("eyebrow.{$code}")
                        ->label('Eyebrow')
                        ->maxLength(120),
                    TextInput::make("title.{$code}")
                        ->label('Title')
                        ->maxLength(200),
                    Textarea::make("lead.{$code}")
                        ->label('Intro text')
                        ->rows(3)
                        ->maxLength(600),
                ]),

                ...self::sectionSpecificFields(),
            ]);
    }

    /**
     * One collapsible block per section type, shown only when editing that
     * type. Fields come from SectionKey so each phase only extends the enum.
     *
     * @return list<Component>
     */
    private static function sectionSpecificFields(): array
    {
        $blocks = [];

        foreach (SectionKey::cases() as $key) {
            $fields = $key->contentFields();
            $groups = $key->itemGroups();

            if ($fields === [] && $groups === [] && $key !== SectionKey::Timeline) {
                continue;
            }

            $components = [];

            if ($fields !== []) {
                $components[] = TranslatedTabs::make('Texts', function (string $code) use ($fields): array {
                    $inputs = [];

                    foreach ($fields as $name => $field) {
                        $path = "content.{$code}.{$name}";

                        $input = match ($field['type']) {
                            'textarea' => Textarea::make($path)->rows(3),
                            'url' => TextInput::make($path)->url()->maxLength(500),
                            'number' => TextInput::make($path)->numeric()->minValue(0),
                            default => TextInput::make($path)->maxLength(300),
                        };

                        $inputs[] = $input
                            ->label($field['label'])
                            ->helperText($field['help'] ?? null);
                    }

                    return $inputs;
                });
            }

            if ($key === SectionKey::Timeline) {
                $components[] = Repeater::make('content.curve_points')
                    ->label('Extra curve points')
                    ->helperText('Optional. The curve passes through every recovery stage; add points here to shape it in between (e.g. the dip during shedding).')
                    ->columns(2)
                    ->reorderableWithButtons()
                    ->addActionLabel('Add point')
                    ->defaultItems(0)
                    ->schema([
                        TextInput::make('month')->numeric()->step(0.1)->minValue(0)->maxValue(24)->required(),
                        TextInput::make('percent')->numeric()->minValue(0)->maxValue(100)->required()->suffix('%'),
                    ]);
            }

            foreach ($groups as $group => $meta) {
                $components[] = Repeater::make("items_{$group}")
                    ->label($meta['label'])
                    ->relationship('items', fn (Builder $query): Builder => $query->where('group', $group))
                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => $data + ['group' => $group])
                    ->orderColumn('sort_order')
                    ->reorderableWithDragAndDrop()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['title'][Locale::DEFAULT] ?? null)
                    ->addActionLabel('Add item')
                    ->defaultItems(0)
                    ->schema([
                        TranslatedTabs::make('Item', fn (string $code, bool $isDefault): array => array_values(array_filter([
                            TextInput::make("title.{$code}")
                                ->label('Text')
                                ->required($isDefault)
                                ->maxLength(200),
                            $meta['has_body']
                                ? Textarea::make("body.{$code}")->label('Detail')->rows(2)->maxLength(400)
                                : null,
                        ]))),
                        Toggle::make('is_published')->label('Published')->default(true),
                    ]);
            }

            $blocks[] = Section::make($key->label())
                ->description('Fields specific to this section.')
                ->components($components)
                ->visible(fn (?CostPageSection $record): bool => $record?->key === $key->value);
        }

        return $blocks;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated(false)
            ->columns([
                TextColumn::make('key')
                    ->label('Section')
                    ->state(fn (CostPageSection $record): string => $record->sectionKey()?->label() ?? $record->key)
                    ->weight('bold'),

                TextColumn::make('title')
                    ->state(fn (CostPageSection $record): ?string => $record->translate('title', Locale::DEFAULT))
                    ->placeholder('—')
                    ->limit(60),

                ToggleColumn::make('is_visible')
                    ->label('Visible'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::FourExtraLarge),
            ])
            ->toolbarActions([]);
    }
}
