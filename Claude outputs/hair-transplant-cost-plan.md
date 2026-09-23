# Hair transplant cost page: build plan

Source design: `Claude outputs/turkeymed-hair-transplant-cost-design.html`
Status: **plan for review. Nothing is implemented yet.**

---

## 1. Principles

1. **Everything on the page comes from the database and can be edited in Filament.** Blade holds only layout. JS holds only behaviour. The one exception is the head photo and its zone outlines: they're a single piece of artwork, so they stay in code. The zone *names and graft ranges* are still editable.
2. **Follow the existing house conventions:**
   - Translatable text is stored as JSON keyed by locale (`{"en": "...", "ar": "..."}`) and read with the `HasTranslatedFields` trait. It falls back to English. This is how `ProcessStep`, `PatientResult`, `TreatmentCard` and `ServiceCategory` already work.
   - Anything the admin can list is a real table with `sort_order`, is drag-reorderable, and has `is_published` where that makes sense.
   - Seeders are idempotent and **never overwrite admin edits**. They only seed when the page doesn't exist yet, the same pattern as `HomeContentSeeder`.
3. **Reusable for other treatments.** Every table hangs off a `cost_pages` row that is linked to a service category. The same code can later build "Dental cost in Turkey" or "Eye surgery cost" as a new record in the admin, with no new code.
4. **Numbers live in exactly one place.** Package prices feed the calculator's "from €…", the Turkey price range, the savings % and the schema price range. Nothing is typed twice.
5. **Server-rendered first, JS second.** All prices, all techniques, all zones and all timeline stages are in the HTML, so search engines and screen readers see them. JS only switches between states and animates.
6. **Aurora rules:**
   - Colours come from tokens only. The design's hex values map onto `navy-*`/`cyan-*`/`line`/`muted`.
   - Left/right uses logical properties (start/end), so Arabic mirrors.
   - Focus rings, touch targets of at least 44px, and `aria-*` on every control.
   - No new npm or composer dependencies.

---

## 2. Architecture

### URL and routing

- New `CostPageController`, with a separate `show` (English) and `showLocalized` action, following the site's routing pattern.
- Proposed URLs: `/pricing/{slug}` and `/{locale}/pricing/{slug}`, e.g. `/pricing/hair-transplant-cost-turkey`. *(Decision D1.)*
- If there's an old WordPress URL, a row in the existing `redirects` table sends it here with a 301.
- Breadcrumbs follow the design: Home › Hair Transplant Surgery (category link) › Cost in Turkey.

### Page shell

`resources/views/cost/show.blade.php` contains:

- `x-layout.app` with SEO title, description, canonical, hreflang alternates for locales that have a translated title, and the OG image.
- Breadcrumbs.
- A loop over the page's **visible sections**, in `sort_order`, rendered with `<x-dynamic-component :component="'cost.'.$section->key" …>`.
- The mobile sticky bar. If the calculator section is visible, it shows the calculator version; otherwise the standard `x-service.sticky-cta`.

### Sections are rows

Each section is a row in `cost_page_sections`. In the admin you can:

- show or hide it
- reorder it
- edit its eyebrow, title and lead, plus the section-specific text fields

A fixed set of section keys ships with the code. Each key has its own Blade component and its own admin form fields:

| key | Component | Design section |
|---|---|---|
| `calculator` | `x-cost.graft-calculator` | 1 · Hero + graft calculator |
| `compare` | `x-cost.country-compare` | 2a · Worldwide comparison |
| `packages` | `x-cost.packages` | 2b · Packages |
| `versus` | `x-cost.versus-table` | 3a · Turkey vs US table |
| `promise` | `x-cost.promise-callout` | 3b · "5,000 grafts" callout |
| `procedure` | `x-cost.procedure` | 4 · How it's performed |
| `timeline` | `x-cost.recovery-timeline` | 5 · When will I see results |
| `results` | `x-cost.results` | 6 · Before / after |
| `blog` | `x-cost.related-posts` | 7 · Blog |
| `cta` | `x-cost.cta` | Bottom CTA banner |

