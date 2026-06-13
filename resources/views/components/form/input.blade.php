@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
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

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        @if ($describedby) aria-describedby="{{ $describedby }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        {{ $attributes->merge(['class' => 'w-full min-h-[48px] rounded-md border bg-white px-3.5 py-3 text-base text-ink placeholder:text-n-400 focus:border-cyan-600 focus:outline-none focus:ring-[3px] focus:ring-cyan-100 '.($hasError ? 'border-error' : 'border-n-300')]) }}
    >

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-sm text-muted">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-error" role="alert">{{ $message }}</p>
    @enderror
</div>
