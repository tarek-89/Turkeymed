@props([
    'name',
    'value' => 1,
    'checked' => false,
    'required' => false,
    'id' => null,
])

@php
    $id ??= $name;
    $hasError = $errors->has($name);
    $isChecked = (bool) old($name, $checked);
@endphp

<div class="block">
    <label for="{{ $id }}" class="flex items-start gap-2.5 text-sm text-muted">
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="checkbox"
            value="{{ $value }}"
            @if ($isChecked) checked @endif
            @if ($required) required @endif
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->merge(['class' => 'mt-0.5 h-[22px] w-[22px] flex-none accent-cyan-600']) }}
        >
        <span>{{ $slot }}</span>
    </label>

    @error($name)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-error" role="alert">{{ $message }}</p>
    @enderror
</div>
