<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Service $record */
        $record = $this->getRecord();

        $translationLinks = $record->translations()
            ->sortBy('language')
            ->map(fn (Service $translation): Action => Action::make('translation_'.$translation->language)
                ->label(strtoupper($translation->language).' — '.str($translation->title)->limit(30))
                ->icon(Heroicon::OutlinedPencilSquare)
                ->url(ServiceResource::getUrl('edit', ['record' => $translation])))
            ->values()
            ->all();

        return array_filter([
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn (): string => $record->url())
                ->openUrlInNewTab(),

            Action::make('editSource')
                ->label('Edit HTML')
                ->icon(Heroicon::OutlinedCodeBracket)
                ->color('gray')
                ->modalWidth(Width::SevenExtraLarge)
                ->modalHeading('Edit HTML Source')
                ->modalDescription('Edit the raw HTML of the body content. Remove unwanted elements, fix image URLs, etc.')
                ->fillForm(fn (): array => [
                    'body_html' => $record->body,
                ])
                ->schema([
                    Textarea::make('body_html')
                        ->hiddenLabel()
                        ->rows(30)
                        ->extraAttributes(['class' => 'font-mono text-xs']),
                ])
                ->action(function (array $data) use ($record): void {
                    $record->update(['body' => $data['body_html']]);
                    $this->refreshFormData(['body']);
                }),

            $translationLinks !== []
                ? ActionGroup::make($translationLinks)
                    ->label('Translations')
                    ->icon(Heroicon::OutlinedLanguage)
                    ->button()
                : null,

            ServiceResource::addTranslationAction(),

            DeleteAction::make(),
        ]);
    }
}
