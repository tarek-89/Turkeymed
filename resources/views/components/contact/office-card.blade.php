@props([
    /** @var \App\Models\Office */
    'office',
])

<div {{ $attributes->merge(['class' => 'flex flex-col rounded-xl border border-line bg-white p-6 shadow-md']) }}>
    <div class="flex items-start justify-between gap-3">
        <h4 class="text-lg font-bold text-ink">{{ $office->translate('name') }}</h4>

        @if ($badge = $office->translate('badge'))
            <x-ui.badge variant="soft" class="flex-none">{{ $badge }}</x-ui.badge>
        @endif
    </div>

    @if ($address = $office->translate('address'))
        <p class="mt-2 text-sm leading-relaxed text-muted">{!! nl2br(e($address)) !!}</p>
    @endif

    <div class="mt-4 space-y-2 text-sm">
        @if ($office->phone)
            <p class="flex items-center gap-2 font-semibold text-ink">
                <x-ui.icon name="phone" class="h-4 w-4 flex-none text-cyan-800" />
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $office->phone) }}" class="hover:text-cyan-800">{{ $office->phone }}</a>
            </p>
        @endif

        @if ($hours = $office->translate('hours'))
            <p class="flex items-center gap-2 text-muted">
                <x-ui.icon name="clock" class="h-4 w-4 flex-none text-muted" />
                {{ $hours }}
            </p>
        @endif
    </div>

    @if ($office->directions_url || $office->phone)
        <div class="mt-5 flex flex-wrap gap-2.5">
            @if ($office->directions_url)
                <x-ui.button :href="$office->directions_url" variant="primary" size="sm" target="_blank" rel="noopener noreferrer">
                    {{ __('contact.get_directions') }}
                </x-ui.button>
            @endif
            @if ($office->phone)
                <x-ui.button :href="'tel:'.preg_replace('/[^0-9+]/', '', $office->phone)" variant="secondary" size="sm">
                    {{ __('contact.call') }}
                </x-ui.button>
            @endif
        </div>
    @endif
</div>