In the design, sections 2a+2b and 3a+3b share one background band each. The plan keeps them as separate rows so each can be hidden on its own. The component groups them visually when they sit next to each other.

### Admin

- Filament: **Pages → "Pricing pages"** (`CostPageResource`).
- The edit screen has the page fields (general, SEO, calculator settings, pricing display) and one **tab per relation manager**:
  - Sections
  - Graft zones
  - Quick picks
  - Techniques
  - Packages (tiers)
  - Package features
  - Countries
  - Comparison rows
  - Procedure steps
  - Recovery stages
  - Recovery phases
  - List items
- Every list is drag-reorderable (`->reorderable('sort_order')`) and has an EN/FR/ES/AR tab set for its text, like the rest of the admin.

### Frontend JS

- New modules in `resources/js/cost/`: `graft-calculator.js`, `country-compare.js`, `packages.js`, `recovery-timeline.js`, `results-filter.js`.
- `app.js` imports them. Each one starts up only if its `data-*` root exists on the page.
- Each component outputs its data as `<script type="application/json" data-…-config>`: numbers plus already-translated labels. JS never contains copy.
- Money is formatted with `Intl.NumberFormat(locale, {style:'currency', currency})`.

---

## 3. Database: all new tables

`{}` = translatable JSON column (keyed by locale).

### Core
**cost_pages**
- `id`
- `service_category_id` (FK, nullable, null on delete)
- `slug` (unique)
- `is_published`
- `title{}` (the H1)
- `meta_title{}`, `meta_description{}`
- `og_image` (nullable)
- `currency` (char 3, default `EUR`)
- calculator settings:
  - `hairs_per_graft` (decimal 3,1)
  - `session_cap_grafts` (int)
  - `meter_max_grafts` (int)
  - `two_session_price_from` (int, nullable)
  - `duration_rules` (JSON: `[{max_grafts, label{}}]`)
- price range shown for Turkey:
  - `price_range_min`, `price_range_max` (int, nullable; if empty, the lowest and highest package price is used)
- timestamps

**cost_page_sections**
- `id`, `cost_page_id` (FK, cascade)
- `key` (string; unique per page)
- `is_visible`, `sort_order`
- `eyebrow{}`, `title{}`, `lead{}`
- `content` (JSON, locale-keyed, holding the section-specific text fields listed per phase)
- timestamps

**cost_page_items** (one table for all simple bullet lists)
- `id`, `cost_page_section_id` (FK, cascade)
- `group` (string, e.g. `badges`, `save_points`, `payment_methods`, `promise_points`, `advantages`)
- `title{}`, `body{}`
- `sort_order`, `is_published`
- timestamps

### Calculator (Phase 1)
**graft_zones**
- `id`, `cost_page_id`
- `number` (tinyint 1–6, matches the SVG artwork; unique per page)
- `name{}`, `min_grafts`, `max_grafts`
- `sort_order`

**graft_presets**
- `id`, `cost_page_id`
- `label{}`
- `zone_numbers` (JSON int array)
- `sort_order`, `is_published`

### Comparison (Phase 2)
**cost_countries**
- `id`, `cost_page_id`
- `country_code` (char 2, drives the flag)
- `name{}`
- `name_in_sentence{}` (nullable, e.g. "the United Kingdom")
- `min_price`, `max_price`
- `note{}` ("Surgery only · usually priced per graft")
- `is_default`, `sort_order`, `is_published`

### Packages (Phase 3)
**pricing_techniques**
- `id`, `cost_page_id`
- `name{}` ("FUE"), `tagline{}` ("Max grafts")
- `sort_order`, `is_published`

**pricing_tiers**
- `id`, `cost_page_id`
- `name{}`, `subtitle{}`
- `highlights{}` (locale → list of short tags)
- `is_featured` (the "Most popular" badge)
- `cta_label{}` (nullable)
- `sort_order`, `is_published`

**pricing_tier_prices**
- `id`, `pricing_tier_id`, `pricing_technique_id`
- `price` (unsigned int)
- unique (`tier`, `technique`)

