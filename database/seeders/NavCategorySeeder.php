<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

/**
 * Assigns existing (imported) services to their service category so the
 * navbar dropdowns match the intended structure. Matches services by their
 * English title and propagates the category to every translation in the
 * same translation group, so the menu is correct in all locales.
 *
 * Idempotent: re-running simply re-applies the same assignments.
 */
class NavCategorySeeder extends Seeder
{
    /**
     * category slug => list of English service titles.
     *
     * @var array<string, list<string>>
     */
    private const MAP = [
        'hair-transplant-surgery' => [
            'FUE Hair Transplant',
            'Sapphire FUE Hair Transplant',
            'DHI Hair Transplant',
            'Beard Transplant',
            'Female Hair Transplant',
            'Eyebrow Transplant',
            'Hair Transplant Results',
            'Platelet-Rich Plasma (PRP)',
            'Dermomine Micrograft',
            'SVF Stem Cell Treatment for Hair Loss',
            'Mesotherapy Hair Loss Treatment',
        ],
        'dental-clinic' => [
            'Dental implant in Turkey',
            'Dental Implants Surgery',
            'Zirconium Crowns',
            'Laminate Veneers',
        ],
        'eye-surgery' => [
            'Eye Lasik Operation',
        ],
        'services' => [
            'Gastric Botox Injections',
            'Gastric Bypass Surgery',
            'Gastric Sleeve Surgery',
            'Intragastric Balloon Placement',
            'Weight Loss Procedures',
        ],
    ];

    public function run(): void
    {
        foreach (self::MAP as $slug => $titles) {
            $category = ServiceCategory::where('slug', $slug)->first();

            if (! $category) {
                $this->command?->warn("Category '{$slug}' not found — run ServiceCategorySeeder first.");

                continue;
            }

            foreach ($titles as $title) {
                $matches = Service::query()->where('title', $title)->get();

                foreach ($matches as $service) {
                    // Assign this service and all its translation-group siblings.
                    if ($service->translation_group_id) {
                        Service::query()
                            ->where('translation_group_id', $service->translation_group_id)
                            ->update(['service_category_id' => $category->id]);
                    } else {
                        $service->update(['service_category_id' => $category->id]);
                    }
                }
            }
        }
    }
}
