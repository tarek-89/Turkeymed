@props([
    /** @var \Illuminate\Support\Collection<int, \App\Models\SocialLink> */
    'links',
])

@if ($links->isNotEmpty())
    <ul {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-3']) }}>
        @foreach ($links as $link)
            <li>
                <a
                    href="{{ $link->url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="{{ $link->displayLabel() }}"
                    title="{{ $link->displayLabel() }}"
                    class="grid h-11 w-11 place-items-center rounded-lg bg-navy-700 text-white transition duration-150 hover:-translate-y-0.5 hover:bg-navy-800"
                >
                    <x-ui.social-icon :name="$link->platform" />
                </a>
            </li>
        @endforeach
    </ul>
@endif
