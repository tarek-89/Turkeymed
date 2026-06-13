{{--
    Generic field wrapper for custom controls. The dedicated input/textarea/
    select/checkbox components wire errors themselves; use this only when you
    need to wrap a control the kit doesn't cover.
--}}
@props([
    'label' => null,
    'for' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
])

<div {{ $attributes->merge(['class' => 'block']) }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="mb-1.5 block text-sm font-semibold text-ink-2">
            {{ $label }}@if ($required)<span class="text-error"> *</span>@endif
        </label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="mt-1.5 text-sm text-muted">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="mt-1.5 text-sm text-error" role="alert">{{ $error }}</p>
    @endif
</div>
