@props([
    /** @var \Illuminate\Support\Collection<int, array{number:int,name:string|null}> */
    'zones',
    'selected' => [],
    'label' => 'Scalp zones',
])

{{--
    Head illustration with the six tappable scalp zones. The photo and zone
    outlines are fixed artwork (zone numbers 1–6 match graft_zones.number);
    names come from the database for the accessible labels. Pinned LTR.
--}}
@php
    $badges = [
        6 => [[543, 218]],
        5 => [[338, 290], [748, 290]],
        4 => [[543, 402]],
        3 => [[543, 520]],
        2 => [[298, 572], [788, 572]],
        1 => [[543, 630]],
    ];
    $names = $zones->keyBy('number');
    $zoneLabel = fn (int $n): string => $n.' · '.($names[$n]['name'] ?? '');
@endphp

<svg
    class="calc-head-svg"
    viewBox="0 30 1086 1330"
    role="group"
    aria-label="{{ $label }}"
    dir="ltr"
    data-calc-head
>
    <defs>
        <linearGradient id="calcZoneFill" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" style="stop-color: var(--color-navy-700)" />
            <stop offset="1" style="stop-color: var(--color-cyan-600)" />
        </linearGradient>
        <clipPath id="calcSil"><path d="M269 720 L266 700 L263 680 L261 660 L258 640 L256 620 L253 600 L250 580 L248 560 L245 540 L243 520 L240 500 L239 480 L238 460 L238 440 L241 420 L242 400 L248 380 L256 360 L264 340 L268 320 L274 300 L285 280 L292 260 L309 240 L321 220 L336 200 L357 180 L385 160 L430 140 L477 120 L500 113 L543 110 L586 113 L613 120 L665 140 L710 160 L736 180 L754 200 L773 220 L783 240 L794 260 L800 280 L810 300 L819 320 L824 340 L831 360 L834 380 L842 400 L846 420 L850 440 L852 460 L852 480 L848 500 L846 520 L843 540 L840 560 L838 580 L836 600 L833 620 L830 640 L828 660 L826 680 L823 700 L820 720Z" /></clipPath>
        <clipPath id="calcScalp"><path d="M0 0H1086V609H859C759 629 656 668 543 668C430 668 327 629 227 609H0Z" /></clipPath>
        <clipPath id="calcNo6"><path clip-rule="evenodd" d="M0 0H1086V1448H0ZM370 218a173 78 0 1 0 346 0a173 78 0 1 0-346 0Z" /></clipPath>
        <clipPath id="calcNo3"><path clip-rule="evenodd" d="M0 0H1086V1448H0ZM380 470.8Q543 437.7 706 470.8C690 625 396 625 380 470.8Z" /></clipPath>
        <clipPath id="calcNoBowl"><path clip-rule="evenodd" d="M0 0H1086V1448H0ZM350 477C356 610 430 712 543 712C656 712 730 610 736 477Z" /></clipPath>
        <filter id="calcLineShadow" x="-5%" y="-5%" width="110%" height="110%"><feDropShadow dx="0" dy="1" stdDeviation="1.6" style="flood-color: var(--color-ink); flood-opacity: .55" /></filter>
    </defs>

    <image href="{{ asset('images/cost/head-zones.webp') }}" x="0" y="0" width="1086" height="1448" preserveAspectRatio="xMidYMid slice" />

    <g clip-path="url(#calcSil)">
        <g clip-path="url(#calcScalp)" data-calc-zones>
            <ellipse class="calc-zone {{ in_array(6, $selected, true) ? 'is-on' : '' }}" data-zone="6" cx="543" cy="218" rx="173" ry="78"><title>{{ $zoneLabel(6) }}</title></ellipse>
            <g clip-path="url(#calcNo6)"><path class="calc-zone {{ in_array(5, $selected, true) ? 'is-on' : '' }}" data-zone="5" d="M0 0H1086V260H957Q543 464 129 260H0Z"><title>{{ $zoneLabel(5) }}</title></path></g>
            <path class="calc-zone {{ in_array(4, $selected, true) ? 'is-on' : '' }}" data-zone="4" d="M0 260H129Q543 464 957 260H1086V561H957Q543 347.5 129 561H0Z"><title>{{ $zoneLabel(4) }}</title></path>
            <path class="calc-zone {{ in_array(3, $selected, true) ? 'is-on' : '' }}" data-zone="3" d="M380 470.8Q543 437.7 706 470.8C690 625 396 625 380 470.8Z"><title>{{ $zoneLabel(3) }}</title></path>
            <g clip-path="url(#calcNoBowl)"><g clip-path="url(#calcNo3)"><path class="calc-zone {{ in_array(2, $selected, true) ? 'is-on' : '' }}" data-zone="2" d="M0 561H129Q543 347.5 957 561H1086V1448H0Z"><title>{{ $zoneLabel(2) }}</title></path></g></g>
            <g clip-path="url(#calcNo3)"><path class="calc-zone {{ in_array(1, $selected, true) ? 'is-on' : '' }}" data-zone="1" d="M350 477C356 610 430 712 543 712C656 712 730 610 736 477Z"><title>{{ $zoneLabel(1) }}</title></path></g>

            <g class="calc-zone-lines" filter="url(#calcLineShadow)" aria-hidden="true">
                <ellipse cx="543" cy="218" rx="173" ry="78" />
                <path d="M129 260Q543 464 957 260" />
                <path d="M129 561Q543 347.5 957 561" />
                <path d="M706 470.8C690 625 396 625 380 470.8" />
                <path d="M350 477C356 610 430 712 543 712C656 712 730 610 736 477" />
                <path d="M227 609C327 629 430 668 543 668C656 668 759 629 859 609" />
            </g>
        </g>
    </g>

    <g class="calc-zone-nums" aria-hidden="true">
        @foreach ($badges as $number => $points)
            @foreach ($points as [$x, $y])
                <g data-zone-num="{{ $number }}" class="{{ in_array($number, $selected, true) ? 'is-on' : '' }}">
                    <circle cx="{{ $x }}" cy="{{ $y }}" r="30" />
                    <text x="{{ $x }}" y="{{ $y + 11 }}">{{ $number }}</text>
                </g>
            @endforeach
        @endforeach
    </g>
</svg>
