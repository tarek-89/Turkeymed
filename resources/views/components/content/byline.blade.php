@props([
    'author' => null,       // Author model, or null
    'authorName' => null,   // legacy plain-string fallback
    'reviewer' => null,     // Author model, or null
    'updated' => null,      // Carbon instance, or null
    'language' => \App\Support\Locale::DEFAULT,
])
@php
    $hasAuthor = $author || ($authorName !== null && trim((string) $authorName) !== '');
@endphp
@if ($hasAuthor || $reviewer || $updated)
    <div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted']) }}>
        @if ($hasAuthor)
            <span>
                {{ __('content.written_by') }}
                @if ($author)
                    <a href="{{ $author->url($language) }}" class="font-semibold text-ink transition-colors duration-150 hover:text-cyan-800">{{ $author->name }}</a>@if ($author->credentials)<span>, {{ $author->credentials }}</span>@endif
                @else
                    <span class="font-semibold text-ink">{{ $authorName }}</span>
                @endif
            </span>
        @endif
        @if ($reviewer)
            <span>
                {{ __('content.reviewed_by') }}
                <a href="{{ $reviewer->url($language) }}" class="font-semibold text-ink transition-colors duration-150 hover:text-cyan-800">{{ $reviewer->name }}</a>
            </span>
        @endif
        @if ($updated)
            <span>{{ __('content.updated') }} <time datetime="{{ $updated->toDateString() }}">{{ $updated->translatedFormat('M j, Y') }}</time></span>
        @endif
    </div>
@endif
