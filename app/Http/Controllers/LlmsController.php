<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Support\Locale;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Dynamic /llms.txt — a curated, markdown map of the site for AI agents
 * (Perplexity, Claude, MCP-based assistants) per the llms.txt proposal.
 *
 * Unlike sitemap.xml (every URL) this is a hand-curated overview: the key
 * pages plus service categories and top services, in every supported
 * language, with a short description per link and absolute URLs. It is
 * generated from live data so it never drifts.
 *
 * While the site is not indexable (staging / pre-launch) a minimal file is
 * served so we never advertise a staging site to AI systems — mirroring the
 * robots.txt kill-switch.
 */
class LlmsController extends Controller
{
    /** Max top services listed per language (keeps the file curated, not a dump). */
    private const SERVICES_PER_LANGUAGE = 12;

    public function index(): Response
    {
        $brand = (string) config('site.brand');

        if (! config('site.indexable', true)) {
            return $this->text("# {$brand}\n\n> This site is not yet public.\n");
        }

        $lines = [
            "# {$brand}",
            '',
            '> '.$this->summary($brand),
            '',
        ];

        foreach (Locale::codes() as $code) {
            $lines = array_merge($lines, $this->languageSection($code), ['']);
        }

        return $this->text(rtrim(implode("\n", $lines))."\n");
    }

    /**
     * One markdown section per language: key pages, service categories and
     * top services, each an absolute URL with a short description.
     *
     * @return list<string>
     */
    private function languageSection(string $code): array
    {
        $heading = Locale::native($code);

        $lines = [
            "## {$heading}",
            '',
            $this->link($this->localizedUrl('home', $code), 'Home', 'Overview of treatments, clinics and patient support.'),
            $this->link($this->localizedUrl('services', $code), 'Services', 'All treatments and medical procedures offered.'),
            $this->link($this->localizedUrl('blog', $code), 'Blog', 'Guides and articles on treatments and recovery.'),
            $this->link($this->localizedUrl('about', $code), 'About', 'Who we are, our clinics and accreditations.'),
            $this->link($this->localizedUrl('contact', $code), 'Contact', 'Talk to a patient coordinator.'),
        ];

        // Only real categories that actually have published treatments in this
        // language — mirrors the public services index and drops empty/junk
        // categories.
        $categories = ServiceCategory::query()
            ->withCount(['services' => fn ($query) => $query->published()->language($code)])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (ServiceCategory $category): bool => $category->services_count > 0);

        if ($categories->isNotEmpty()) {
            $lines[] = '';

            foreach ($categories as $category) {
                $name = self::clean((string) $category->translate('name', $code));
                $lines[] = $this->link(
                    $category->serviceUrl($code),
                    $name,
                    'Treatments in the '.$name.' category.',
                );
            }
        }

        // Top treatments: categorised, published services only. Excluding
        // uncategorised content keeps out legal/utility pages (privacy, terms,
        // booking forms) that are not treatments.
        $services = Service::published()
            ->language($code)
            ->whereNotNull('service_category_id')
            ->latest('published_at')
            ->limit(self::SERVICES_PER_LANGUAGE)
            ->get();

        if ($services->isNotEmpty()) {
            $lines[] = '';

            foreach ($services as $service) {
                $title = self::clean($service->title);
                $lines[] = $this->link(
                    $service->url(),
                    $title,
                    self::clean($service->metaDescription()) ?: $title,
                );
            }
        }

        return $lines;
    }

    /** A markdown list item: `- [Title](url): description`. */
    private function link(string $url, string $title, string $description): string
    {
        $description = Str::of($description)->squish()->limit(160)->toString();

        return "- [{$title}]({$url}): {$description}";
    }

    /**
     * Normalise CMS/WordPress text for a plain-text file: decode HTML entities
     * (e.g. "&amp;" → "&"), strip any stray tags, and collapse whitespace.
     */
    private static function clean(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $decoded = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::of($decoded)->squish()->toString();
    }

    /**
     * Absolute URL for a key page in the given locale. English (default) uses
     * the unprefixed route; other locales use the localized route.
     */
    private function localizedUrl(string $page, string $code): string
    {
        $isDefault = $code === Locale::DEFAULT;

        return match ($page) {
            'home' => $isDefault ? route('home') : route('home.localized', $code),
            'services' => $isDefault ? route('services.index') : route('services.index.localized', $code),
            'blog' => $isDefault ? route('posts.index') : route('posts.index.localized', $code),
            'about' => $isDefault ? route('about') : route('about.localized', $code),
            'contact' => $isDefault ? route('contact') : route('contact.localized', $code),
            default => url('/'),
        };
    }

    /**
     * One or two sentences describing the site — the line an LLM weights most
     * heavily. Admin-editable via the `llms.summary` setting; falls back to a
     * sensible default built from the brand.
     */
    private function summary(string $brand): string
    {
        $custom = Setting::get('llms.summary');

        if (is_string($custom) && trim($custom) !== '') {
            return Str::of($custom)->squish()->toString();
        }

        return "{$brand} is a medical-tourism provider coordinating treatments in Türkiye for international patients — including hair transplants, dental care and aesthetic procedures — with multilingual patient coordination and clinics vetted for quality.";
    }

    private function text(string $body): Response
    {
        return response($body)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
