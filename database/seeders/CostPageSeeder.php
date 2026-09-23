<?php

namespace Database\Seeders;

use App\Models\CostPage;
use App\Models\CostPageSection;
use App\Models\ServiceCategory;
use App\Support\Cost\CalculatorDefaults;
use App\Support\Cost\SectionKey;
use Illuminate\Database\Seeder;

/**
 * Default "Hair transplant cost in Turkey" pricing page, with the content of
 * the Aurora design. English only: the admin translates in the locale tabs.
 *
 * Idempotent and non-destructive: the page is created once; each later
 * seedX() only fills a list that is still empty, so admin edits are never
 * overwritten and later phases can be seeded onto an existing database.
 */
class CostPageSeeder extends Seeder
{
    public const SLUG = 'hair-transplant-cost-turkey';

    public function run(): void
    {
        $page = $this->seedPage();

        $this->seedSectionHeadings($page);
        $this->seedCalculator($page);
        $this->seedComparison($page);
        $this->seedPackages($page);
        $this->seedVersusAndPromise($page);
        $this->seedProcedure($page);
        $this->seedTimeline($page);
        $this->seedBlogAndCta($page);
    }

    private function seedPage(): CostPage
    {
        $page = CostPage::query()->where('slug', self::SLUG)->first();

        if ($page) {
            $page->ensureSections();
            $page->ensureZones();

            return $page;
        }

        // DatabaseSeeder runs without model events, so the sections the
        // `created` hook would add are created explicitly below.
        $page = CostPage::create([
            'slug' => self::SLUG,
            'service_category_id' => ServiceCategory::query()->where('slug', 'hair-transplant-surgery')->value('id'),
            'is_published' => false,
            'title' => ['en' => 'Hair transplant cost in Turkey'],
            'meta_title' => ['en' => 'Hair Transplant Cost in Turkey 2026: Prices & Graft Calculator'],
            'meta_description' => ['en' => 'All-inclusive hair transplant packages in Istanbul from €1,500. Estimate your graft count in seconds and compare prices with the UK, US and Germany.'],
            'currency' => 'EUR',
            'price_range_min' => 1500,
            'price_range_max' => 3200,
            'hairs_per_graft' => 2.2,
            'session_cap_grafts' => 4500,
            'meter_max_grafts' => 5000,
            'two_session_price_from' => 2900,
            'default_zone_numbers' => [1, 2, 3],
            'duration_rules' => CalculatorDefaults::durationRules(),
        ]);

        $page->ensureSections();
        $page->ensureZones();

        return $page;
    }

    /** Eyebrow / title / lead for every section, only where still empty. */
    private function seedSectionHeadings(CostPage $page): void
    {
        $headings = [
            SectionKey::Calculator->value => [
                'eyebrow' => 'Hair transplant · Pricing 2026',
                'lead' => 'Tap the areas you want to restore. You get an estimated graft count in seconds — and a clear, all-inclusive price before you book a flight.',
            ],
            SectionKey::Compare->value => [
                'eyebrow' => 'Premium results, fair pricing',
                'title' => 'Hair transplant prices in Turkey compared worldwide',
                'lead' => 'Same techniques, same standards — a very different bill. Pick a country to compare.',
            ],
            SectionKey::Packages->value => [
                'title' => 'Choose your package',
            ],
            SectionKey::Versus->value => [
                'eyebrow' => 'Two pricing models',
                'title' => 'Turkey vs the United States: what the same operation costs',
                'lead' => "Patients who fly to Istanbul aren't just buying a discount. US clinics bill per graft, so the total grows with your case. A package prices the operation — a bigger case doesn't cost more.",
            ],
            SectionKey::Promise->value => [
                'eyebrow' => "Why our packages don't promise a graft number",
                'title' => '"5,000 grafts included" is a number picked before anyone has seen your scalp.',
            ],
            SectionKey::Procedure->value => [
                'eyebrow' => 'The procedure',
                'title' => 'How is your hair transplant performed?',
                'lead' => 'We use FUE (Follicular Unit Extraction): follicles are taken one by one from the donor area and implanted where you need them — under local anaesthesia, in a single day.',
            ],
            SectionKey::Timeline->value => [
                'eyebrow' => 'Recovery',
                'title' => 'When will I see the results?',
                'lead' => 'Growth is gradual. Expect a full year before the final look — here is what is normal at each stage.',
            ],
            SectionKey::Results->value => [
                'eyebrow' => 'Real results',
                'title' => 'Hair transplant Turkey: before and after',
            ],
            SectionKey::Blog->value => [
                'eyebrow' => 'Blog',
                'title' => 'Learn before you leap',
            ],
            SectionKey::Cta->value => [
                'title' => 'Know your number. Now get your price.',
                'lead' => 'Send your photos — a medical coordinator replies in your language within 24 hours.',
            ],
        ];

        foreach ($page->sections as $section) {
            /** @var CostPageSection $section */
            $defaults = $headings[$section->key] ?? [];
            $update = [];

            foreach (['eyebrow', 'title', 'lead'] as $field) {
                if (isset($defaults[$field]) && blank($section->translate($field, 'en'))) {
                    $update[$field] = ['en' => $defaults[$field]];
                }
            }

            if ($update !== []) {
                $section->update($update);
            }
        }
    }

