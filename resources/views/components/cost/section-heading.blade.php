@props([
    /** @var \App\Models\CostPageSection */
    'section',
    'level' => 'h2',
    'align' => 'between',
])

{{-- Section heading from the admin-editable eyebrow / title / lead. --}}
@if ($section->translate('title') || $section->translate('eyebrow'))
    <x-ui.section-heading
        :eyebrow="$section->translate('eyebrow')"
        :title="$section->translate('title')"
        :level="$level"
        :align="$align"
        {{ $attributes }}
    >
        @if ($section->translate('lead'))
            {{ $section->translate('lead') }}
        @endif

        @isset($action)
            <x-slot:action>{{ $action }}</x-slot:action>
        @endisset
    </x-ui.section-heading>
@endif
