@props([
    /** @var \App\Models\Testimonial */
    'testimonial',
])

@php
    $featured = $testimonial->is_featured;
    $surface = $featured
        ? 'bg-[linear-gradient(160deg,var(--color-navy-700),var(--color-cyan-700))] text-white border-transparent'
        : 'bg-white border-line text-ink';
    $quoteColor = $featured ? 'text-white' : 'text-ink';
    $metaColor = $featured ? 'text-white/70' : 'text-muted';
    $starColor = $featured ? 'text-cyan-200' : 'text-warning';
@endphp

<figure {{ $attributes->merge(['class' => 'flex flex-col rounded-2xl border p-7 shadow-md '.$surface]) }}>
    <div class="text-lg leading-none {{ $starColor }}" aria-label="{{ $testimonial->rating }}/5">
        {{ str_repeat('★', (int) $testimonial->rating) }}
    </div>

    <blockquote class="mt-4 text-lg leading-relaxed {{ $quoteColor }}">"{{ $testimonial->translate('quote') }}"</blockquote>

    <figcaption class="mt-6 flex items-center gap-3">
        @if ($testimonial->avatarUrl())
            <img src="{{ $testimonial->avatarUrl() }}" alt="" aria-hidden="true" width="44" height="44" class="h-11 w-11 flex-none rounded-full object-cover">
        @else
            <span class="h-11 w-11 flex-none rounded-full {{ $featured ? 'bg-white/20' : 'bg-cyan-50' }}" aria-hidden="true"></span>
        @endif
        <span>
            <b class="block font-bold {{ $quoteColor }}">{{ $testimonial->author_name }}</b>
            @if ($meta = $testimonial->translate('author_meta'))
                <span class="block text-sm {{ $metaColor }}">{{ $meta }}</span>
            @endif
        </span>
    </figcaption>
</figure>
