<?php

namespace Tests\Feature;

use App\Models\TreatmentCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreatmentCardLinkTest extends TestCase
{
    use RefreshDatabase;

    private function card(?string $url): TreatmentCard
    {
        return TreatmentCard::factory()->make(['url' => $url]);
    }

    public function test_internal_path_is_unprefixed_for_english(): void
    {
        app()->setLocale('en');

        $this->assertSame('/services/dental-clinic', $this->card('/services/dental-clinic')->href());
    }

    public function test_internal_path_is_locale_prefixed_for_non_default_language(): void
    {
        app()->setLocale('ar');

        $this->assertSame('/ar/services/dental-clinic', $this->card('/services/dental-clinic')->href('ar'));
    }

    public function test_href_uses_current_locale_when_none_given(): void
    {
        app()->setLocale('fr');

        $this->assertSame('/fr/services/dental-clinic', $this->card('/services/dental-clinic')->href());
    }

    public function test_path_that_already_has_a_locale_is_not_double_prefixed(): void
    {
        $this->assertSame('/ar/services/dental-clinic', $this->card('/ar/services/dental-clinic')->href('ar'));
    }

    public function test_external_url_is_left_untouched(): void
    {
        $this->assertSame('https://example.com/page', $this->card('https://example.com/page')->href('ar'));
    }

    public function test_anchor_link_is_not_prefixed(): void
    {
        $this->assertSame('#booking', $this->card('#booking')->href('ar'));
    }

    public function test_dangerous_scheme_is_neutralised(): void
    {
        $this->assertSame('#', $this->card('javascript:alert(1)')->href('ar'));
    }

    public function test_empty_url_falls_back_to_localised_contact_page(): void
    {
        app()->setLocale('ar');

        $this->assertSame(route('contact.localized', 'ar'), $this->card('')->href());
    }

    public function test_default_locale_empty_url_falls_back_to_contact(): void
    {
        app()->setLocale('en');

        $this->assertSame(route('contact'), $this->card(null)->href());
    }
}
