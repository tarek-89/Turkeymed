@props([
    /** @var \Illuminate\Support\Collection<int, \App\Models\Service> */
    'services',
    'heading' => null,
])

@if ($services->isNotEmpty())
    <x-ui.card :class="$attributes->get('class')">
        <x-ui.eyebrow as="h3" class="mb-2">{{ $heading ?? __('services.related_heading') }}</x-ui.eyebrow>

        <nav class="divide-y divide-line" aria-label="{{ $heading ?? __('services.related_heading') }}">
            @foreach ($services as $service)
                <a href="{{ $service->url() }}" class="flex min-h-[44px] items-center justify-between gap-3 py-3 text-sm font-semibold text-ink transition duration-150 hover:text-cyan-800">
                    {{ $service->title }}
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="flex-none text-muted rtl:-scale-x-100">
                        <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </a>
            @endforeach
        </nav>
    </x-ui.card>
@endif
