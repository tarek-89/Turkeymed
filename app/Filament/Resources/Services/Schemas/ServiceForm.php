<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\SeoAnalyzer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Content')
                    ->columnSpan(2)
                    ->components([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(500)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, Set $set, Get $get): void {
                                if ($operation === 'create' && blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(300)
                            ->live(onBlur: true)
                            ->helperText('URL path of the service. Do not change on migrated pages — it preserves SEO.')
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('language', $get('language')),
                            ),

                        RichEditor::make('body')
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->fileAttachmentsDisk('r2')
                            ->fileAttachmentsDirectory(now()->format('Y/m'))
                            ->resizableImages()
                            ->default(function (): ?string {
                                $sourceId = request()->integer('from');

                                return $sourceId ? Service::find($sourceId)?->body : null;
                            }),

                        Textarea::make('excerpt')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Optional summary, also used as a fallback meta description.'),
                    ]),

                Section::make('Publishing')
                    ->columnSpan(1)
                    ->components([
                        Select::make('status')
                            ->options([
                                'publish' => 'Published',
                                'draft' => 'Draft',
                            ])
                            ->default('draft')
                            ->required()
                            ->native(false),

                        DateTimePicker::make('published_at')
                            ->default(now()),

                        Select::make('service_category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->createOptionForm([
                                TextInput::make('name')->required()->maxLength(200),
                                TextInput::make('slug')->required()->maxLength(200),
                                TextInput::make('sort_order')->numeric()->default(0),
                            ]),

                        Select::make('language')
                            ->options(fn (): array => Post::languageOptions())
                            ->default(fn (): string => request()->query('lang', Post::DEFAULT_LANGUAGE))
                            ->required()
                            ->searchable()
                            ->native(false),

                        TextInput::make('author'),

                        Hidden::make('translation_group_id')
                            ->default(fn (): ?int => request()->filled('group') ? request()->integer('group') : null),
                    ]),

                Section::make('Featured image')
                    ->columnSpan(1)
                    ->components([
                        FileUpload::make('featured_image')
                            ->hiddenLabel()
                            ->image()
                            ->disk('r2')
                            ->directory(now()->format('Y/m'))
                            ->visibility('private')
                            ->maxSize(4096),
                    ]),

                Section::make('SEO')
                    ->columnSpanFull()
                    ->columns(2)
                    ->components([
                        Group::make([
                            TextInput::make('meta_title')
                                ->maxLength(500)
                                ->live(debounce: 600)
                                ->helperText(fn (?string $state): string => mb_strlen($state ?? '').' / 60 characters. Leave empty to use "Title - '.config('app.name').'".'),

                            Textarea::make('meta_description')
                                ->rows(3)
                                ->maxLength(1000)
                                ->live(debounce: 600)
                                ->helperText(fn (?string $state): string => mb_strlen($state ?? '').' / 160 characters.'),

                            TextInput::make('focus_keyword')
                                ->live(debounce: 600)
                                ->helperText('The main search term this service page should rank for.'),
                        ]),

                        Placeholder::make('seo_analysis')
                            ->hiddenLabel()
                            ->content(function (Get $get): \Illuminate\Contracts\View\View {
                                $analysis = SeoAnalyzer::analyze(
                                    title: $get('title'),
                                    metaTitle: $get('meta_title'),
                                    metaDescription: $get('meta_description'),
                                    slug: $get('slug'),
                                    language: $get('language'),
                                    body: $get('body'),
                                    keyword: $get('focus_keyword'),
                                );

                                return view('filament.seo-analysis', $analysis);
                            }),
                    ]),

                Section::make('Migration info')
                    ->columnSpan(1)
                    ->collapsible()
                    ->collapsed()
                    ->visibleOn('edit')
                    ->components([
                        TextInput::make('wp_post_id')
                            ->disabled()
                            ->dehydrated(false),

                        Toggle::make('is_elementor')
                            ->disabled()
                            ->dehydrated(false),

                        DateTimePicker::make('wp_modified_at')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
