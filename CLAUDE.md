<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>

# Aurora Design System (MANDATORY)

Every page, component, and feature MUST use the Aurora design system. It is the single source of truth for all visual design. Tokens live in `resources/css/app.css` (`@theme`) and Tailwind v4 generates the utility classes. The full visual reference is `resources/design-system/aurora-reference.html`. Never hardcode hex colors, font families, arbitrary radii, or one-off shadows — always use the tokens below via Tailwind utilities.

## Color — use token utilities only

- Primary brand: `navy-700` (#1C4068). Scale `navy-50…900` available as `bg-navy-*`, `text-navy-*`, `border-navy-*`.
- Accent: `cyan-400` (#34C8CD). Scale `cyan-50…900`.
- Signature gradient: `bg-[linear-gradient(135deg,var(--color-navy-800),var(--color-navy-700)_40%,var(--color-cyan-700))]` (navy → teal). Use for hero/feature surfaces.
- Neutrals: `surface` (#F6FAFB page bg), `ink` (#0F2230 body text), `muted` (#51616C secondary text), `line` (#E3EDF1 borders).
- Semantic: `success`, `warning`, `error`, `info` (e.g. `text-error`, `bg-success`).
- **Accessibility rule (non-negotiable):** `cyan-400` fails AA as text on white (~1.9:1). Use `cyan-400` as a FILL/accent only. For accent text or links on a light background use `cyan-800` (#0C5F63, ~5.6:1). Body text is `ink`; muted text is `muted`. White text only on `navy-*` (≥700) or `cyan-700+` fills.

## Typography

- Latin UI/body: `font-sans` = Plus Jakarta Sans. Arabic/RTL: `font-arabic` = IBM Plex Sans Arabic (applied automatically via `[lang=ar]`/`[dir=rtl]`). Mono: `font-mono` = JetBrains Mono.
- Body text never below 16px (`text-base`) to keep mobile legible and avoid iOS input zoom. Body copy line length caps ~65–75 chars (`max-w-prose`/`max-w-2xl`).
- Scale: display 60/1.05, h1 48/1.08, h2 36/1.1, h3 28/1.15, h4 22/1.25, body-lg 18, body 16, small 14, overline 12 (+0.12em, uppercase).
- One `<h1>` per page; headings ordered h2→h6, no skipping.

## Spacing, layout, radius, motion

- Spacing is a 4px base — only use Tailwind's default multiples (1=4px, 2=8px, …). No arbitrary spacing.
- Content max width 1600px; section vertical rhythm: mobile `py-10` (40), tablet `py-14` (56), desktop `py-20` (80).
- Radius tokens: `rounded-sm` 8, `rounded-md` 12, `rounded-lg` 16, `rounded-xl` 22, `rounded-2xl` 28, plus `rounded-full` (pill).
- Elevation: `shadow-md`, `shadow-lg`, `shadow-glow` (cyan glow for accent CTAs). Don't invent shadows.
- Motion: fast 150ms (hovers), normal 300ms (panels), slow 500ms (reveals). Easing `--ease-standard` and `--ease-emphasized`. Reduced motion is honored globally in `app.css` — never override it.

## Accessibility — WCAG 2.1 AA (baked in, keep it)

- Focus visible on every interactive element: 3px `cyan-300` ring, 2px offset (already global in `app.css`; don't remove focus outlines).
- Touch targets ≥ 44×44px on mobile.
- Semantic HTML and landmarks (`header`/`nav`/`main`/`footer`); every form input has a `<label>`; errors use `aria-invalid` + `aria-describedby`; decorative images `aria-hidden`, meaningful images get descriptive `alt`.
- RTL: use logical properties (`ps-*`, `pe-*`, `ms-*`, `me-*`, `text-start`/`text-end`) — never hardcoded left/right — so Arabic mirrors cleanly.

## Component conventions

- Buttons: Primary = `navy-700` fill / white text; Accent = `cyan-400` fill / `navy-900` text (8.1:1) or gradient; Secondary = `navy-700` outline; Ghost = text + arrow. Hover/active darken one step; loading uses spinner.
- Cards/surfaces: white background, `border border-line`, `rounded-xl`/`rounded-2xl`, `shadow-md` on hover.
- Build with existing tokens first; if a genuinely new token is needed, add it to `@theme` in `app.css` (never inline) and note it here.
- `.text-gradient` (component class in `app.css`): signature navy→cyan gradient as text fill — for decorative display text like the 404 numerals. Pair with a heading scale + `font-extrabold`.

## Reusable components (use these before writing new markup)

Every component merges attributes, so you can override any class and pass extra attributes.

Layout / chrome:

- `<x-layout.app :title :description :canonical :alternates :ogImage :noindex :fab :bodyClass>` — the page base (html/head/header/footer/FAB). Wrap every page in this. Set `:fab="false"` on pages that render their own sticky CTA (e.g. service pages).
- `<x-layout.header />`, `<x-layout.footer />`, `<x-layout.mobile-drawer />`, `<x-layout.whatsapp-fab />`, `<x-layout.brand-mark />` — composed by the layout; rarely used directly.
- `<x-ui.language-switcher />` — language dropdown; composed by the header.
- `<x-ui.container :as>` — centers content at max 1600px with responsive padding.

Content atoms:

- `<x-ui.section :tight :container :as>` — vertical rhythm wrapper (wraps a container by default).
- `<x-ui.section-heading :eyebrow :title :level :align="between|center">` — section header; optional `<x-slot:action>` (e.g. a link-arrow) and default slot for a lead paragraph.
- `<x-ui.heading :level :size>` — heading with decoupled semantic level vs visual size (`display|h1|h2|h3|h4`).
- `<x-ui.eyebrow>` — overline label.
- `<x-ui.card :variant="default|gradient|soft" :href :interactive>` — surface card; renders `<a>` when `:href` is set.
- `<x-ui.icon-box :variant="default|light|gradient" :size="md|lg">` — rounded tile for an SVG (decorative, `aria-hidden`).
- `<x-ui.icon :name>` — small named stroke-icon set (`shield|clock|chat|star|heart|check`), inherits `currentColor`. Used for editor-chosen icons (e.g. promises).
- `<x-ui.badge :variant="soft|solid|accent|outline|translucent" :dot>` — pill / tag.
- `<x-ui.link-arrow :href :variant="default|light">` — "View all →" link (arrow flips in RTL).
- `<x-ui.button :variant="primary|accent|secondary|ghost|translucent" :size="md|sm" :href :block>` — buttons / link-buttons. `translucent` is for dark/gradient surfaces.
- `<x-ui.whatsapp-button :variant :size :block :label>` — wa.me link-button (icon + label) built on `x-ui.button`; number comes from `config('site.whatsapp')`.

Page-level building blocks:

- `<x-ui.breadcrumbs :items>` — breadcrumb trail + `BreadcrumbList` JSON-LD. `items` = `[['label' => ..., 'href' => ...], ..., ['label' => current]]` (last item = current page, no href).
- `<x-ui.page-hero :eyebrow :title>` — split hero. Default slot = lead paragraph; slots: `actions`, `meta`, `media` (media column only renders when provided).
- `<x-ui.prose>` — typography wrapper (`.post-body` styles incl. tables, figures, blockquotes) for trusted CMS/WordPress HTML bodies. Use for both posts and services.
- `<x-ui.cta-banner :title>` — gradient CTA band. Default slot = supporting text; `actions` slot for buttons.
- `<x-ui.before-after :before :after :beforeLabel :afterLabel :alt>` — draggable before/after image comparison. The real control is an `<input type="range">` (keyboard/touch/SR accessible); JS mirrors it into `--ba-pos`. Pinned `dir="ltr"` (inherently directional). Images must share the same aspect ratio (4:3, enforced in Filament).
- `<x-ui.image-carousel :images :alt :ratio>` — scroll-snap photo slider with prev/next arrows; renders a single `<img>` for one image and a neutral placeholder for none. Shares the `[data-carousel]` JS in `app.js`.
- `<x-ui.stat :value :label :accent :variant="default|light">` — big number + caption; `light` for white text on dark/gradient surfaces.
- `<x-ui.social-icon :name>` — brand glyph set (filled, `currentColor`): `instagram|facebook|youtube|tiktok|telegram|x|linkedin|whatsapp|website`.
- `<x-ui.social-links :links>` — row of navy social-icon tiles from a `SocialLink` collection; renders nothing when empty. Managed in Filament → Components → "Social & links".

Homepage (`x-home.*`):

- `<x-home.treatment-card :card>` — treatment bento card from a `TreatmentCard`; renders the `feature` (large gradient), `default` (white) or `cta` (soft) variant.
- `<x-home.testimonial :testimonial>` — patient-story quote card from a `Testimonial`; `is_featured` renders the gradient variant.
- `<x-home.video :video>` — responsive YouTube embed from a `Video` (nocookie iframe; 16:9 for `video`, 9:16 for `short`).

The homepage (`home/index.blade.php`, `HomeController`) is data-driven: Hero/CTA come from `Setting` (managed in Filament → Pages → Homepage), and each section reads its component model (`TreatmentCard`, `Testimonial`, `ProcessStep`, `Gallery`, `Video`, `InstagramPost`) plus reuses `Stat`, `PatientResult` and `Post`. Sections hide themselves when empty. `/` is the homepage; the blog index moved to `/blog`.

Contact (`x-contact.*`):

- `<x-contact.method :icon :title :description :value :href :accent>` — contact method card (WhatsApp/phone/email). `:accent` renders the highlighted gradient card. Method *values* come from `config('site.*')`; descriptions are admin-editable settings.
- `<x-contact.office-card :office>` — office card (name, badge, address, phone, hours, directions/call buttons) from an `Office` model. Offices are grouped by country on the contact page.

Blog (`x-blog.*`):

- `<x-blog.post-card :post :compact>` — post teaser card (image or icon placeholder, category badge, date, clamped excerpt, read-arrow). Use on the blog index and "keep reading" grids. `:compact="true"` renders a small horizontal teaser (thumb + title + date) for secondary placements like the category page.

Service pages (`x-service.*`, reusable across all services):

- `<x-service.layout>` — content + sticky-sidebar grid. Default slot = article; `aside` slot = sidebar cards.
- `<x-service.consultation-card :title :text>` — gradient "talk to a coordinator" card with consultation + WhatsApp CTAs (defaults translated via `lang/*/services.json`).
- `<x-service.related-links :services :heading>` — arrow-link list card; renders nothing when the collection is empty.
- `<x-service.sticky-cta :price>` — mobile-only sticky conversion bar. Pair with `:fab="false"` and `body-class="max-lg:pb-24"` on `x-layout.app`.
- `<x-service.results :results>` — "Real results" scroll-snap carousel; each slide is a full-width card (before/after slider + text column: eyebrow, headline built from `grafts_count`/`months_to_result`, consent note, prev/next arrows under the text). Renders nothing when empty. Data: `PatientResult` model (`Service::patientResults()` = pinned-to-service + category-wide, manual sort order); managed in Filament under the "Components" group, consent-gated publishing.

Forms (accessible by default — they render their own `<label>`, pull validation errors from `$errors`, and wire `aria-invalid` + `aria-describedby`):

- `<x-form.input :name :label :type :hint :required>`
- `<x-form.textarea :name :label :rows :hint :required>`
- `<x-form.select :name :label :hint :required>` — pass `<option>`s as the slot.
- `<x-form.checkbox :name :checked :required>` — consent-style checkbox; label text is the slot.
- `<x-form.field :label :for :hint :error :required>` — wrapper for a control the kit doesn't cover.
