@props([
    /** @var \App\Models\CostPage */
    'page',
    /** @var \App\Models\CostPageSection */
    'section',
    'language',
])

{{--
    "Prices compared worldwide": country tabs + two scaled bars + savings
    card. Rendered for the default country on the server; the script only
    swaps the numbers (all precomputed here) when another tab is chosen.
--}}
@php
    $countries = $page->countries->where('is_published', true)->values();
    $range = $page->priceRange();
    $current = $countries->firstWhere('is_default', true) ?? $countries->first();
    $ctaUrl = $section->text('cta_url') ?: \App\Support\Navigation::contactUrl();
    $savePoints = $section->itemsIn('save_points');
    $saveTemplate = $section->text('save_text') ?: __('cost.compare.save_text');

    $data = $countries->map(function ($country) use ($page, $language): array {
        $savings = $page->savingsFor($country);

        return [
            'id' => $country->id,
            'code' => strtoupper($country->country_code),
            'name' => $country->translate('name', $language),
            'sentenceName' => $country->sentenceName($language),
            'price' => $page->money($country->min_price, $language).' – '.$page->money($country->max_price, $language),
            'note' => $country->translate('note', $language),
            'percent' => $savings['percent'],
            'amount' => $page->money($savings['amount'], $language),
            'bar' => $savings['bar'],
        ];
    })->values();
    $currentData = $current ? $data->firstWhere('id', $current->id) : null;
    // Savings sentence: the template is escaped, then {country}/{amount} become bold (escaped) values.
    $saveHtml = $currentData
        ? str_replace(['{country}', '{amount}'], ['<b>'.e($currentData['sentenceName']).'</b>', '<b>'.e($currentData['amount']).'</b>'], e($saveTemplate))
        : '';
@endphp

@if ($countries->isNotEmpty() && $range['min'] !== null && $range['max'] !== null)
    <x-ui.section id="compare">
        <x-cost.section-heading :section="$section" align="center" />

        <div class="grid gap-5 lg:grid-cols-[1.1fr_0.9fr] lg:items-stretch" data-country-compare>
            <script type="application/json" data-cmp-config>@json(['countries' => $data, 'saveText' => $saveTemplate], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>

            {{-- Chart card --}}
            <x-ui.card class="p-[clamp(20px,3vw,32px)]">
                @if ($countries->count() > 1)
                    <div class="mb-6 flex flex-wrap gap-1.5" role="tablist" aria-label="{{ __('cost.compare.tabs_aria') }}">
                        @foreach ($countries as $country)
                            <button
                                type="button"
                                role="tab"
                                class="calc-chip inline-flex items-center gap-2"
                                id="cmp-tab-{{ $country->id }}"
                                aria-selected="{{ $country->is($current) ? 'true' : 'false' }}"
                                aria-controls="cmp-panel"
                                tabindex="{{ $country->is($current) ? '0' : '-1' }}"
                                data-country="{{ $country->id }}"
                            >
                                <x-ui.flag :code="$country->country_code" />
                                {{ $country->translate('name', $language) }}
                            </button>
                        @endforeach
                    </div>
                @endif

                <div id="cmp-panel" @if ($countries->count() > 1) role="tabpanel" aria-labelledby="cmp-tab-{{ $current->id }}" @endif aria-live="polite">
                    {{-- Turkey --}}
                    <div class="mb-5 grid gap-2">
                        <div class="flex items-baseline justify-between gap-3 font-bold">
                            <span class="inline-flex items-center gap-2.5"><x-ui.flag code="TR" />{{ $section->text('turkey_label') ?: __('cost.compare.turkey_label') }}</span>
                            <span class="whitespace-nowrap text-xl font-extrabold tracking-[-0.02em]">{{ $page->money($range['min'], $language) }} – {{ $page->money($range['max'], $language) }}</span>
                        </div>
                        <div class="cmp-bar" aria-hidden="true"><i class="cmp-bar-tr" data-cmp-tr-bar style="width: {{ $currentData['bar'] }}%"></i></div>
                        @if ($section->text('turkey_note'))
                            <div class="text-xs text-muted">{{ $section->text('turkey_note') }}</div>
                        @endif
                    </div>

                    {{-- Other country --}}
                    <div class="grid gap-2">
                        <div class="flex items-baseline justify-between gap-3 font-bold">
                            <span class="inline-flex items-center gap-2.5">
                                <span data-cmp-flag><x-ui.flag :code="$current->country_code" /></span>
                                <span data-cmp-name>{{ $currentData['name'] }}</span>
                            </span>
                            <span class="whitespace-nowrap text-xl font-extrabold tracking-[-0.02em]" data-cmp-price>{{ $currentData['price'] }}</span>
                        </div>
                        <div class="cmp-bar" aria-hidden="true"><i class="cmp-bar-other" style="width: 100%"></i></div>
                        <div class="text-xs text-muted" data-cmp-note>{{ $currentData['note'] }}</div>
                    </div>
                </div>

                {{-- Hidden flags for every country so the script can swap without markup of its own --}}
                <template data-cmp-flags>
                    @foreach ($countries as $country)
                        <span data-flag-for="{{ $country->id }}"><x-ui.flag :code="$country->country_code" /></span>
                    @endforeach
                </template>

                @if ($section->text('chart_note'))
                    <p class="mt-5 text-xs text-muted">{{ $section->text('chart_note') }}</p>
                @endif
            </x-ui.card>

            {{-- Savings card --}}
            <div class="flex flex-col justify-center rounded-xl bg-[linear-gradient(160deg,var(--color-navy-700),var(--color-cyan-700))] p-[clamp(24px,3vw,36px)] text-white">
                <span class="calc-result-label">{{ $section->text('save_label') ?: __('cost.compare.save_label') }}</span>
                <div class="mt-2 text-[clamp(3rem,7vw,4.5rem)] font-extrabold leading-none tracking-[-0.04em]">
                    <span data-cmp-percent>{{ $currentData['percent'] }}</span><em class="not-italic text-cyan-200">%</em>
                </div>
                <p class="mt-3 max-w-[42ch] text-white/80" data-cmp-save-text>{!! $saveHtml !!}</p>

                @if ($savePoints->isNotEmpty())
                    <ul class="mt-4 grid list-none gap-2 p-0 text-sm">
                        @foreach ($savePoints as $point)
                            <li class="flex items-start gap-2.5">
                                <span class="mt-px grid h-5 w-5 flex-none place-items-center rounded-full bg-white/15" aria-hidden="true">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10" /></svg>
                                </span>
                                {{ $point->translate('title') }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                <x-ui.button :href="$ctaUrl" variant="accent" class="mt-6 self-start">{{ $section->text('cta_label') ?: __('nav.cta_long') }}</x-ui.button>
            </div>
        </div>
    </x-ui.section>
@endif
