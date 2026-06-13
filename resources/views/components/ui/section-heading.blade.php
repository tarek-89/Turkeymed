@props([
    'eyebrow' => null,
    'title' => null,
    'level' => 'h2',
    'align' => 'between', // 'between' (heading left, optional action right) or 'center'
    'id' => null,
])

@php
    $wrapper = $align === 'center'
        ? 'mx-auto mb-8 max-w-[580px] text-center'
        : 'mb-6 flex flex-wrap items-end justify-between gap-4';
@endphp

<div {{ $attributes->merge(['class' => $wrapper]) }}>
    <div>
        @if ($eyebrow)
            <x-ui.eyebrow class="mb-2.5 block">{{ $eyebrow }}</x-ui.eyebrow>
        @endif

        @if ($title)
            <x-ui.heading :level="$level" :id="$id">{{ $title }}</x-ui.heading>
        @endif

        @if ($slot->isNotEmpty())
            <div class="mt-3 lead">{{ $slot }}</div>
        @endif
    </div>

    @isset($action)
        <div class="flex-none">{{ $action }}</div>
    @endisset
</div>
