<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * "Add translation" — links the source post into a translation group (creating
     * one if needed) and opens the create form pre-filled with the group, target
     * language, and source body to translate.
     */
    public static function addTranslationAction(): Action
    {
        return Action::make('addTranslation')
            ->label('Add translation')
            ->icon(Heroicon::OutlinedLanguage)
            ->schema([
                Select::make('language')
                    ->label('Translate to')
                    ->options(function (Post $record): array {
                        $taken = $record->translation_group_id
                            ? Post::query()
                                ->where('translation_group_id', $record->translation_group_id)
                                ->pluck('language')
                                ->all()
                            : [$record->language];

                        return collect(Post::languageOptions())->except($taken)->all();
                    })
                    ->required()
                    ->searchable()
                    ->native(false),
            ])
            ->action(function (Post $record, array $data) {
                if (! $record->translation_group_id) {
                    // New groups continue above the imported Polylang group ids
                    $record->update([
                        'translation_group_id' => ((int) Post::max('translation_group_id')) + 1,
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
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