**pricing_features**
- `id`, `cost_page_id`
- `label{}`
- `sort_order`, `is_published`

**pricing_feature_values**
- `id`, `pricing_feature_id`, `pricing_tier_id`
- `is_included` (bool)
- `value{}` (nullable, e.g. "2 nights", "1", "2 · with a day to decide")
- unique (`feature`, `tier`)

### Versus table (Phase 4)
**cost_comparison_rows**
- `id`, `cost_page_id`
- `label{}`, `ours{}`, `theirs{}`
- `sort_order`, `is_published`

### Procedure (Phase 5)
**cost_procedure_steps**
- `id`, `cost_page_id`
- `duration{}` ("~45 min", "2–3 h")
- `title{}`, `body{}`
- `sort_order`, `is_published`

### Timeline (Phase 6)
**recovery_stages**
- `id`, `cost_page_id`
- `month` (decimal 4,1; 0.5 = 2 weeks)
- `percent` (tinyint 0–100)
- `when_label{}` ("After 2 weeks"), `short_label{}` ("2w")
- `title{}`, `body{}`, `tip{}`
- `sort_order`

**recovery_phases**
- `id`, `cost_page_id`
- `from_month`, `to_month`
- `name{}`, `short_name{}`
- `tone` (enum: `navy`, `cyan-soft`, `cyan`, mapped to tokens)
- `sort_order`

Extra curve points (the dip at ~1.6 months and 89% at 9 months) go in the `timeline` section's `content.curve_points` as `[{month, percent}]`. They aren't text, so this is a JSON repeater, not a table.

