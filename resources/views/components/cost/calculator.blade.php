@props([
    /** @var \App\Models\CostPage */
    'page',
    /** @var \App\Models\CostPageSection */
    'section',
    'language',
])

{{--
    Hero + graft calculator. Everything is server-rendered from the page's
    zones, presets and settings (initial estimate included); the browser
    script only reacts to taps. Data + labels are handed over as JSON.
--}}
@php
    $calculator = $page->calculator();
    $zones = $calculator->zoneConfig($language);
    $presets = $page->presets->where('is_published', true)->values();
    $selected = collect($page->default_zone_numbers ?: [])->map(fn ($n): int => (int) $n)->intersect($zones->pluck('number'))->values()->all();
    $estimate = $calculator->estimate($selected);
    $whatsapp = preg_replace('/\D/', '', (string) config('site.whatsapp'));
    $ctaUrl = $section->text('cta_url') ?: \App\Support\Navigation::contactUrl();
    $badges = $section->itemsIn('badges');

    $config = [
        'zones' => $zones->all(),
        'hairsPerGraft' => (float) $page->hairs_per_graft,
        'sessionCap' => (int) $page->session_cap_grafts,
        'meterMax' => (int) $page->meter_max_grafts,
        'priceFrom' => $page->priceFrom(),
        'twoSessionPriceFrom' => $page->two_session_price_from,
        'durationRules' => $calculator->durationRules($language),
        'currency' => $page->currency,
        'locale' => $language,
        'whatsapp' => $whatsapp,
        'whatsappMessage' => $section->text('whatsapp_message') ?: __('cost.calculator.whatsapp_default'),
        'strings' => [
            'selectZone' => __('cost.calculator.select_zone'),
            'range' => __('cost.calculator.range'),
            'zone' => __('cost.calculator.zone'),
            'zones' => __('cost.calculator.zones'),
            'oneSession' => __('cost.calculator.one_session'),
            'twoSessions' => __('cost.calculator.two_sessions'),
            'from' => __('cost.calculator.from'),
            'hairsApprox' => __('cost.calculator.hairs_approx'),
            'mobileGrafts' => __('cost.calculator.mobile_grafts'),
            'noteSingle' => $section->text('result_note'),
            'noteTwoSessions' => $section->text('result_note_two_sessions'),
        ],
    ];
@endphp

