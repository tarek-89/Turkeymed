@props([
    'items' => [], // list of ['question' => ..., 'answer' => ...]
])
@if (count($items) > 0)
    <script type="application/ld+json">{!! json_encode(\App\Support\Seo\SchemaBuilder::faqPage($items), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <section {{ $attributes->merge(['class' => 'mx-auto max-w-3xl']) }}>
        <x-ui.heading level="h2" size="h3">{{ __('content.faq_title') }}</x-ui.heading>

        <div class="mt-6 space-y-3">
            @foreach ($items as $faq)
                <details class="group rounded-xl border border-line bg-white p-5 shadow-md">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-ink">
                        {{ $faq['question'] }}
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0 text-cyan-800 transition-transform duration-150 group-open:rotate-180">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </summary>
                    <div class="mt-3 leading-relaxed text-muted">{!! nl2br(e($faq['answer'])) !!}</div>
                </details>
            @endforeach
        </div>
    </section>
@endif
