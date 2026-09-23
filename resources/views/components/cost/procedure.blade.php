@props([
    /** @var \App\Models\CostPage */
    'page',
    /** @var \App\Models\CostPageSection */
    'section',
    'language',
])

{{-- "How is it performed": numbered step cards + the advantages card. --}}
@php
    $steps = $page->procedureSteps->where('is_published', true)->values();
    $advantages = $section->itemsIn('advantages');
    $hasAdvantages = $advantages->isNotEmpty() || $section->text('advantages_title');
@endphp

@if ($steps->isNotEmpty())
    <x-ui.section id="procedure">
        <x-cost.section-heading :section="$section" />

        <div @class(['grid gap-6', 'lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] lg:items-start' => $hasAdvantages])>
            <ol class="grid list-none gap-3.5 p-0 sm:grid-cols-2">
                @foreach ($steps as $step)
                    <li class="relative rounded-xl border border-line bg-white p-[22px]">
                        @if ($step->translate('duration', $language))
                            <span class="absolute end-[22px] top-[22px] font-mono text-[0.7rem] text-muted">{{ $step->translate('duration', $language) }}</span>
                        @endif
                        <span class="mb-3.5 grid h-10 w-10 place-items-center rounded-md bg-[linear-gradient(135deg,var(--color-navy-700),var(--color-cyan-600))] font-extrabold text-white" aria-hidden="true">{{ $loop->iteration }}</span>
                        <h3 class="text-[1.05rem] font-extrabold tracking-[-0.01em] text-ink"><span class="sr-only">{{ $loop->iteration }}. </span>{{ $step->translate('title', $language) }}</h3>
                        @if ($step->translate('body', $language))
                            <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $step->translate('body', $language) }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>

            @if ($hasAdvantages)
                <div class="rounded-xl border border-cyan-100 bg-cyan-50 p-[26px]">
                    @if ($section->text('advantages_title'))
                        <h3 class="mb-4 text-[1.15rem] font-extrabold tracking-[-0.01em] text-ink">{{ $section->text('advantages_title') }}</h3>
                    @endif

                    @if ($advantages->isNotEmpty())
                        <ol class="grid list-none gap-3 p-0">
                            @foreach ($advantages as $advantage)
                                <li class="flex items-start gap-3 text-[0.93rem] leading-normal text-ink-2">
                                    <span class="flex-none pt-[3px] font-mono text-[0.72rem] text-cyan-800" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    {{ $advantage->translate('title') }}
                                </li>
                            @endforeach
                        </ol>
                    @endif

                    @if ($section->text('link_url') && $section->text('link_label'))
                        <x-ui.link-arrow :href="$section->text('link_url')" class="mt-5">{{ $section->text('link_label') }}</x-ui.link-arrow>
                    @endif
                </div>
            @endif
        </div>
    </x-ui.section>
@endif
