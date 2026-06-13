@props([
    /** @var array<int, array{label: string, href?: string|null}> Last item (no href) is the current page. */
    'items' => [],
])

@php
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($items)->values()->map(fn (array $item, int $index) => array_filter([
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['label'],
            'item' => $item['href'] ?? null,
        ]))->all(),
    ];
@endphp

<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

<nav aria-label="{{ __('common.breadcrumb_label') }}" {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2 text-sm text-muted']) }}>
    @foreach ($items as $item)
        @if (! $loop->first)
            <span aria-hidden="true" class="text-muted/60">/</span>
        @endif

        @if (! empty($item['href']) && ! $loop->last)
            <a href="{{ $item['href'] }}" class="font-semibold text-cyan-800 hover:text-cyan-900">{{ $item['label'] }}</a>
        @else
            <span aria-current="page" class="font-semibold text-ink">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
