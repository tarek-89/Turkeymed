<?php

namespace App\Support\Cost;

/**
 * The fixed set of sections a pricing page can show, in default order.
 * Each key maps to a Blade component (`x-cost.{key}`) and declares the
 * section-specific text fields and bullet-list groups the admin can edit.
 * Adding a section type means adding a case here and a component view.
 */
enum SectionKey: string
{
    case Calculator = 'calculator';
    case Compare = 'compare';
    case Packages = 'packages';
    case Versus = 'versus';
    case Promise = 'promise';
    case Procedure = 'procedure';
    case Timeline = 'timeline';
    case Results = 'results';
    case Blog = 'blog';
    case Cta = 'cta';

    /** Admin-facing name. */
    public function label(): string
    {
        return match ($this) {
            self::Calculator => 'Hero + graft calculator',
            self::Compare => 'Worldwide price comparison',
            self::Packages => 'Packages',
            self::Versus => 'Turkey vs abroad table',
            self::Promise => 'Graft promise callout',
            self::Procedure => 'How the procedure is performed',
            self::Timeline => 'Recovery timeline',
            self::Results => 'Before / after results',
            self::Blog => 'Related articles',
            self::Cta => 'Bottom call to action',
        };
    }

    /** Blade component name rendered for this section. */
    public function component(): string
    {
        return 'cost.'.$this->value;
    }

