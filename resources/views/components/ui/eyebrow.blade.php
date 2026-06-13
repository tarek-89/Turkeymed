@props(['as' => 'span'])

<{{ $as }} {{ $attributes->merge(['class' => 'text-xs font-bold uppercase tracking-[0.12em] text-cyan-700']) }}>{{ $slot }}</{{ $as }}>
