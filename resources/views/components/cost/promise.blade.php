@props([
    /** @var \App\Models\CostPage */
    'page',
    /** @var \App\Models\CostPageSection */
    'section',
    'language',
])

{{--
    Graft promise callout. Rendered inside x-cost.versus when that section is
    visible (they share one band); on its own otherwise.
--}}
@php
    $versus = $page->section(\App\Support\Cost\SectionKey::Versus);
    $insideVersus = ($versus?->is_visible ?? false) && $page->comparisonRows->where('is_published', true)->isNotEmpty();
@endphp

@unless ($insideVersus)
    <x-ui.section :tight="true" id="promise">
        <div class="mx-auto max-w-3xl">
            <x-cost.partials.promise :section="$section" :language="$language" />
        </div>
    </x-ui.section>
@endunless
