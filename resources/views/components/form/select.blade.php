@props([
    'name',
    'label' => null,
    'hint' => null,
    'required' => false,
    'id' => null,
])

@php
    $id ??= $name;
    $hasError = $errors->has($name);
    $describedby = collect([
        $hint ? "{$id}-hint" : null,
        $hasError ? "{$id}-error" : null,
    ])->filter()->implode(' ');
@endphp

<div class="block">
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-semibold text-ink-2">
            {{ $label }}@if ($required)<span class="text-error"> *</span>@endif
        </label>
    @endif

    <div class="relative">
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            @if ($required) required @endif
            @if ($describedby) aria-describedby="{{ $describedby }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            {{ $attributes->merge(['class' => 'w-full min-h-[48px] appearance-none rounded-md border bg-white px-3.5 pe-10 py-3 text-base text-ink focus:border-cyan-600 focus:outline-none focus:ring-[3px] focus:ring-cyan-100 '.($hasError ? 'border-error' : 'border-n-300')]) }}
        >
            {{ $slot }}
        </select>
        <span class="pointer-events-none absolute end-4 top-1/2 h-1.5 w-1.5 -translate-y-2/3 rotate-45 border-b-2 border-r-2 border-muted" aria-hidden="true"></span>
    </div>

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-sm text-muted">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-error" role="alert">{{ $message }}</p>
    @enderror
</div>
