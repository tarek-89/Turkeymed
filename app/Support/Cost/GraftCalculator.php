<?php

namespace App\Support\Cost;

use App\Models\CostPage;
use App\Models\GraftZone;
use App\Support\Locale;
use Illuminate\Support\Collection;

/**
 * The graft estimate maths, shared by the server-rendered initial state and
 * (as JSON config) by the browser script, so both agree.
 */
class GraftCalculator
{
    public function __construct(private readonly CostPage $page) {}

    /**
     * Estimate for a set of zone numbers.
     *
     * @param  list<int>  $zoneNumbers
     * @return array{min: int, max: int, mid: int, hairs: int, two_sessions: bool, duration: string|null, price_from: int|null, zones: list<int>}
     */
    public function estimate(array $zoneNumbers): array
    {
        $zones = $this->page->zones->whereIn('number', $zoneNumbers);

        $min = (int) $zones->sum('min_grafts');
        $max = (int) $zones->sum('max_grafts');
        $mid = (int) (round(($min + $max) / 2 / 50) * 50);
        $twoSessions = $mid > $this->page->session_cap_grafts;

        return [
            'zones' => $zones->pluck('number')->map(fn ($n): int => (int) $n)->values()->all(),
            'min' => $min,
            'max' => $max,
            'mid' => $mid,
            'hairs' => (int) (round($mid * (float) $this->page->hairs_per_graft / 50) * 50),
            'two_sessions' => $twoSessions,
            'duration' => $zones->isEmpty() ? null : $this->durationFor($mid),
            'price_from' => $twoSessions ? $this->page->two_session_price_from : $this->page->priceFrom(),
        ];
    }

    public function durationFor(int $grafts, ?string $locale = null): ?string
    {
        foreach ($this->durationRules() as $rule) {
            if ($rule['max_grafts'] === null || $grafts < $rule['max_grafts']) {
                return $rule['label'];
            }
        }

        return null;
    }

    /**
     * Duration rules with the label resolved to the current locale.
     *
     * @return list<array{max_grafts: int|null, label: string}>
     */
    public function durationRules(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $rules = $this->page->duration_rules ?: CalculatorDefaults::durationRules();

        return collect($rules)
            ->map(function (array $rule) use ($locale): array {
                $labels = (array) ($rule['label'] ?? []);

                return [
                    'max_grafts' => filled($rule['max_grafts'] ?? null) ? (int) $rule['max_grafts'] : null,
                    'label' => (string) ($labels[$locale] ?? $labels[Locale::DEFAULT] ?? (array_values(array_filter($labels))[0] ?? '')),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Zone rows in the shape the browser script needs.
     *
     * @return Collection<int, array{number: int, name: string|null, min: int, max: int}>
     */
    public function zoneConfig(?string $locale = null): Collection
    {
        return $this->page->zones->map(fn (GraftZone $zone): array => [
            'number' => $zone->number,
            'name' => $zone->translate('name', $locale),
            'min' => $zone->min_grafts,
            'max' => $zone->max_grafts,
        ])->values();
    }
}
