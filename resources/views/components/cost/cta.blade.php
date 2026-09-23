@props([
    /** @var \App\Models\CostPage */
    'page',
    /** @var \App\Models\CostPageSection */
    'section',
    'language',
])

{{-- Bottom call to action: the site's gradient CTA band. --}}
@if ($section->translate('title'))
    <x-ui.section id="cta">
        <x-ui.cta-banner :title="$section->translate('title')">
            {{ $section->translate('lead') }}

            <x-slot:actions>
                <x-ui.button :href="$section->text('cta_url') ?: \App\Support\Navigation::contactUrl()" variant="accent">
                    {{ $section->text('cta_label') ?: __('nav.cta_long') }}
                </x-ui.button>
                <x-ui.whatsapp-button variant="translucent" />
            </x-slot:actions>
        </x-ui.cta-banner>
    </x-ui.section>
@endif
