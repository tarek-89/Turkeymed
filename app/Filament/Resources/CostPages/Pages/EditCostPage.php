<?php

namespace App\Filament\Resources\CostPages\Pages;

use App\Filament\Resources\CostPages\CostPageResource;
use App\Models\CostPage;
use App\Support\Locale;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditCostPage extends EditRecord
{
    protected static string $resource = CostPageResource::class;

    protected function getHeaderActions(): array
    {
        /** @var CostPage $record */
        $record = $this->getRecord();

        return [
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn (): string => $record->url(Locale::DEFAULT))
                ->openUrlInNewTab(),

            Action::make('repairSections')
                ->label('Add missing sections')
                ->icon(Heroicon::OutlinedWrench)
                ->color('gray')
                ->action(function () use ($record): void {
                    $record->ensureSections();

                    Notification::make()->title('Sections checked')->success()->send();
                }),

            DeleteAction::make(),
        ];
    }
}