    /** Phase 1: quick picks, the calculator's own texts and the hero badges. */
    private function seedCalculator(CostPage $page): void
    {
        if ($page->presets()->doesntExist()) {
            foreach (CalculatorDefaults::presets() as $position => $preset) {
                $page->presets()->create([
                    'label' => ['en' => $preset['label']],
                    'zone_numbers' => $preset['zones'],
                    'sort_order' => $position + 1,
                ]);
            }
        }

        $section = $page->section(SectionKey::Calculator);

        if ($section === null) {
            return;
        }

        if (blank($section->content)) {
            $section->update(['content' => ['en' => [
                'zones_step_label' => '1 · Select your zones',
                'zones_hint' => 'Tap the list or the head — they stay in sync.',
                'presets_label' => 'Quick pick (Norwood):',
                'result_step_label' => '2 · Your estimate',
                'result_note' => 'This is what your pattern needs. What can safely be transplanted depends on your donor area — your surgeon confirms that from photos, free of charge.',
                'result_note_two_sessions' => 'A case this size is usually planned over two sessions to protect your donor area. Your surgeon confirms what is safe from your photos.',
                'cta_label' => 'Get my free quote',
                'whatsapp_label' => 'Send this estimate on WhatsApp',
                'whatsapp_message' => 'Hi TurkeyMed, my graft calculator estimate is ~{grafts} grafts ({zones}). Can I get a quote?',
                'mobile_cta_label' => 'Get my quote',
                'footnote' => 'Estimates are averages for planning only and do not replace a medical assessment. The package price does not change with the graft count.',
            ]]]);
        }

        if ($section->items()->where('group', 'badges')->doesntExist()) {
            foreach (['Free estimate', 'No email needed', 'Reviewed by our medical team'] as $position => $badge) {
                $section->items()->create([
                    'group' => 'badges',
                    'title' => ['en' => $badge],
                    'sort_order' => $position + 1,
                ]);
            }
        }
    }

    /** Phase 2: three comparison countries (the admin adds the rest) and the section's texts. */
    private function seedComparison(CostPage $page): void
    {
        if ($page->countries()->doesntExist()) {
            $countries = [
                ['code' => 'GB', 'name' => 'United Kingdom', 'sentence' => 'the United Kingdom', 'min' => 8000, 'max' => 12000, 'default' => true],
                ['code' => 'US', 'name' => 'United States', 'sentence' => 'the United States', 'min' => 9000, 'max' => 15000, 'default' => false],
                ['code' => 'DE', 'name' => 'Germany', 'sentence' => 'Germany', 'min' => 6000, 'max' => 10000, 'default' => false],
            ];

            foreach ($countries as $position => $country) {
                $page->countries()->create([
                    'country_code' => $country['code'],
                    'name' => ['en' => $country['name']],
                    'name_in_sentence' => ['en' => $country['sentence']],
                    'min_price' => $country['min'],
                    'max_price' => $country['max'],
                    'note' => ['en' => 'Surgery only · usually priced per graft'],
                    'is_default' => $country['default'],
                    'sort_order' => $position + 1,
                ]);
            }
        }

        $section = $page->section(SectionKey::Compare);

        if ($section === null) {
            return;
        }

        if (blank($section->content)) {
            $section->update(['content' => ['en' => [
                'turkey_label' => 'Turkey · TurkeyMed',
                'turkey_note' => 'All-inclusive: operation, hotel, transfers, medication, translator',
                'chart_note' => 'Estimated 2026 ranges for a 3,000-graft FUE case. Bars are drawn to scale from range midpoints.',
                'save_label' => 'You typically save',
                'save_text' => 'compared with {country} — about {amount} on the same operation, with travel and stay already counted.',
                'cta_label' => 'Get a free quote',
            ]]]);
        }

        if ($section->items()->where('group', 'save_points')->doesntExist()) {
            $points = [
                'The price you agree is the price you pay',
                'Accredited partner hospitals in Istanbul',
                'A coordinator who speaks your language',
            ];

            foreach ($points as $position => $point) {
                $section->items()->create([
                    'group' => 'save_points',
                    'title' => ['en' => $point],
                    'sort_order' => $position + 1,
                ]);
            }
        }
    }