    /**
     * Section-specific translatable text fields (beyond eyebrow/title/lead),
     * stored in `content.{locale}.{name}`. Each phase adds its own.
     *
     * @return array<string, array{label: string, type: 'text'|'textarea'|'url'|'number', help?: string}>
     */
    public function contentFields(): array
    {
        return match ($this) {
            self::Calculator => [
                'zones_step_label' => ['label' => 'Step 1 label', 'type' => 'text'],
                'zones_hint' => ['label' => 'Step 1 hint', 'type' => 'text'],
                'presets_label' => ['label' => 'Quick-pick label', 'type' => 'text'],
                'result_step_label' => ['label' => 'Step 2 label', 'type' => 'text'],
                'result_note' => ['label' => 'Estimate note', 'type' => 'textarea', 'help' => 'Shown under the estimate for a single-session case.'],
                'result_note_two_sessions' => ['label' => 'Estimate note (two sessions)', 'type' => 'textarea', 'help' => 'Shown instead when the estimate is above the two-session threshold.'],
                'cta_label' => ['label' => 'Main button label', 'type' => 'text'],
                'cta_url' => ['label' => 'Main button URL', 'type' => 'url', 'help' => 'Leave empty to link to the contact page.'],
                'whatsapp_label' => ['label' => 'WhatsApp button label', 'type' => 'text'],
                'whatsapp_message' => ['label' => 'WhatsApp message', 'type' => 'textarea', 'help' => 'Pre-filled chat message. You can use {grafts} and {zones}.'],
                'mobile_cta_label' => ['label' => 'Mobile bar button label', 'type' => 'text'],
                'footnote' => ['label' => 'Footnote', 'type' => 'textarea'],
            ],
            self::Compare => [
                'turkey_label' => ['label' => 'Turkey row label', 'type' => 'text', 'help' => 'e.g. "Turkey · TurkeyMed"'],
                'turkey_note' => ['label' => 'Turkey row note', 'type' => 'text', 'help' => 'e.g. "All-inclusive: operation, hotel, transfers…"'],
                'chart_note' => ['label' => 'Chart footnote', 'type' => 'textarea'],
                'save_label' => ['label' => 'Savings card label', 'type' => 'text', 'help' => 'e.g. "You typically save"'],
                'save_text' => ['label' => 'Savings sentence', 'type' => 'textarea', 'help' => 'Use {country} and {amount}.'],
                'cta_label' => ['label' => 'Button label', 'type' => 'text'],
                'cta_url' => ['label' => 'Button URL', 'type' => 'url', 'help' => 'Leave empty to link to the contact page.'],
            ],
            self::Packages => [
                'popular_label' => ['label' => '"Most popular" badge text', 'type' => 'text'],
                'differences_label' => ['label' => '"Show differences only" label', 'type' => 'text'],
                'price_suffix' => ['label' => 'Text under the price', 'type' => 'text', 'help' => 'e.g. "all-inclusive". The technique name is added automatically.'],
                'cta_label' => ['label' => 'Default button label', 'type' => 'text'],
                'cta_url' => ['label' => 'Button URL', 'type' => 'url', 'help' => 'Leave empty to link to the contact page.'],
                'payment_label' => ['label' => 'Payment methods label', 'type' => 'text', 'help' => 'e.g. "We accept"'],
            ],
            self::Versus => [
                'ours_header' => ['label' => 'Our column header', 'type' => 'text', 'help' => 'e.g. "Turkey · TurkeyMed"'],
                'theirs_header' => ['label' => 'Other column header', 'type' => 'text', 'help' => 'e.g. "United States"'],
                'footnote' => ['label' => 'Footnote', 'type' => 'textarea'],
            ],
            self::Promise => [
                'intro' => ['label' => 'Opening paragraph', 'type' => 'textarea'],
                'closing' => ['label' => 'Closing paragraph', 'type' => 'textarea'],
                'ask_eyebrow' => ['label' => 'Question box: eyebrow', 'type' => 'text'],
                'ask_quote' => ['label' => 'Question box: the question', 'type' => 'textarea'],
                'ask_answer' => ['label' => 'Question box: explanation', 'type' => 'textarea'],
            ],
            self::Procedure => [
                'advantages_title' => ['label' => 'Advantages card title', 'type' => 'text', 'help' => 'e.g. "Advantages of the FUE method"'],
                'link_label' => ['label' => 'Link label', 'type' => 'text', 'help' => 'e.g. "Read the full FUE guide"'],
                'link_url' => ['label' => 'Link URL', 'type' => 'url', 'help' => 'The link is hidden when empty.'],
            ],
            self::Timeline => [
                'chart_title' => ['label' => 'Chart title', 'type' => 'text', 'help' => 'e.g. "Visible result over 12 months"'],
                'legend_label' => ['label' => 'Legend label', 'type' => 'text', 'help' => 'e.g. "Typical growth curve"'],
                'hint' => ['label' => 'Hint', 'type' => 'text', 'help' => 'e.g. "tap a milestone"'],
                'tip_label' => ['label' => 'Tip label', 'type' => 'text', 'help' => 'e.g. "Your part:"'],
                'footnote' => ['label' => 'Footnote', 'type' => 'textarea'],
            ],
            self::Blog => [
                'link_label' => ['label' => '"All articles" link label', 'type' => 'text'],
                'posts_count' => ['label' => 'Number of articles', 'type' => 'number', 'help' => 'Default 3.'],
            ],
            self::Cta => [
                'cta_label' => ['label' => 'Main button label', 'type' => 'text'],
                'cta_url' => ['label' => 'Main button URL', 'type' => 'url', 'help' => 'Leave empty to link to the contact page.'],
            ],
            default => [],
        };
    }

    /**
     * Bullet-list groups (cost_page_items.group) this section owns.
     *
     * @return array<string, array{label: string, has_body: bool}>
     */
    public function itemGroups(): array
    {
        return match ($this) {
            self::Calculator => [
                'badges' => ['label' => 'Hero badges', 'has_body' => false],
            ],
            self::Compare => [
                'save_points' => ['label' => 'Savings card bullet points', 'has_body' => false],
            ],
            self::Packages => [
                'payment_methods' => ['label' => 'Payment methods', 'has_body' => false],
            ],
            self::Promise => [
                'promise_points' => ['label' => 'Numbered points', 'has_body' => true],
            ],
            self::Procedure => [
                'advantages' => ['label' => 'Advantages list', 'has_body' => false],
            ],
            default => [],
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $key): string => $key->value, self::cases());
    }
}
