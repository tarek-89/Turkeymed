@props([
    /** @var \Illuminate\Support\Collection<int, \App\Models\PatientResult> */
    'results',
])

{{--
    "Real results" — one full-width card per result (before/after slider + text
    column), sliding horizontally via scroll-snap. Arrows live under the text.
--}}
@if ($results->isNotEmpty())
    <x-ui.section :tight="true">
        <div data-carousel class="mx-auto max-w-5xl">
            <div data-carousel-track class="no-scrollbar flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth">
                @foreach ($results as $result)
                    @php
                        $headline = implode(' · ', array_filter([
                            $result->grafts_count
                                ? trans_choice('patient_results.grafts', $result->grafts_count, ['count' => number_format($result->grafts_count)])
                                : null,
                            $result->months_to_result
                                ? trans_choice('patient_results.months', $result->months_to_result, ['count' => $result->months_to_result])
                                : null,
                        ])) ?: __('patient_results.heading');
                    @endphp

                    <article class="w-full flex-none snap-start">
                        <div class="grid h-full items-center gap-8 rounded-2xl border border-line bg-white p-5 sm:p-8 lg:grid-cols-2 lg:gap-12">
                            <x-ui.before-after
                                :before="$result->beforeImageUrl()"
                                :after="$result->afterImageUrl()"
                                :before-label="$result->before_label"
                                :after-label="$result->after_label"
                                :alt="__('patient_results.eyebrow').' — '.$headline"
                            />

                            <div>
                                <x-ui.eyebrow class="block">{{ __('patient_results.eyebrow') }}</x-ui.eyebrow>

                                <x-ui.heading level="h2" class="mt-2.5">{{ $headline }}</x-ui.heading>

                                <p class="lead mt-3.5">{{ __('patient_results.consent_note') }}</p>

                                @if ($results->count() > 1)
                                    <div class="mt-7 flex gap-2.5">
                                        <button
                                            type="button"
                                            data-carousel-prev
                                            aria-label="{{ __('patient_results.prev') }}"
                                            class="grid h-11 w-11 place-items-center rounded-full border border-line bg-white text-ink transition duration-150 hover:border-navy-700"
                                        >
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="rtl:-scale-x-100">
                                                <path d="M19 12H5M11 18l-6-6 6-6" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            data-carousel-next
                                            aria-label="{{ __('patient_results.next') }}"
                                            class="grid h-11 w-11 place-items-center rounded-full border border-line bg-white text-ink transition duration-150 hover:border-navy-700"
                                        >
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="rtl:-scale-x-100">
                                                <path d="M5 12h14M13 6l6 6-6 6" />
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </x-ui.section>
@endif