### Existing table changes (Phase 7)
**patient_results**, add:
- `area` (string nullable; enum in code: `front_line`, `full_coverage`, `crown`, `beard`, `afro`)
- `technique` (string nullable: `fue`/`dhi`)
- `zones` (JSON nullable, int array)
- `description{}` (nullable; the design's per-result text)

No other existing table changes.

---

## 4. Phases: one section per phase

Every phase follows the same checklist and **ends with a stop for your review**:

1. Migration(s): `php artisan make:migration`
2. Model(s), casts, relations and scopes, plus a factory (`make:model -mf`)
3. Filament relation manager or fields
4. Seeder part (design content; EN plus FR/ES/AR, see D4)
5. Blade component(s) in `resources/views/components/cost/`
6. JS module (if interactive)
7. Lang keys for fixed UI strings (`lang/*/cost.json`)
8. Feature tests: happy path, hidden/unpublished data, fallback language, edge cases
9. `vendor/bin/pint --dirty`, then run that phase's tests. You check it in the browser.

### Phase 0: Foundation (page shell, no sections yet)
- **Tables:** `cost_pages`, `cost_page_sections`, `cost_page_items`
- **Models:**
  - `CostPage`: relations to everything, `url($locale)`, `visibleSections()`, `packagePriceRange()`, `scopePublished`
  - `CostPageSection`: `content($field)` translate helper
  - `CostPageItem`
- **Admin:**
  - `CostPageResource` under the **Pages** group
  - List page and edit page with General, SEO and Calculator settings
  - "Sections" relation manager: toggle visible, drag order, and the section form. Common fields always show; section-specific fields appear based on `key`. The key is fixed and can't be created freely: the 10 rows are seeded, and the relation manager has no "create" button.
- **Frontend:**
  - Routes, `CostPageController` (404 when the page is unpublished or the locale is unsupported)
  - `cost/show.blade.php` with breadcrumbs and SEO
  - The section loop (for now each section renders an empty placeholder component)
- **Seeder:** `CostPageSeeder` creates the "Hair transplant cost in Turkey" page and its 10 section rows. It exits if the slug already exists. Registered in `DatabaseSeeder`.
- **Tests:**
  - Page renders at the EN and localized URLs
  - Unpublished page gives 404
  - Hidden section is not rendered
  - Sections render in order
  - Seeder is idempotent
- **Done when:** the URL loads with the header, breadcrumbs, H1 and footer, and Filament can toggle and reorder sections.

### Phase 1: Section 1, hero + graft calculator
- **Tables:** `graft_zones`, `graft_presets`
- **Admin:**
  - "Graft zones" relation manager: edit only (no create/delete, because there are exactly 6 zones). Validates `min ≤ max`.
  - "Quick picks" relation manager, with a multi-select of the zones.
  - Calculator settings on the page form, including the duration rules repeater.
- **Section fields (`calculator`):**
  - eyebrow, H1 (from the page title), lead
  - step labels ("1 · Select your zones", "2 · Your estimate"), hint, quick-pick label
  - result note (normal), result note (two sessions)
  - CTA label + URL (default: contact), WhatsApp label, WhatsApp message template with `{grafts}` / `{zones}`
  - footnote
  - `badges` → `cost_page_items`
- **Component:** `x-cost.graft-calculator`
  - Hero text uses the existing page-hero styles.
  - Zone list: real `<button aria-pressed>` elements, server-rendered from `graft_zones`.
  - Head SVG and zone paths are ported from the design as a Blade partial. The head photo is extracted from the design's embedded image to `public/images/cost/head-zones.webp`.
  - Result card has `aria-live="polite"`.
- **JS `graft-calculator.js`:**
  - Zone and head selection stay in sync.
  - Presets, the count-up animation, the meter, hairs/sessions/time/price, reset.
  - The WhatsApp link uses `config('site.whatsapp')`. The design's `wa.me/?text=` has no number, which is a bug in the design.
  - Mobile sticky bar with the estimate.
  - Initial selection is zones 1–3, as in the design (a setting: `default_zone_numbers`).
- **Price logic:**
  - "from €X" = the cheapest package price. Until Phase 3 exists, it falls back to a page field.
  - If the estimate is above `session_cap_grafts`, the two-session note shows and the price is `two_session_price_from`.
- **Seed:**
  - Zones: Hairline 600–800, Temples 700–900, Frontal 1,200–1,500, Mid-scalp 1,000–1,300, Crown 1,400–1,600, Vertex/back 700–900
  - Presets: NW 2–3 → [1,2]; NW 3V → [1,2,3]; NW 4 → [1–4]; NW 5 → [1–5]; NW 6–7 → [1–6]
  - Settings: 2.2 hairs/graft, cap 4,500, meter max 5,000, two-session from 2,900
  - Duration rules: < 2,000 → "4–5 h"; < 3,500 → "6–7 h"; otherwise "7–8 h"
  - All copy from the design
- **Tests:**
  - Zones render in order
  - Preset data is present in the config JSON
  - Min > max is rejected in admin
  - Section hidden when toggled off
  - Zone names in the Arabic locale

### Phase 2: Section 2a, worldwide price comparison
- **Table:** `cost_countries`
- **Admin:** "Countries" relation manager, with a check that exactly one row is marked `is_default`
- **Section fields (`compare`):**
  - eyebrow, title, lead
  - Turkey row label ("Turkey · TurkeyMed") and Turkey subtitle ("All-inclusive: operation, hotel…")
  - chart note, "You typically save" label
  - savings sentence template: `compared with {country} — about {amount} …`
  - CTA label + URL
  - `save_points` → items
- **Component:** `x-cost.country-compare`
  - Country tabs are real `role="tablist"`.
  - Bars are scaled from range midpoints, calculated in PHP (a `CostPage::savingsFor(country)` helper) so the first country is correct without JS.
  - Flags come from a new small `x-ui.flag :code` component: inline SVG for the seeded countries (TR, GB, US, DE, FR, CA, AU), with a code-pill fallback for any other code.
- **JS `country-compare.js`:** switches the country, bars, % and amount (all precomputed in the JSON config)
- **Seed:** UK 8,000–12,000 (default), US 9,000–15,000, Germany 6,000–10,000, France 5,500–9,500, Canada 7,000–13,000, Australia 7,500–14,000, plus the 3 save points
- **Tests:**
  - Savings maths (unit test)
  - Default country rendered server-side
  - Unpublished country hidden
  - Turkey range falls back to the package min/max when the page range is empty

### Phase 3: Section 2b, packages
- **Tables:** `pricing_techniques`, `pricing_tiers`, `pricing_tier_prices`, `pricing_features`, `pricing_feature_values`
- **Admin:**
  - "Techniques" relation manager.
  - "Packages" relation manager. The tier form has **one price input per technique**: a relationship repeater pre-filled with every technique, so an admin can't forget one.
  - "Package features" relation manager. The feature form has **one row per tier**: an "Included" toggle plus an optional translated value.
  - Adding a technique or tier creates the missing price or value rows automatically (model `created` hook), so the matrix never has gaps.
- **Section fields (`packages`):**
  - "Show differences only" label, "Most popular" label, "all-inclusive" suffix, default CTA label + URL, "We accept" label
  - `payment_methods` → items
- **Component:** `x-cost.packages`
  - All techniques' prices are server-rendered (`data-technique` attributes), so crawlers see every price.
  - Real tabs, and a real `role="switch"` for differences only.
  - Features marked `data-same="1"` when every tier has the same value.
- **JS `packages.js`:** technique tabs and the differences-only filter (hides `[data-same]` rows). No HTML is rebuilt in JS.
- **Seed:**
  - Techniques: FUE (Max grafts), DHI (Without shaving), VIP (Stem cell · Exosome)
  - Tiers: Basic (Operation only), Standard (Best for short trips, featured), Premium (Ideal after long flights), each with its highlight tags
  - Prices:

    | | Basic | Standard | Premium |
    |---|---|---|---|
    | FUE | 1,500 | 1,850 | 2,100 |
    | DHI | 1,900 | 2,250 | 2,500 |
    | VIP | 2,900 | 3,250 | 3,500 |

  - All 12 features with their per-tier values, exactly as in the design
  - Payment methods: Cash, Credit card, PayPal, Bank transfer
- **Tests:**
  - Every technique × tier price is rendered
  - The "differences" flag is correct
  - Unpublished tier or technique hidden
  - Creating a tier creates its price rows
  - The calculator "from" price now equals the cheapest package

### Phase 4: Section 3, Turkey vs US table + graft promise callout
- **Table:** `cost_comparison_rows`
- **Admin:** "Comparison rows" relation manager
- **Section fields:**
  - `versus`: eyebrow, title, lead, column headers (ours / theirs), footnote
  - `promise`: eyebrow, title, intro paragraph, closing paragraph, question box (eyebrow, quote, answer); `promise_points` → items (title + body)
- **Components:**
  - `x-cost.versus-table`: a real `<table>` with `<th scope>`, styles matching `.post-body table`, horizontal scroll on mobile
  - `x-cost.promise-callout`: numbered points
- **Seed:** the 6 comparison rows, 3 promise points and all copy from the design
- **Tests:** rows in order, the table has proper headers, the callout hides when its section is hidden

### Phase 5: Section 4, how the procedure is performed
- **Table:** `cost_procedure_steps`
- **Admin:** "Procedure steps" relation manager
- **Section fields (`procedure`):** eyebrow, title, lead, advantages card title, "read more" link label + URL; `advantages` → items
- **Component:** `x-cost.procedure`. Reuses `x-ui.card`, `x-ui.link-arrow` and the home "How it works" pattern. The step number comes automatically from the order. The advantages list is numbered 01–05.
- **Seed:** 4 steps (Consultation & hairline design ~45 min; Extraction 2–3 h; Channel opening ~1 h; Implantation 2–3 h) and the 5 advantages
- **Tests:** numbering follows sort order, the link hides when the URL is empty

### Phase 6: Section 5, recovery timeline
- **Tables:** `recovery_stages`, `recovery_phases`
- **Admin:**
  - Stages relation manager: `percent` limited to 0–100, `month` to 0–24
  - Phases relation manager: `from < to`
  - Curve points repeater in the timeline section form
- **Section fields (`timeline`):** eyebrow, title, lead, chart title, legend labels, "Your part:" label, footnote
- **Component:** `x-cost.recovery-timeline`
  - Stage cards are server-rendered as an `<ol>`, so the content is readable without JS.
  - The chart `<svg>` gets its accessible label from the data (e.g. "about 5% at 2 weeks, 30% at 3 months…").
  - Chart colours use CSS tokens.
- **JS `recovery-timeline.js`:**
  - Draws the curve and the hair illustrations from the config.
  - Resize handling, the draw-in animation when scrolled into view (skipped with reduced motion), keyboard focus on milestones.
  - Mobile uses a vertical rail.
  - The chart is pinned left-to-right (time axis), like the before/after slider.
- **Seed:**
  - 4 stages: 2 weeks 5%, 3 months 30%, 6 months 65%, 1 year 100%, with titles, texts and tips
  - 3 phases: Healing & shedding 0–3, New growth 3–6, Thickening & maturing 6–12
  - Curve points: (1.6, 3), (9, 89)
- **Tests:** stages in order, bad percent rejected, chart config JSON present, hidden section

### Phase 7: Section 6, before / after
- **Migration:** add `area`, `technique`, `zones`, `description{}` to `patient_results`
- **Admin:** new fields in the existing `PatientResultForm` and table (filterable by area)
- **Section fields (`results`):** eyebrow, title, "See all" label + URL (link hidden when empty), "All" chip label, number of results
- **Data:** published results from the page's service category (`PatientResult::published()`, so **consent is still enforced**)
- **Component:** `x-cost.results`
  - Filter chips are generated only from areas that actually have results.
  - Reuses `x-ui.before-after` and the existing `[data-carousel]` JS.
  - Badges for technique, area and zones.
  - The text is the result's `description`, falling back to `consent_note`.
- **JS `results-filter.js`:** filters the slides by `data-area` and resets the carousel
- **Seed:** none. The 3 results in the design point to images already on `media.turkeymed.net`. They're tagged in the admin (or I add a one-off seeder that only updates `area`/`technique`/`description` on those 3 records, if you confirm the IDs).
- **Tests:**
  - Unconsented result never rendered
  - Chips only for areas that exist
  - Section hides when there are no results
  - Service pages still work (regression)

### Phase 8: Section 7, blog + bottom CTA
- **Section fields:**
  - `blog`: eyebrow, title, "All articles" label, number of posts. Posts come from the page's category; the link goes to the category blog URL.
  - `cta`: title, text, primary label + URL, WhatsApp label
- **Components:**
  - `x-cost.related-posts`: reuses `x-blog.post-card`
  - `x-cost.cta`: reuses `x-ui.cta-banner` and `x-ui.whatsapp-button`
- **Seed:** copy from the design ("Learn before you leap", "Know your number. Now get your price.")
- **Tests:** only published posts in the right language and category, section hides when there are no posts

### Phase 9: Integration and polish
- **Header:** add "Pricing" to `Navigation::primary()` (and the mobile drawer), pointing at the page for the current locale *(D2)*
- **Sitemap, llms.txt:** include published cost pages for each locale
- **Schema:**
  - `MedicalWebPage` + `BreadcrumbList`
  - A price range (`AggregateOffer`: lowest and highest package price, currency) on the MedicalProcedure
  - FAQ schema only if an FAQ section is added later
- **Full pass:**
  - RTL and Arabic
  - Keyboard-only use
  - Reduced motion
  - Mobile widths 360/390/768
  - Lighthouse
  - `npm run build` on your machine
- Run the full test suite, with your OK.

---

## 5. Seeding summary

`CostPageSeeder`, called from `DatabaseSeeder`:

- Creates the page only if the `hair-transplant-cost-turkey` slug doesn't exist. After that it does nothing, so **admin edits are never overwritten.**
- Each phase adds its own private `seedX()` method, guarded by "does this page already have rows in this table", so later phases can be seeded on an existing database without touching earlier data.
- It's linked to the `hair-transplant-surgery` category by slug. If the category is missing, the page is still created with no category.
- To run it on its own: `php artisan db:seed --class=CostPageSeeder`

---

## 6. Issues found in the design (fixed in the build unless you say otherwise)

1. **Price mismatches.** "€1,500 – €3,200" appears in the comparison and the table, but the packages go up to €3,500 (VIP Premium). The build seeds the page range as 1,500–3,200 so the design matches, and the admin can clear it to use the package min/max. *(D3)*
2. **Two-session "from €2,900" is the VIP Basic price.** It's kept as its own editable setting.
3. **The WhatsApp link has no phone number** (`wa.me/?text=`). The build uses the site's WhatsApp number.
4. **Packages, zones and timeline are built only by JS** in the design, so they're invisible to crawlers. They're server-rendered in the build.
5. **"the United Kingdom" is hard-coded grammar.** It becomes the translatable `name_in_sentence`.
6. **Hex colours and inline styles** throughout. They're converted to Aurora tokens and utilities.
7. **The results filter chips don't filter anything** in the design. They filter for real in the build.
8. **"See all results →" points to a page that doesn't exist.** The link is hidden until a URL is set.
9. **The design adds "Pricing" to the header.** Handled in Phase 9.
10. **Some copy makes medical claims** ("Virtually painless", "Written guarantee"). It's seeded as written, but it's editable, and your medical team may want to review it.

---

## 7. Decisions needed before Phase 0

- **D1 URL:** `/pricing/hair-transplant-cost-turkey`, or an existing WordPress URL/slug that should be kept?
- **D2 Navigation:** add "Pricing" to the main header, as in the design?
- **D3 Turkey range:** keep 1,500–3,200 as designed, or use the real package span 1,500–3,500?
- **D4 Languages:** should the seeder include FR/ES/AR translations written by me (flagged for native review), or English only so you fill the others in the admin?
- **D5 Competitor prices:** OK to publish the UK/US/DE/FR/CA/AU ranges as "Estimated 2026 ranges"?

---

## 8. Second audit: refinements

- Tests run against MySQL (`turkeymed_test`), so JSON column queries behave the same in tests and production. No SQLite workarounds needed.
- The head photo in the design is a JPEG data URI. It's extracted once to `public/images/cost/head-zones.jpg` and converted to WebP with a JPEG fallback.
- The localized route checks `Locale::isSupported()` rather than "does a post exist in this language", because cost pages store translations as JSON inside one row.
- The hreflang alternates list only the locales whose `title` is filled in, so a half-translated page never advertises an empty language.
- The `results` section reads results by the page's category. If a page has no category, the section is hidden and the admin sees a warning.
- The Sections relation manager can't create or delete rows. The 10 rows are created by the seeder. If a section row is missing on an older page (e.g. a new section type is added later), a "Repair sections" action on the page adds the missing rows.
- Every relation manager form uses the same EN/FR/ES/AR tabs helper, shared in one place (`App\Filament\Support\TranslatedTabs`), so the 12 forms stay consistent.
- All JS modules respect `prefers-reduced-motion` (no count-up, no curve draw-in) and are wrapped so a JS error in one section can't break the others.

---

## 9. As built (Sep 2026)

All phases delivered. Differences from the plan above:

- **Results (Phase 7)** reuse the existing `x-service.results` carousel as-is: no new patient-result fields, no filter chips, no "See all" link.
- **Blog + CTA (Phase 8)** reuse `x-blog.post-card` and `x-ui.cta-banner`.
- **Versus + promise** share one band when both are visible (the promise renders beside the table); either can be hidden independently.
- **Countries** seeded: GB (default), US, DE. The admin adds others.
- **Turkey range** seeded as 1,500–3,200; clearing both fields makes it fall back to the cheapest/dearest package price.
- **Navigation**: pricing pages are linked in the footer (Company column), not in the header.
- Prices for the calculator and comparison come from the packages (`CostPage::priceFrom()` / `priceRange()`).
- Test files: CostPageTest, GraftCalculatorTest, CountryCompareTest, PackagesTest, VersusPromiseTest, ProcedureStepsTest, RecoveryTimelineTest, CostPageRelatedContentTest, CostPageIntegrationTest.

Review pass fixes: tab panels only carry `role="tabpanel"`/`aria-labelledby` when there are tabs; the mobile estimate bar is `inert` while hidden; flag size no longer overridden inside the country tabs.
