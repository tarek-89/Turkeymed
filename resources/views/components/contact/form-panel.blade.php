@props([
    'embed',        // trusted admin-pasted form embed HTML
    'image' => null, // optional portrait URL shown beside the form on large screens
])

@php
    $whatsapp = preg_replace('/\D/', '', (string) config('site.whatsapp'));
    $phone = (string) config('site.phone');
    $email = (string) config('site.email');
@endphp

<div @class([
    'overflow-hidden rounded-2xl border border-line bg-surface',
    'lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(0,0.7fr)]' => $image,
])>
    <div class="p-6 md:p-8">
        <x-ui.eyebrow class="block">{{ __('contact.message_eyebrow') }}</x-ui.eyebrow>
        <x-ui.heading level="h2" class="mt-2">{{ __('contact.form_title') }}</x-ui.heading>
        <p class="mt-2 max-w-prose text-muted">{{ __('contact.form_text') }}</p>

        {{-- No frame of its own: the panel shares the embed's background so the form reads as part of the page. --}}
        <div class="contact-embed contact-embed-form mt-4">{!! $embed !!}</div>

        <div class="mt-4 flex flex-wrap items-center justify-center gap-x-4 gap-y-3 border-t border-line pt-4">
            <span class="text-sm text-muted">{{ __('contact.reach_us_through') }}</span>
            <ul class="flex gap-2.5">
                @foreach ([
                    ['icon' => 'phone', 'label' => __('contact.method_call'), 'href' => 'tel:'.preg_replace('/[^0-9+]/', '', $phone)],
                    ['icon' => 'mail', 'label' => __('contact.method_email'), 'href' => 'mailto:'.$email],
                    ['icon' => 'whatsapp', 'label' => __('contact.method_whatsapp'), 'href' => 'https://wa.me/'.$whatsapp],
                ] as $shortcut)
                    <li>
                        <a
                            href="{{ $shortcut['href'] }}"
                            aria-label="{{ $shortcut['label'] }}"
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-line bg-white text-cyan-800 transition duration-150 hover:bg-navy-700 hover:text-white"
                        >
                            <x-ui.icon :name="$shortcut['icon']" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @if ($image)
        <div class="relative hidden lg:block" aria-hidden="true">
            <img
                src="{{ $image }}"
                alt=""
                loading="lazy"
                decoding="async"
                class="contact-portrait absolute inset-0 h-full w-full object-cover object-top"
            >
        </div>
    @endif
</div>
