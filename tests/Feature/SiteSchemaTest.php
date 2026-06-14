<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Support\Seo\SchemaBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSchemaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_homepage_emits_organization_and_website_json_ld(): void
    {
        Office::factory()->create([
            'is_primary' => true,
            'is_published' => true,
            'name' => ['en' => 'Istanbul'],
            'address' => ['en' => '123 Bağdat Caddesi'],
            'country' => ['en' => 'Türkiye'],
        ]);
        SocialLink::factory()->create([
            'platform' => 'instagram',
            'url' => 'https://instagram.com/turkeymed',
            'is_published' => true,
        ]);
        Setting::set('org.legal_name', 'TurkeyMed Health Ltd');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('"@type":"MedicalOrganization"', false);
        $response->assertSee('"@type":"WebSite"', false);
        $response->assertSee('TurkeyMed Health Ltd', false);
        $response->assertSee('https://instagram.com/turkeymed', false);
        $response->assertSee('123 Bağdat Caddesi', false);
    }

    public function test_organization_schema_strips_empty_fields(): void
    {
        $org = SchemaBuilder::organization('en');

        $this->assertSame('MedicalOrganization', $org['@type']);
        $this->assertArrayHasKey('@id', $org);
        $this->assertArrayNotHasKey('legalName', $org);   // none set
        $this->assertArrayNotHasKey('address', $org);     // no office
        $this->assertArrayNotHasKey('sameAs', $org);      // no socials
    }

    public function test_primary_office_supplies_the_address(): void
    {
        Office::factory()->create([
            'is_primary' => false,
            'is_published' => true,
            'name' => ['en' => 'Branch'],
            'address' => ['en' => 'Branch Street'],
            'country' => ['en' => 'Türkiye'],
        ]);
        Office::factory()->create([
            'is_primary' => true,
            'is_published' => true,
            'name' => ['en' => 'HQ'],
            'address' => ['en' => 'HQ Avenue'],
            'country' => ['en' => 'Türkiye'],
        ]);

        $org = SchemaBuilder::organization('en');

        $this->assertSame('HQ Avenue', $org['address']['streetAddress']);
        $this->assertSame('HQ', $org['address']['addressLocality']);
    }

    public function test_website_references_the_organization_as_publisher(): void
    {
        $website = SchemaBuilder::website('en');

        $this->assertSame('WebSite', $website['@type']);
        $this->assertSame(url('/').'#organization', $website['publisher']['@id']);
    }
}
