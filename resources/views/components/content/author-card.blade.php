@props([
    'author',
    'language' => \App\Support\Locale::DEFAULT,
])
<aside {{ $attributes->merge(['class' => 'rounded-2xl border border-line bg-white p-6 shadow-md']) }}>
    <div class="flex items-start gap-4">
        @if ($author->photoUrl())
            <img
                src="{{ $author->photoUrl() }}"
                alt="{{ $author->name }}"
                width="64"
                height="64"
                loading="lazy"
                class="h-16 w-16 shrink-0 rounded-full object-cover"
            >
        @endif
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-cyan-800">{{ __('content.about_the_author') }}</p>
            <a href="{{ $author->url($language) }}" class="mt-1 block text-lg font-bold text-ink transition-colors duration-150 hover:text-cyan-800">{{ $author->name }}</a>
            @php($role = trim(implode(' · ', array_filter([$author->title, $author->credentials]))))
            @if ($role !== '')
                <p class="text-sm text-muted">{{ $role }}</p>
            @endif
            @if ($author->translate('bio', $language))
                <p class="mt-3 text-sm text-ink">{{ $author->translate('bio', $language) }}</p>
            @endif
        </div>
    </div>
</aside>
