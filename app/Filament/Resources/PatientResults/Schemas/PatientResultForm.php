<?php

namespace App\Filament\Resources\PatientResults\Schemas;

use App\Models\Service;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PatientResultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Images')
                    ->description('Both images are cropped to 4:3 so the comparison slider aligns perfectly. Use the same framing and lighting where possible.')
                    ->columnSpan(2)
                    ->columns(2)
                    ->components([
                        FileUpload::make('before_image')
                            ->label('Before')
                            ->required()
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['4:3'])
                            ->imageCropAspectRatio('4:3')
                            ->disk('r2')
                            ->directory('results/'.now()->format('Y/m'))
                            ->visibility('private')
                            ->maxSize(4096),

                        FileUpload::make('after_image')
                            ->label('After')
                            ->required()
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['4:3'])
                            ->imageCropAspectRatio('4:3')
                            ->disk('r2')
                            ->directory('results/'.now()->format('Y/m'))
                            ->visibility('private')
                            ->maxSize(4096),

                        TextInput::make('before_label')
                            ->label('Before label override')
                            ->maxLength(100)
                            ->placeholder('Before')
                            ->helperText('Optional. Defaults to a translated "Before".'),

                        TextInput::make('after_label')
                            ->label('After label override')
                            ->maxLength(100)
                            ->placeholder('After — month 12')
                            ->helperText('Optional. Defaults to a translated "After".'),
                    ]),

                Section::make('Case details')
                    ->columnSpan(1)
                    ->components([
                        Select::make('service_category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('service_id', null))
                            ->helperText('Shown on every service page in this category.'),

                        Select::make('service_id')
                            ->label('Pin to a service (optional)')
                            ->options(fn (Get $get): array => Service::query()
                                ->when($get('service_category_id'), fn ($query, $categoryId) => $query->where('service_category_id', $categoryId))
                                ->orderBy('title')
                                ->pluck('title', 'id')
                                ->all())
                            ->searchable()
                            ->native(false)
                            ->helperText('Pinned results appear only on this service page (plus the gallery).'),

                        TextInput::make('grafts_count')
                            ->label('Grafts')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20000)
                            ->helperText('Hair procedures only — used to build the public headline.'),

                        TextInput::make('months_to_result')
                            ->label('Months to result')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60),

                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers show first. Rows can also be drag-reordered in the list.'),
                    ]),

                Section::make('Publishing')
                    ->columnSpan(1)
                    ->components([
                        Checkbox::make('consent_confirmed')
                            ->label('Written patient consent obtained')
                            ->live()
                            ->afterStateUpdated(function (?bool $state, Set $set): void {
                                if (! $state) {
                                    $set('is_published', false);
                                }
                            })
                            ->helperText('Required before this result can be published. Keep the signed consent form on file.'),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->disabled(fn (Get $get): bool => ! $get('consent_confirmed'))
                            ->helperText('Only consented results can go live — this is also enforced on the website itself.'),
                    ]),
            ]);
    }
}