<x-ui.section :tight="true" id="calculator">
    {{-- Hero text --}}
    <div class="grid max-w-[760px] gap-3.5">
        @if ($section->translate('eyebrow'))
            <x-ui.eyebrow class="block">{{ $section->translate('eyebrow') }}</x-ui.eyebrow>
        @endif

        <x-ui.heading level="h1">{{ $page->translate('title', $language) }}</x-ui.heading>

        @if ($section->translate('lead'))
            <p class="lead measure">{{ $section->translate('lead') }}</p>
        @endif

        @if ($badges->isNotEmpty())
            <div class="mt-1.5 flex flex-wrap gap-2">
                @foreach ($badges as $badge)
                    <x-ui.badge :dot="$loop->first">{{ $badge->translate('title') }}</x-ui.badge>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Calculator --}}
    <div class="calc mt-8" data-graft-calculator>
        <script type="application/json" data-calc-config>@json($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>

        {{-- 1 · Zones --}}
        <div class="calc-zones">
            <h2 class="text-xl font-extrabold tracking-[-0.02em] text-ink">{{ $section->text('zones_step_label') ?: __('cost.calculator.step_zones') }}</h2>
            @if ($section->text('zones_hint'))
                <p class="mb-4 mt-1.5 text-sm text-muted">{{ $section->text('zones_hint') }}</p>
            @endif

            <ul class="grid list-none gap-2 p-0" data-calc-zone-list>
                @foreach ($zones as $zone)
                    <li>
                        <button
                            type="button"
                            class="calc-zone-btn"
                            data-zone="{{ $zone['number'] }}"
                            aria-pressed="{{ in_array($zone['number'], $selected, true) ? 'true' : 'false' }}"
                        >
                            <span class="calc-zone-num" aria-hidden="true">{{ $zone['number'] }}</span>
                            <span class="flex-1 text-[0.95rem] font-bold">{{ $zone['name'] }}</span>
                            <span class="whitespace-nowrap font-mono text-xs text-muted">{{ number_format($zone['min']) }}–{{ number_format($zone['max']) }}</span>
                            <span class="calc-zone-check" aria-hidden="true">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10" /></svg>
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>

            @if ($presets->isNotEmpty())
                <div class="mt-4 flex flex-wrap items-center gap-1.5">
                    <span class="me-1 text-xs font-bold text-muted">{{ $section->text('presets_label') ?: __('cost.calculator.presets_label') }}</span>
                    @foreach ($presets as $preset)
                        @php($presetZones = $preset->zoneNumbers())
                        <button
                            type="button"
                            class="calc-chip"
                            data-preset="{{ implode(',', $presetZones) }}"
                            aria-pressed="{{ $presetZones !== [] && count($presetZones) === count($selected) && array_diff($presetZones, $selected) === [] ? 'true' : 'false' }}"
                        >{{ $preset->translate('label') }}</button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Head --}}
        <div class="calc-head">
            <x-cost.partials.head-svg :zones="$zones" :selected="$selected" :label="__('cost.calculator.head_aria')" />
        </div>

        {{-- 2 · Estimate --}}
        <div class="calc-result" aria-live="polite">
            <span class="calc-result-label">{{ $section->text('result_step_label') ?: __('cost.calculator.step_estimate') }}</span>

            <div class="calc-result-big">
                <span data-calc-grafts data-value="{{ $estimate['mid'] }}">{{ number_format($estimate['mid']) }}</span>
                <small>{{ __('cost.calculator.grafts') }}</small>
            </div>

            <div class="mt-2 text-sm text-white/75" data-calc-range>
                @if ($estimate['zones'] === [])
                    {{ __('cost.calculator.select_zone') }}
                @else
                    {{ __('cost.calculator.range', ['min' => number_format($estimate['min']), 'max' => number_format($estimate['max']), 'zones' => trans_choice('cost.calculator.zone_count', count($estimate['zones']), ['count' => count($estimate['zones'])])]) }}
                @endif
            </div>

            <div class="calc-meter" aria-hidden="true"><i data-calc-meter style="width: {{ min(100, $page->meter_max_grafts > 0 ? $estimate['mid'] / $page->meter_max_grafts * 100 : 0) }}%"></i></div>
            <div class="mt-1.5 flex justify-between text-[0.68rem] text-white/55" aria-hidden="true">
                <span>0</span>
                <span>{{ number_format((int) ($page->meter_max_grafts / 2)) }}</span>
                <span>{{ number_format($page->meter_max_grafts) }}+</span>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-2.5">
                <div class="calc-cell"><b data-calc-hairs>{{ $estimate['zones'] === [] ? '0' : __('cost.calculator.hairs_approx', ['count' => number_format($estimate['hairs'])]) }}</b><span>{{ __('cost.calculator.hairs') }}</span></div>
                <div class="calc-cell"><b data-calc-sessions>{{ $estimate['zones'] === [] ? '—' : ($estimate['two_sessions'] ? __('cost.calculator.two_sessions') : __('cost.calculator.one_session')) }}</b><span>{{ __('cost.calculator.sessions') }}</span></div>
                <div class="calc-cell"><b data-calc-time>{{ $estimate['duration'] ?? '—' }}</b><span>{{ __('cost.calculator.time') }}</span></div>
                <div class="calc-cell"><b data-calc-price>{{ $estimate['price_from'] ? __('cost.calculator.from', ['price' => $page->money($estimate['price_from'])]) : '—' }}</b><span>{{ __('cost.calculator.package') }}</span></div>
            </div>

            @if ($section->text('result_note') || $section->text('result_note_two_sessions'))
                <p class="calc-note" data-calc-note>{{ $estimate['two_sessions'] ? ($section->text('result_note_two_sessions') ?: $section->text('result_note')) : $section->text('result_note') }}</p>
            @endif

            <div class="mt-auto grid gap-2.5 pt-5">
                <x-ui.button :href="$ctaUrl" variant="accent" :block="true">{{ $section->text('cta_label') ?: __('nav.cta_long') }}</x-ui.button>

                <x-ui.button href="https://wa.me/{{ $whatsapp }}" variant="translucent" :block="true" target="_blank" rel="noopener noreferrer" data-calc-whatsapp>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-12.3 7.4L3 21l2.1-5.7A8.4 8.4 0 1 1 21 11.5Z" /></svg>
                    {{ $section->text('whatsapp_label') ?: __('cost.calculator.whatsapp') }}
                </x-ui.button>

                <button type="button" class="justify-self-center text-xs font-bold text-white/70 underline underline-offset-4 hover:text-white" data-calc-reset>{{ __('cost.calculator.reset') }}</button>
            </div>
        </div>
    </div>

    @if ($section->text('footnote'))
        <p class="mt-3.5 max-w-[80ch] text-xs text-muted">{{ $section->text('footnote') }}</p>
    @endif

    {{-- Mobile sticky estimate bar (shown once the result card scrolls out of view) --}}
    <div class="calc-mbar" data-calc-bar aria-hidden="true" inert>
        <div>
            <b data-calc-bar-grafts>{{ __('cost.calculator.mobile_grafts', ['count' => number_format($estimate['mid'])]) }}</b>
            <span data-calc-bar-sub>{{ trans_choice('cost.calculator.zone_count', count($estimate['zones']), ['count' => count($estimate['zones'])]) }}@if ($estimate['price_from']) · {{ __('cost.calculator.from', ['price' => $page->money($estimate['price_from'])]) }}@endif</span>
        </div>
        <x-ui.button :href="$ctaUrl" variant="accent" size="sm">{{ $section->text('mobile_cta_label') ?: __('nav.cta') }}</x-ui.button>
    </div>
</x-ui.section>
