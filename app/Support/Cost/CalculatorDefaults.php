<?php

namespace App\Support\Cost;

/**
 * Default graft-calculator data (from the Aurora design) used when a pricing
 * page is created, so the calculator works before the admin edits anything.
 */
class CalculatorDefaults
{
    /**
     * @return array<int, array{name: string, min: int, max: int}>
     */
    public static function zones(): array
    {
        return [
            1 => ['name' => 'Hairline', 'min' => 600, 'max' => 800],
            2 => ['name' => 'Temples', 'min' => 700, 'max' => 900],
            3 => ['name' => 'Frontal', 'min' => 1200, 'max' => 1500],
            4 => ['name' => 'Mid-scalp', 'min' => 1000, 'max' => 1300],
            5 => ['name' => 'Crown', 'min' => 1400, 'max' => 1600],
            6 => ['name' => 'Vertex / back', 'min' => 700, 'max' => 900],
        ];
    }

    /**
     * @return list<array{label: string, zones: list<int>}>
     */
    public static function presets(): array
    {
        return [
            ['label' => 'NW 2–3', 'zones' => [1, 2]],
            ['label' => 'NW 3V', 'zones' => [1, 2, 3]],
            ['label' => 'NW 4', 'zones' => [1, 2, 3, 4]],
            ['label' => 'NW 5', 'zones' => [1, 2, 3, 4, 5]],
            ['label' => 'NW 6–7', 'zones' => [1, 2, 3, 4, 5, 6]],
        ];
    }

    /**
     * Operation-time labels by estimated graft count (first matching `max_grafts` wins).
     *
     * @return list<array{max_grafts: int|null, label: array<string, string>}>
     */
    public static function durationRules(): array
    {
        return [
            ['max_grafts' => 2000, 'label' => ['en' => '4–5 h']],
            ['max_grafts' => 3500, 'label' => ['en' => '6–7 h']],
            ['max_grafts' => null, 'label' => ['en' => '7–8 h']],
        ];
    }
}
