@props([
    /** @var \App\Models\CostPage */
    'page',
    /** @var \App\Models\CostPageSection */
    'section',
    'language',
])

{{--
    "Turkey vs abroad" table. When the graft-promise section is visible it is
    rendered here, beside the table, so the two share one band as designed
    (x-cost.promise then renders nothing on its own).
--}}
@php
    $rows = $page->comparisonRows->where('is_published', true)->values();
    $promise = $page->section(\App\Support\Cost\SectionKey::Promise);
    $withPromise = $promise?->is_visible ?? false;
@endphp

@if ($rows->isNotEmpty())
    <x-ui.section id="versus" class="border-y border-line bg-white">
        <div @class(['grid gap-6', 'lg:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] lg:items-start' => $withPromise])>
            <div class="min-w-0">
                <x-cost.section-heading :section="$section" />

                <x-ui.card class="overflow-hidden p-2">
                    <div class="overflow-x-auto">
                        <table class="vs-table">
                            <thead>
                                <tr>
                                    <th scope="col"><span class="sr-only">{{ __('cost.versus.criteria') }}</span></th>
                                    <th scope="col" class="is-ours">{{ $section->text('ours_header') ?: __('cost.compare.turkey_label') }}</th>
                                    <th scope="col">{{ $section->text('theirs_header') ?: __('cost.versus.abroad') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    <tr>
                                        <th scope="row">{{ $row->translate('label', $language) }}</th>
                                        <td class="is-ours">{{ $row->translate('ours', $language) }}</td>
                                        <td>{{ $row->translate('theirs', $language) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>

                @if ($section->text('footnote'))
                    <p class="mt-3 text-xs text-muted">{{ $section->text('footnote') }}</p>
                @endif
            </div>

            @if ($withPromise)
                <div class="min-w-0">
                    <x-cost.partials.promise :section="$promise" :language="$language" />
                </div>
            @endif
        </div>
    </x-ui.section>
@endif
