<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Post $record */
        $record = $this->getRecord();

        $translationLinks = $record->translations()
            ->sortBy('language')
            ->map(fn (Post $translation): Action => Action::make('translation_'.$translation->language)
                ->label(strtoupper($translation->language).' — '.str($translation->title)->limit(30))
                ->icon(Heroicon::OutlinedPencilSquare)
                ->url(PostResource::getUrl('edit', ['record' => $translation])))
            ->values()
            ->all();

        return array_filter([
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn (): string => $record->url())
                ->openUrlInNewTab(),

            $translationLinks !== []
                ? ActionGroup::make($translationLinks)
                    ->label('Translations')
                    ->icon(Heroicon::OutlinedLanguage)
                    ->button()
                : null,

            PostResource::addTranslationAction(),

            DeleteAction::make(),
        ]);
    }
}
