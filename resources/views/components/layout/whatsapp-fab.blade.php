@php
    $number = preg_replace('/\D/', '', (string) config('site.whatsapp'));
@endphp

<a
    href="https://wa.me/{{ $number }}"
    target="_blank"
    rel="noopener noreferrer"
    class="fixed bottom-[22px] end-[22px] z-[90] grid h-14 w-14 place-items-center rounded-full bg-[linear-gradient(135deg,var(--color-cyan-400),var(--color-cyan-700))] shadow-glow"
    aria-label="{{ __('common.whatsapp_aria') }}"
>
    <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z" />
    </svg>
</a>