    /** Phase 3: techniques, packages, the price matrix, the feature matrix and the section texts. */
    private function seedPackages(CostPage $page): void
    {
        if ($page->techniques()->doesntExist() && $page->tiers()->doesntExist() && $page->features()->doesntExist()) {
            $techniques = [];
            foreach ([['FUE', 'Max grafts'], ['DHI', 'Without shaving'], ['VIP', 'Stem cell · Exosome']] as $position => [$name, $tagline]) {
                $techniques[$name] = $page->techniques()->create([
                    'name' => ['en' => $name],
                    'tagline' => ['en' => $tagline],
                    'sort_order' => $position + 1,
                ]);
            }

            $tierDefinitions = [
                ['name' => 'Basic', 'subtitle' => 'Operation only', 'highlights' => ['Max grafts', '1 consultation'], 'featured' => false, 'prices' => ['FUE' => 1500, 'DHI' => 1900, 'VIP' => 2900]],
                ['name' => 'Standard', 'subtitle' => 'Best for short trips', 'highlights' => ['Max grafts', '2 nights', '1 consultation'], 'featured' => true, 'prices' => ['FUE' => 1850, 'DHI' => 2250, 'VIP' => 3250]],
                ['name' => 'Premium', 'subtitle' => 'Ideal after long flights', 'highlights' => ['Max grafts', '3 nights', '2 consultations'], 'featured' => false, 'prices' => ['FUE' => 2100, 'DHI' => 2500, 'VIP' => 3500]],
            ];

            $tiers = [];
            foreach ($tierDefinitions as $position => $definition) {
                $tier = $page->tiers()->create([
                    'name' => ['en' => $definition['name']],
                    'subtitle' => ['en' => $definition['subtitle']],
                    'highlights' => ['en' => $definition['highlights']],
                    'is_featured' => $definition['featured'],
                    'sort_order' => $position + 1,
                ]);

                foreach ($definition['prices'] as $technique => $price) {
                    $tier->prices()->create(['pricing_technique_id' => $techniques[$technique]->id, 'price' => $price]);
                }

                $tiers[$definition['name']] = $tier;
            }

            // Feature → value per tier: false = not included, true = included, string = included with a detail.
            $features = [
                '4★ hotel stay near the clinic' => ['Basic' => false, 'Standard' => '2 nights', 'Premium' => '3 nights'],
                'Surgeon consultation' => ['Basic' => '1', 'Standard' => '1', 'Premium' => '2 · with a day to decide'],
                'VIP transfers in Istanbul' => ['Basic' => false, 'Standard' => true, 'Premium' => true],
                'Growth factor therapy (PRP)' => ['Basic' => false, 'Standard' => true, 'Premium' => true],
                'Oxygen therapy' => ['Basic' => false, 'Standard' => false, 'Premium' => true],
                'Blood tests' => ['Basic' => true, 'Standard' => true, 'Premium' => true],
                'Medications' => ['Basic' => true, 'Standard' => true, 'Premium' => true],
                'Painless local anaesthesia' => ['Basic' => true, 'Standard' => true, 'Premium' => true],
                'Aftercare kit & first wash' => ['Basic' => true, 'Standard' => true, 'Premium' => true],
                'Translator' => ['Basic' => true, 'Standard' => true, 'Premium' => true],
                'Written guarantee' => ['Basic' => true, 'Standard' => true, 'Premium' => true],
                '12-month follow-up support' => ['Basic' => true, 'Standard' => true, 'Premium' => true],
            ];

            $position = 0;
            foreach ($features as $label => $values) {
                $feature = $page->features()->create(['label' => ['en' => $label], 'sort_order' => ++$position]);

                foreach ($values as $tierName => $value) {
                    $feature->values()->create([
                        'pricing_tier_id' => $tiers[$tierName]->id,
                        'is_included' => $value !== false,
                        'value' => is_string($value) ? ['en' => $value] : null,
                    ]);
                }
            }
        }

        $section = $page->section(SectionKey::Packages);

        if ($section === null) {
            return;
        }

        if (blank($section->content)) {
            $section->update(['content' => ['en' => [
                'popular_label' => 'Most popular',
                'differences_label' => 'Show differences only',
                'price_suffix' => 'all-inclusive',
                'cta_label' => 'Get your personal plan',
                'payment_label' => 'We accept',
            ]]]);
        }

        if ($section->items()->where('group', 'payment_methods')->doesntExist()) {
            foreach (['Cash', 'Credit card', 'PayPal', 'Bank transfer'] as $position => $method) {
                $section->items()->create([
                    'group' => 'payment_methods',
                    'title' => ['en' => $method],
                    'sort_order' => $position + 1,
                ]);
            }
        }
    }

