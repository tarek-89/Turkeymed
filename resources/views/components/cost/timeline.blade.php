@props([
    /** @var \App\Models\CostPage */
    'page',
    /** @var \App\Models\CostPageSection */
    'section',
    'language',
])

{{--
    Recovery timeline: a growth-curve chart (drawn by the script from the
    stages) above the stage cards (server-rendered, so the content reads
    without JavaScript). Chart and cards stay in sync when a milestone is
    tapped; on phones the cards become a vertical rail.
--}}
@php
    $stages = $page->recoveryStages->values();
    $phases = $page->recoveryPhases->values();
    $curvePoints = collect(((array) $section->content)['curve_points'] ?? [])
        ->filter(fn ($p): bool => is_array($p) && isset($p['month'], $p['percent']))
        ->map(fn (array $p): array => ['month' => (float) $p['month'], 'percent' => (int) $p['percent']])
        ->values();
    $months = max(12, (int) ceil($stages->max('month') ?? 12));

    $config = [
        'months' => $months,
        'stages' => $stages->map(fn ($s): array => [
            'month' => (float) $s->month,
            'percent' => (int) $s->percent,
            'short' => $s->translate('short_label', $language),
            'when' => $s->translate('when_label', $language),
            'title' => $s->translate('title', $language),
        ])->all(),
        'phases' => $phases->map(fn ($p): array => [
            'from' => (float) $p->from_month,
            'to' => (float) $p->to_month,
            'name' => $p->translate('name', $language),
            'short' => $p->translate('short_name', $language) ?: $p->translate('name', $language),
            'tone' => $p->tone,
        ])->all(),
        'curvePoints' => $curvePoints->all(),
        'strings' => [
            'day0' => __('cost.timeline.day0'),
            'monthShort' => __('cost.timeline.month_short'),
        ],
    ];

    $summary = $stages->map(fn ($s): string => __('cost.timeline.summary_item', ['percent' => $s->percent, 'when' => mb_strtolower((string) $s->translate('when_label', $language))]))->implode(', ');

    // Deterministic "hair" sketch per stage: more and longer strands as the percentage grows.
    $hairSketch = function (int $percent, int $index): string {
        $p = $percent / 100;
        $seed = $index * 97 + 13;
        $rand = function () use (&$seed): float {
            $seed = ($seed * 9301 + 49297) % 233280;

            return $seed / 233280;
        };
        $out = '';
        $count = 46;
        for ($j = 0; $j < $count; $j++) {
            $x = 4 + $j * (292 / ($count - 1));
            $show = $rand() < (0.25 + $p * 0.75);
            $h = $show ? (4 + $p * 28 * (0.55 + $rand() * 0.45)) : 0;
            $lean = ($rand() - 0.5) * 5;
            if ($h > 0) {
                $out .= sprintf('<path d="M%.1f 36q%.1f %.1f %.1f %.1f" stroke-width="%.2f" opacity="%.2f"/>', $x, $lean / 2, -$h / 2, $lean, -$h, 1 + $p * 1.1, 0.45 + $p * 0.55);
            } elseif ($index === 0 && $rand() < 0.5) {
                $out .= sprintf('<circle cx="%.1f" cy="36" r="1.2" class="tl-hair-dot"/>', $x);
            }
        }

        return $out;
    };
@endphp

@if ($stages->isNotEmpty())
    <x-ui.section :tight="true" id="timeline">
        <x-cost.section-heading :section="$section" align="center" />

        <div class="tl" data-recovery-timeline>
            <script type="application/json" data-tl-config>@json($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>

            {{-- Chart --}}
            <div class="tl-chart">
                <div class="flex flex-wrap items-center justify-between gap-2 px-1 pb-2">
                    @if ($section->text('chart_title'))
                        <b class="text-[0.95rem]">{{ $section->text('chart_title') }}</b>
                    @endif
                    <span class="flex items-center gap-3.5 text-xs text-muted">
                        @if ($section->text('legend_label'))
                            <span><i class="tl-legend-line" aria-hidden="true"></i>{{ $section->text('legend_label') }}</span>
                        @endif
                        @if ($section->text('hint'))
                            <span class="font-mono">{{ $section->text('hint') }}</span>
                        @endif
                    </span>
                </div>
                <svg class="tl-svg" role="img" aria-label="{{ __('cost.timeline.chart_aria', ['summary' => $summary]) }}" dir="ltr" data-tl-svg></svg>
            </div>

            {{-- Stages --}}
            <ol class="tl-stages">
                @foreach ($stages as $stage)
                    <li class="tl-stage {{ $loop->first ? 'is-on' : '' }}" data-tl-stage="{{ $loop->index }}">
                        <button type="button" class="tl-stage-btn" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                            <span class="tl-dot" aria-hidden="true">{{ $stage->translate('short_label', $language) }}</span>
                            <span class="tl-box">
                                <span class="flex items-baseline justify-between gap-2">
                                    <span class="font-mono text-[0.7rem] uppercase tracking-[0.06em] text-cyan-800">{{ $stage->translate('when_label', $language) }}</span>
                                    <span class="tl-pct">{{ $stage->percent < 100 ? '~' : '' }}{{ $stage->percent }}%<small>{{ __('cost.timeline.visible') }}</small></span>
                                </span>
                                <svg class="tl-hair" viewBox="0 0 300 40" preserveAspectRatio="none" aria-hidden="true"><path d="M0 37H300" class="tl-hair-base" /><g class="tl-hair-strands">{!! $hairSketch((int) $stage->percent, $loop->index) !!}</g></svg>
                                <span class="block text-[1.05rem] font-extrabold tracking-[-0.01em] text-ink">{{ $stage->translate('title', $language) }}</span>
                                @if ($stage->translate('body', $language))
                                    <span class="mt-1.5 block text-[0.88rem] leading-normal text-muted">{{ $stage->translate('body', $language) }}</span>
                                @endif
                                @if ($stage->translate('tip', $language))
                                    <span class="tl-tip">
                                        @if ($section->text('tip_label'))
                                            <b>{{ $section->text('tip_label') }}</b>
                                        @endif
                                        <span>{{ $stage->translate('tip', $language) }}</span>
                                    </span>
                                @endif
                            </span>
                        </button>
                    </li>
                @endforeach
            </ol>
        </div>

        @if ($section->text('footnote'))
            <p class="mt-3.5 text-center text-xs text-muted">{{ $section->text('footnote') }}</p>
        @endif
    </x-ui.section>
@endif
