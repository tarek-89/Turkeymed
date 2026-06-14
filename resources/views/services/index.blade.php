<x-layout.app
    :title="__('nav.treatments').' - '.config('site.brand')"
    :description="__('home.treatments_title')"
    :canonical="$language === \App\Support\Locale::DEFAULT ? route('services.index') : route('services.index.localized', $language)"
>
    <x-ui.section :tight="true">
        <x-ui.section-heading :eyebrow="__('home.treatments_eyebrow')" :title="__('home.treatments_title')" level="h1" />

        @if ($categories->isEmpty())
            <x-ui.card variant="soft" class="mx-auto max-w-xl text-center">
                <p class="text-muted">{{ __('services.category_empty') }}</p>
            </x-ui.card>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <x-ui.card :href="$category->serviceUrl($language)" :interactive="true" class="group flex flex-col">
                        <h2 class="text-lg font-bold text-ink transition-colors duration-150 group-hover:text-cyan-800">{{ $category->name }}</h2>
                        <p class="mt-2 text-sm text-muted">{{ trans_choice('services.category_count', $category->services_count) }}</p>
                    </x-ui.card>
                @endforeach
            </div>
        @endif
    </x-ui.section>
</x-layout.app>