    /** Phase 4: the Turkey vs US table rows and the graft-promise callout. */
    private function seedVersusAndPromise(CostPage $page): void
    {
        if ($page->comparisonRows()->doesntExist()) {
            $rows = [
                ['Typical price', '€1,500 – €3,200, all-inclusive', '€9,000 – €15,000'],
                ["How it's priced", 'Per package — the operation, not the graft', 'Per graft — the bill grows with your case'],
                ['A 3,000-graft case', 'The package price, unchanged', 'Rises with every extra graft'],
                ['4,000 grafts or more', 'The package price, unchanged', 'Rises again — no ceiling on a per-graft quote'],
                ['Hotel, transfers, translator', 'Included from the Standard package', 'Not part of the quote'],
                ['Where it happens', 'Accredited partner hospital, Istanbul', 'Clinic or private surgical suite, varies'],
            ];

            foreach ($rows as $position => [$label, $ours, $theirs]) {
                $page->comparisonRows()->create([
                    'label' => ['en' => $label],
                    'ours' => ['en' => $ours],
                    'theirs' => ['en' => $theirs],
                    'sort_order' => $position + 1,
                ]);
            }
        }

        $versus = $page->section(SectionKey::Versus);

        if ($versus !== null && blank($versus->content)) {
            $versus->update(['content' => ['en' => [
                'ours_header' => 'Turkey · TurkeyMed',
                'theirs_header' => 'United States',
                'footnote' => 'US figures vary by clinic, surgeon and city. Our prices are the euro figures in the package table above.',
            ]]]);
        }

        $promise = $page->section(SectionKey::Promise);

        if ($promise === null) {
            return;
        }

        if (blank($promise->content)) {
            $promise->update(['content' => ['en' => [
                'intro' => "Your donor area — the band at the back and sides — holds a limited supply of follicles, and it doesn't grow back. It is the real ceiling on any transplant. When a clinic has sold more grafts than the donor area can spare, there are only three ways to make the invoice true:",
                'closing' => 'So our packages define the technique, the stay and the aftercare — and the surgeon sets the graft number after examining you. Fewer grafts than expected? Same price, and you keep the reserve for later in life.',
                'ask_eyebrow' => 'How to read any Turkish quote',
                'ask_quote' => '"What happens if my donor area yields fewer grafts than this package promises?"',
                'ask_answer' => 'A clinic that answers clearly has examined donor areas before. A clinic that repeats the number has not.',
            ]]]);
        }

        if ($promise->items()->where('group', 'promise_points')->doesntExist()) {
            $points = [
                ['Over-harvest the donor area', 'Visible thinning at the back, for life.'],
                ['Split follicles to inflate the count', 'More "grafts" on paper, weaker growth.'],
                ['Deliver less than was sold', 'You find out months later.'],
            ];

            foreach ($points as $position => [$title, $body]) {
                $promise->items()->create([
                    'group' => 'promise_points',
                    'title' => ['en' => $title],
                    'body' => ['en' => $body],
                    'sort_order' => $position + 1,
                ]);
            }
        }
    }

