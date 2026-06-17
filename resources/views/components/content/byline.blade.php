@props([
    'author' => null,       // User model, or null
    'authorName' => null,   // legacy plain-string fallback
    'updated' => null,      // Carbon instance, or null
    'language' => \App\Support\Locale::DEFAULT,
])
@php
    $hasAuthor = $author || ($authorName !== null && trim((string) $authorName) !== '');
@endphp
@if ($hasAuthor || $updated)
    <div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted']) }}>
        @if ($hasAuthor)
            <span>
                {{ __('content.written_by') }}
                @if ($author)
                    <span class="font-semibold text-ink">{{ $author->name }}</span>@if ($author->credentials)<span>, {{ $author->credentials }}</span>@endif
                @else
                    <span class="font-semibold text-ink">{{ $authorName }}</span>
                @endif
            </span>
        @endif
        @if ($updated)
            <span>{{ __('content.updated') }} <time datetime="{{ $updated->toDateString() }}">{{ $updated->translatedFormat('M j, Y') }}</time></span>
        @endif
    </div>
@endif
