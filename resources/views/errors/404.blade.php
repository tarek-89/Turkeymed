<x-layout.app :title="__('errors.title_404').' - '.config('site.brand')" :noindex="true">
    @php
        /** Popular treatments for the recovery chips — categorized services only (skips imported utility pages). */
        $popular = rescue(
            fn () => \App\Models\Service::published()
                ->language(app()->getLocale())
                ->whereNotNull('service_category_id')
                ->inRandomOrder()
                ->limit(4)
                ->get(),
            fn () => new \Illuminate\Database\Eloquent\Collection(),
            report: false,
        );
    @endphp

    <x-ui.section>
        <div class="mx-auto max-w-2xl text-center">
            <p aria-hidden="true" class="text-gradient text-[clamp(2.4rem,6vw,3.75rem)] font-extrabold leading-[1.05] tracking-[-0.035em]">
                404
            </p>

            <x-ui.heading level="h1" size="h2" class="mt-2">{{ __('errors.heading') }}</x-ui.heading>

            <p class="lead mx-auto mt-3 max-w-[460px]">{{ __('errors.text') }}</p>

            @if ($popular->isNotEmpty())
                <div class="mt-8 flex flex-wrap items-center justify-center gap-2.5">
                    <span class="text-sm text-muted">{{ __('errors.popular') }}</span>
                    @foreach ($popular as $service)
                        <a
                            href="{{ $service->url() }}"
                            class="rounded-full border border-line bg-white px-4 py-2 text-sm font-semibold text-ink transition duration-150 hover:border-navy-700"
                        >{{ $service->title }}</a>
                    @endforeach
                </div>
            @endif

            <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                <x-ui.button :href="\App\Support\Navigation::homeUrl()" variant="accent">
                    {{ __('errors.back_home') }}
                </x-ui.button>
                <x-ui.whatsapp-button variant="secondary" :label="__('errors.chat')" />
            </div>
        </div>
    </x-ui.section>
</x-layout.app>
