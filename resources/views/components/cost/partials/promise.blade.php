@props([
    /** @var \App\Models\CostPageSection */
    'section',
    'language',
])

{{-- The "why we don't promise a graft number" callout body. --}}
@php($points = $section->itemsIn('promise_points'))

<div class="grid gap-4">
    @if ($section->translate('eyebrow'))
        <x-ui.eyebrow class="block">{{ $section->translate('eyebrow') }}</x-ui.eyebrow>
    @endif

    @if ($section->translate('title'))
        <x-ui.heading level="h3" size="h3">{{ $section->translate('title') }}</x-ui.heading>
    @endif

    @if ($section->text('intro'))
        <p class="leading-[1.65] text-ink-2">{{ $section->text('intro') }}</p>
    @endif

    @if ($points->isNotEmpty())
        <ol class="my-1 grid list-none gap-2.5 p-0">
            @foreach ($points as $point)
                <li class="flex items-start gap-3.5 rounded-md border border-line bg-white px-4 py-3.5">
                    <span class="grid h-8 w-8 flex-none place-items-center rounded-sm bg-error/10 text-sm font-extrabold text-error" aria-hidden="true">{{ $loop->iteration }}</span>
                    <div>
                        <b class="block text-[0.95rem]">{{ $point->translate('title') }}</b>
                        @if ($point->translate('body'))
                            <span class="text-sm text-muted">{{ $point->translate('body') }}</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif

    @if ($section->text('closing'))
        <p class="leading-[1.65] text-ink-2">{{ $section->text('closing') }}</p>
    @endif

    @if ($section->text('ask_quote'))
        <div class="rounded-xl bg-[linear-gradient(160deg,var(--color-navy-700),var(--color-cyan-700))] p-6 text-white">
            @if ($section->text('ask_eyebrow'))
                <x-ui.eyebrow class="block text-cyan-200">{{ $section->text('ask_eyebrow') }}</x-ui.eyebrow>
            @endif
            <q class="my-2.5 block text-xl font-extrabold leading-[1.3] tracking-[-0.02em] [quotes:none]">{{ $section->text('ask_quote') }}</q>
            @if ($section->text('ask_answer'))
                <p class="text-[0.92rem] leading-relaxed text-white/80">{{ $section->text('ask_answer') }}</p>
            @endif
        </div>
    @endif
</div>
