@props([
    /** @var \App\Models\CostPage */
    'page',
    /** @var \App\Models\CostPageSection */
    'section',
    'language',
])

{{--
    Packages: technique tabs, one card per tier, a feature list per card and
    a "show differences only" switch. Every technique's price is in the HTML
    (data-technique-price); the script only toggles which one is shown.
--}}
@php
    $techniques = $page->techniques->where('is_published', true)->values();
    $tiers = $page->tiers->where('is_published', true)->values();
    $features = $page->features->where('is_published', true)->values();
    $current = $techniques->first();
    $ctaUrl = $section->text('cta_url') ?: \App\Support\Navigation::contactUrl();
    $payments = $section->itemsIn('payment_methods');
    $priceSuffix = $section->text('price_suffix') ?: __('cost.packages.price_suffix');
@endphp

@if ($techniques->isNotEmpty() && $tiers->isNotEmpty())
    <x-ui.section id="packages" data-packages>
        <x-cost.section-heading :section="$section" align="center" />

        {{-- Technique tabs + differences switch --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            @if ($techniques->count() > 1)
                <div class="inline-flex rounded-md border border-line bg-white p-1" role="tablist" aria-label="{{ __('cost.packages.techniques_aria') }}">
                    @foreach ($techniques as $technique)
                        <button
                            type="button"
                            role="tab"
                            id="pkg-tab-{{ $technique->id }}"
                            class="pkg-tab"
                            aria-selected="{{ $technique->is($current) ? 'true' : 'false' }}"
                            aria-controls="pkg-panel"
                            tabindex="{{ $technique->is($current) ? '0' : '-1' }}"
                            data-technique="{{ $technique->id }}"
                            data-technique-name="{{ $technique->translate('name', $language) }}"
                        >
                            {{ $technique->translate('name', $language) }}
                            @if ($technique->translate('tagline', $language))
                                <small class="block text-[0.66rem] font-semibold">{{ $technique->translate('tagline', $language) }}</small>
                            @endif
                        </button>
                    @endforeach
                </div>
            @else
                <span></span>
            @endif

            @if ($features->count() > 1)
                <button type="button" class="pkg-switch" role="switch" aria-checked="false" data-pkg-diff>
                    <i aria-hidden="true"></i>{{ $section->text('differences_label') ?: __('cost.packages.differences') }}
                </button>
            @endif
        </div>

        {{-- Cards --}}
        <div id="pkg-panel" @if ($techniques->count() > 1) role="tabpanel" aria-labelledby="pkg-tab-{{ $current->id }}" @endif class="pkg-grid">
            @foreach ($tiers as $tier)
                <article class="pkg-card {{ $tier->is_featured ? 'is-featured' : '' }}">
                    @if ($tier->is_featured)
                        <x-ui.badge variant="solid" class="absolute -top-3 start-6">{{ $section->text('popular_label') ?: __('cost.packages.popular') }}</x-ui.badge>
                    @endif

                    <h3 class="text-xl font-extrabold tracking-[-0.02em] text-ink">{{ $tier->translate('name', $language) }}</h3>
                    @if ($tier->translate('subtitle', $language))
                        <p class="mt-0.5 text-sm text-muted">{{ $tier->translate('subtitle', $language) }}</p>
                    @endif

                    <div class="mt-4">
                        @foreach ($techniques as $technique)
                            @php($price = $tier->priceFor($technique))
                            <div class="pkg-price" data-technique-price="{{ $technique->id }}" @if (! $technique->is($current)) hidden @endif>
                                <span class="text-[2rem] font-extrabold leading-none tracking-[-0.03em] text-ink">{{ $price !== null ? $page->money($price, $language) : '—' }}</span>
                                <small class="mt-1 block text-xs font-semibold text-muted">{{ $technique->translate('name', $language) }} · {{ $priceSuffix }}</small>
                            </div>
                        @endforeach
                    </div>

                    @if ($tier->highlightList($language) !== [])
                        <div class="mt-3.5 flex flex-wrap gap-1.5">
                            @foreach ($tier->highlightList($language) as $tag)
                                <x-ui.badge>{{ $tag }}</x-ui.badge>
                            @endforeach
                        </div>
                    @endif

                    @if ($features->isNotEmpty())
                        <ul class="mt-4 grid list-none gap-2 border-t border-line p-0 pt-4 text-sm">
                            @foreach ($features as $feature)
                                @php($value = $feature->valueFor($tier))
                                @php($included = (bool) $value?->is_included)
                                <li class="flex items-start gap-2.5 {{ $included ? '' : 'text-n-400' }}" data-feature-row @if ($feature->isSameForAll($tiers, $language)) data-same @endif>
                                    @if ($included)
                                        <svg class="mt-0.5 flex-none text-cyan-600" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5 9-10" /></svg>
                                        <span class="sr-only">{{ __('cost.packages.included') }}</span>
                                    @else
                                        <svg class="mt-0.5 flex-none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 12h12" /></svg>
                                        <span class="sr-only">{{ __('cost.packages.not_included') }}</span>
                                    @endif
                                    <span>
                                        {{ $feature->translate('label', $language) }}@if ($included && $value->translate('value', $language)) — <b class="text-ink">{{ $value->translate('value', $language) }}</b>@endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <x-ui.button :href="$ctaUrl" :variant="$tier->is_featured ? 'primary' : 'secondary'" :block="true" class="mt-5">
                        {{ $tier->translate('cta_label', $language) ?: ($section->text('cta_label') ?: __('nav.cta_long')) }}
                    </x-ui.button>
                </article>
            @endforeach
        </div>

        @if ($payments->isNotEmpty())
            <div class="mt-5 flex flex-wrap items-center justify-center gap-2 text-sm text-muted">
                <span>{{ $section->text('payment_label') ?: __('cost.packages.payment_label') }}</span>
                @foreach ($payments as $method)
                    <x-ui.badge variant="outline">{{ $method->translate('title') }}</x-ui.badge>
                @endforeach
            </div>
        @endif
    </x-ui.section>
@endif
