{{--
    Service page two-column layout: main article + sticky sidebar.
    Slots: default = article content, aside = sidebar cards.
--}}
<div {{ $attributes->merge(['class' => 'grid items-start gap-9 lg:grid-cols-[minmax(0,1fr)_340px]']) }}>
    <article class="min-w-0">{{ $slot }}</article>

    @isset($aside)
        <aside class="grid gap-5 lg:sticky lg:top-[90px]">{{ $aside }}</aside>
    @endisset
</div>
