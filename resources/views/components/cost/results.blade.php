@props([
    /** @var \App\Models\CostPage */
    'page',
    /** @var \App\Models\CostPageSection */
    'section',
    'language',
])

{{--
    Before / after results: the site's existing "Real results" carousel, fed
    with the published, consented results of the page's category. The
    section heading is the admin's; the carousel hides itself when empty.
--}}
@php($results = $page->patientResults())

@if ($results->isNotEmpty())
    @if ($section->translate('title'))
        <x-ui.container>
            <x-cost.section-heading :section="$section" class="mb-0 pt-8" />
        </x-ui.container>
    @endif

    <x-service.results :results="$results" />
@endif
