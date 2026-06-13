@props([
    'tight' => false,
    'container' => true,
    'as' => 'section',
])

@php
    $padding = $tight ? 'py-[clamp(28px,5vw,52px)]' : 'py-[clamp(40px,7vw,80px)]';
@endphp

<{{ $as }} {{ $attributes->merge(['class' => $padding]) }}>
    @if ($container)
        <x-ui.container>{{ $slot }}</x-ui.container>
    @else
        {{ $slot }}
    @endif
</{{ $as }}>
