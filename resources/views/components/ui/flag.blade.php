@props([
    'code',   // two-letter ISO country code
    'label' => null,
])

{{--
    Small flat flag for a country code. Inline SVG (no requests, no emoji
    fallback issues) for the countries we compare against; any other code
    shows a neutral pill with the code so the admin can add a country freely.
    Decorative: the country name is always printed next to it.
--}}
@php
    $code = strtoupper((string) $code);
    $flags = [
        'TR' => '<rect width="60" height="40" fill="#e30a17"/><circle cx="23" cy="20" r="10" fill="#fff"/><circle cx="25.5" cy="20" r="8" fill="#e30a17"/><polygon points="36,20 30.5,21.8 33.9,17.1 33.9,22.9 30.5,18.2" fill="#fff"/>',
        'GB' => '<rect width="60" height="40" fill="#012169"/><path d="M0 0L60 40M60 0L0 40" stroke="#fff" stroke-width="8"/><path d="M0 0L60 40M60 0L0 40" stroke="#c8102e" stroke-width="3"/><path d="M30 0V40M0 20H60" stroke="#fff" stroke-width="12"/><path d="M30 0V40M0 20H60" stroke="#c8102e" stroke-width="7"/>',
        'US' => '<rect width="60" height="40" fill="#fff"/><g fill="#b22234"><rect y="0" width="60" height="3.1"/><rect y="6.2" width="60" height="3.1"/><rect y="12.3" width="60" height="3.1"/><rect y="18.5" width="60" height="3.1"/><rect y="24.6" width="60" height="3.1"/><rect y="30.8" width="60" height="3.1"/><rect y="36.9" width="60" height="3.1"/></g><rect width="24" height="21.5" fill="#3c3b6e"/>',
        'DE' => '<rect width="60" height="40" fill="#000"/><rect y="13.3" width="60" height="13.4" fill="#d00"/><rect y="26.7" width="60" height="13.3" fill="#ffce00"/>',
        'FR' => '<rect width="60" height="40" fill="#fff"/><rect width="20" height="40" fill="#0055a4"/><rect x="40" width="20" height="40" fill="#ef4135"/>',
        'IT' => '<rect width="60" height="40" fill="#fff"/><rect width="20" height="40" fill="#009246"/><rect x="40" width="20" height="40" fill="#ce2b37"/>',
        'ES' => '<rect width="60" height="40" fill="#ffc400"/><rect width="60" height="10" fill="#c60b1e"/><rect y="30" width="60" height="10" fill="#c60b1e"/>',
        'NL' => '<rect width="60" height="40" fill="#fff"/><rect width="60" height="13.3" fill="#ae1c28"/><rect y="26.7" width="60" height="13.3" fill="#21468b"/>',
        'BE' => '<rect width="60" height="40" fill="#fdda24"/><rect width="20" height="40" fill="#000"/><rect x="40" width="20" height="40" fill="#ef3340"/>',
        'CH' => '<rect width="60" height="40" fill="#d52b1e"/><path d="M30 10v20M20 20h20" stroke="#fff" stroke-width="6"/>',
        'AT' => '<rect width="60" height="40" fill="#fff"/><rect width="60" height="13.3" fill="#ed2939"/><rect y="26.7" width="60" height="13.3" fill="#ed2939"/>',
        'IE' => '<rect width="60" height="40" fill="#fff"/><rect width="20" height="40" fill="#169b62"/><rect x="40" width="20" height="40" fill="#ff883e"/>',
        'PL' => '<rect width="60" height="40" fill="#fff"/><rect y="20" width="60" height="20" fill="#dc143c"/>',
        'SE' => '<rect width="60" height="40" fill="#006aa7"/><path d="M20 0v40M0 20h60" stroke="#fecc00" stroke-width="7"/>',
        'CA' => '<rect width="60" height="40" fill="#fff"/><rect width="15" height="40" fill="#d52b1e"/><rect x="45" width="15" height="40" fill="#d52b1e"/><path d="M30 10l2.5 6 4-2-1.5 7 5 1.5-4 3 1 4.5-7-2-7 2 1-4.5-4-3 5-1.5-1.5-7 4 2z" fill="#d52b1e"/>',
        'AU' => '<rect width="60" height="40" fill="#012169"/><g fill="#fff"><circle cx="44" cy="10" r="2"/><circle cx="52" cy="17" r="2"/><circle cx="46" cy="27" r="2.4"/><circle cx="38" cy="19" r="1.6"/><circle cx="15" cy="30" r="3"/></g>',
        'AE' => '<rect width="60" height="40" fill="#fff"/><rect width="60" height="13.3" fill="#00732f"/><rect y="26.7" width="60" height="13.3" fill="#000"/><rect width="15" height="40" fill="#ff0000"/>',
        'SA' => '<rect width="60" height="40" fill="#006c35"/><path d="M14 27h32" stroke="#fff" stroke-width="3"/>',
    ];
@endphp

@if (isset($flags[$code]))
    <svg {{ $attributes->merge(['class' => 'inline-block h-[18px] w-[26px] flex-none rounded border border-line']) }} viewBox="0 0 60 40" preserveAspectRatio="none" role="img" aria-label="{{ $label ?? $code }}" aria-hidden="{{ $label ? 'false' : 'true' }}">{!! $flags[$code] !!}</svg>
@else
    <span {{ $attributes->merge(['class' => 'inline-grid h-[18px] w-[26px] flex-none place-items-center rounded border border-line bg-navy-50 text-[9px] font-extrabold tracking-wide text-navy-700']) }} aria-hidden="true">{{ $code }}</span>
@endif
