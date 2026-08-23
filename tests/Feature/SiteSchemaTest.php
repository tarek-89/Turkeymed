<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Service;
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

    public function test_medical_web_page_emits_enriched_procedure_fields_when_set(): void
    {
        $service = Service::factory()->create([
            'meta_description' => 'A hair restoration procedure.',
            'procedure_type' => 'surgical',
            'procedure_body_location' => 'Scalp',
            'procedure_how_performed' => 'Follicular units are extracted and implanted.',
            'procedure_preparation' => 'Avoid blood thinners for one week.',
            'procedure_followup' => 'Gentle washing after 48 hours.',
            'procedure_expected_prognosis' => 'Full regrowth within 12 months.',
        ]);

        $page = SchemaBuilder::medicalWebPage($service, 'en');
        $procedure = $page['about'];

        // "surgical" promotes the type to the valid SurgicalProcedure subtype.
        $this->assertSame('SurgicalProcedure', $procedure['@type']);
        $this->assertSame('Scalp', $procedure['bodyLocation']);
        $this->assertSame('Follicular units are extracted and implanted.', $procedure['howPerformed']);
        $this->assertSame('Avoid blood thinners for one week.', $procedure['preparation']);
        $this->assertSame('Gentle washing after 48 hours.', $procedure['followup']);

        // Expected prognosis has no native MedicalProcedure property, so it is
        // folded into the description rather than dropped.
        $this->assertStringContainsString('Full regrowth within 12 months.', $procedure['description']);

        // Medical review trust signal.
        $this->assertArrayHasKey('lastReviewed', $page);
    }

    public function test_procedure_type_maps_to_valid_schema_org_forms(): void
    {
        $noninvasive = Service::factory()->create(['procedure_type' => 'noninvasive']);
        $percutaneous = Service::factory()->create(['procedure_type' => 'percutaneous']);

        $noninvasiveProcedure = SchemaBuilder::medicalWebPage($noninvasive, 'en')['about'];
        $percutaneousProcedure = SchemaBuilder::medicalWebPage($percutaneous, 'en')['about'];

        $this->assertSame('MedicalProcedure', $noninvasiveProcedure['@type']);
        $this->assertSame('https://schema.org/NoninvasiveProcedure', $noninvasiveProcedure['procedureType']);

        $this->assertSame('MedicalProcedure', $percutaneousProcedure['@type']);
        $this->assertSame('https://schema.org/PercutaneousProcedure', $percutaneousProcedure['procedureType']);
    }

    public function test_medical_web_page_strips_empty_procedure_fields(): void
    {
        $service = Service::factory()->create([
            'meta_description' => 'A procedure with no extra detail.',
        ]);

        $procedure = SchemaBuilder::medicalWebPage($service, 'en')['about'];

        $this->assertSame('MedicalProcedure', $procedure['@type']);
        $this->assertArrayNotHasKey('bodyLocation', $procedure);
        $this->assertArrayNotHasKey('howPerformed', $procedure);
        $this->assertArrayNotHasKey('preparation', $procedure);
        $this->assertArrayNotHasKey('followup', $procedure);
        $this->assertArrayNotHasKey('procedureType', $procedure);
    }
}
