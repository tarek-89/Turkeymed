@props([
    'title' => null,
    'text' => null,
])

<x-ui.card variant="gradient" :class="$attributes->get('class')">
    <h3 class="text-lg font-bold text-white">{{ $title ?? __('services.consultation_title') }}</h3>
    <p class="mt-1.5 text-sm leading-relaxed text-white/80">{{ $text ?? __('services.consultation_text') }}</p>

    <div class="mt-4 grid gap-2.5">
        <x-ui.button :href="\App\Support\Navigation::contactUrl()" variant="accent" :block="true">
            {{ __('services.consultation_cta') }}
        </x-ui.button>
        <x-ui.whatsapp-button variant="translucent" :block="true" :label="__('common.whatsapp_aria')" />
    </div>
</x-ui.card>