    /** Phase 5: the four procedure steps and the FUE advantages card. */
    private function seedProcedure(CostPage $page): void
    {
        if ($page->procedureSteps()->doesntExist()) {
            $steps = [
                ['Consultation & hairline design', 'Your surgeon examines the donor area, confirms the graft plan and draws the hairline with you.', '~45 min'],
                ['Extraction', 'Follicular units are removed individually with a micro-punch. No scalpel, no stitches.', '2–3 h'],
                ['Channel opening', 'Tiny channels set the angle, direction and density — this decides how natural it looks.', '~1 h'],
                ['Implantation', 'Grafts are placed one by one. You rest, eat lunch and go back to your hotel the same day.', '2–3 h'],
            ];

            foreach ($steps as $position => [$title, $body, $duration]) {
                $page->procedureSteps()->create([
                    'title' => ['en' => $title],
                    'body' => ['en' => $body],
                    'duration' => ['en' => $duration],
                    'sort_order' => $position + 1,
                ]);
            }
        }

        $section = $page->section(SectionKey::Procedure);

        if ($section === null) {
            return;
        }

        if (blank($section->content)) {
            $section->update(['content' => ['en' => [
                'advantages_title' => 'Advantages of the FUE method',
                'link_label' => 'Read the full FUE guide',
            ]]]);
        }

        if ($section->items()->where('group', 'advantages')->doesntExist()) {
            $advantages = [
                'Visible signs fade about two weeks after the operation.',
                'Local anaesthesia — you return to normal life right after.',
                'No hard surgical instruments are used.',
                'No linear scar and no stitches on the head.',
                'Virtually painless, apart from the anaesthetic injections.',
            ];

            foreach ($advantages as $position => $advantage) {
                $section->items()->create([
                    'group' => 'advantages',
                    'title' => ['en' => $advantage],
                    'sort_order' => $position + 1,
                ]);
            }
        }
    }

    /** Phase 6: the four recovery stages, three phase bands, extra curve points and the section texts. */
    private function seedTimeline(CostPage $page): void
    {
        if ($page->recoveryStages()->doesntExist()) {
            $stages = [
                [0.5, 5, 'After 2 weeks', '2w', 'Shedding phase', 'Scabs are gone and the transplanted hairs begin to fall out. That is expected — the follicles stay in place, resting under the skin.', 'Wash gently, sleep slightly raised, no sport yet.'],
                [3, 30, 'After 3 months', '3m', 'First new growth', 'New hairs break through — fine, soft and uneven at first. The change is modest and patchy; this is the stage that tests patience.', 'Normal routine is back. Avoid judging the result now.'],
                [6, 65, 'After 6 months', '6m', 'Clear improvement', 'Hairs thicken and darken, coverage becomes obvious and the hairline takes shape. Most patients cut and style normally.', 'Send us progress photos for your 6-month review.'],
                [12, 100, 'After 1 year', '12m', 'Final result', 'Full density and texture. The crown matures last and can take a few extra months to catch up with the front.', 'Final check-in with your coordinator.'],
            ];

            foreach ($stages as $position => [$month, $percent, $when, $short, $title, $body, $tip]) {
                $page->recoveryStages()->create([
                    'month' => $month,
                    'percent' => $percent,
                    'when_label' => ['en' => $when],
                    'short_label' => ['en' => $short],
                    'title' => ['en' => $title],
                    'body' => ['en' => $body],
                    'tip' => ['en' => $tip],
                    'sort_order' => $position + 1,
                ]);
            }
        }

        if ($page->recoveryPhases()->doesntExist()) {
            $phases = [
                [0, 3, 'Healing & shedding', 'Healing', 'navy'],
                [3, 6, 'New growth', 'Growth', 'cyan-soft'],
                [6, 12, 'Thickening & maturing', 'Thickening', 'cyan'],
            ];

            foreach ($phases as $position => [$from, $to, $name, $short, $tone]) {
                $page->recoveryPhases()->create([
                    'from_month' => $from,
                    'to_month' => $to,
                    'name' => ['en' => $name],
                    'short_name' => ['en' => $short],
                    'tone' => $tone,
                    'sort_order' => $position + 1,
                ]);
            }
        }

        $section = $page->section(SectionKey::Timeline);

        if ($section !== null && blank($section->content)) {
            $section->update(['content' => [
                'en' => [
                    'chart_title' => 'Visible result over 12 months',
                    'legend_label' => 'Typical growth curve',
                    'hint' => 'tap a milestone',
                    'tip_label' => 'Your part:',
                    'footnote' => 'Illustrative curve — individual growth varies with age, technique and aftercare.',
                ],
                'curve_points' => [
                    ['month' => 1.6, 'percent' => 3],
                    ['month' => 9, 'percent' => 89],
                ],
            ]]);
        }
    }

    /** Phases 7–8: texts of the articles and call-to-action sections (results reuse existing patient results). */
    private function seedBlogAndCta(CostPage $page): void
    {
        $blog = $page->section(SectionKey::Blog);

        if ($blog !== null && blank($blog->content)) {
            $blog->update(['content' => ['en' => [
                'link_label' => 'All articles',
                'posts_count' => '3',
            ]]]);
        }

        $cta = $page->section(SectionKey::Cta);

        if ($cta !== null && blank($cta->content)) {
            $cta->update(['content' => ['en' => [
                'cta_label' => 'Get a free consultation',
            ]]]);
        }
    }
}
