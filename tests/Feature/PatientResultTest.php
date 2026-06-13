<?php

namespace Tests\Feature;

use App\Models\PatientResult;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientResultTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_service_page_shows_results_from_its_category(): void
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->inCategory($category)->create();
        PatientResult::factory()->inCategory($category)->create(['grafts_count' => 4200, 'months_to_result' => 12]);

        $response = $this->get('/'.$service->slug);

        $response->assertOk();
        $response->assertSee(__('patient_results.eyebrow'));
        $response->assertSee(__('patient_results.consent_note'));
        $response->assertSee(trans_choice('patient_results.grafts', 4200, ['count' => number_format(4200)]));
        $response->assertSee(trans_choice('patient_results.months', 12, ['count' => 12]));
    }

    public function test_results_section_is_hidden_when_there_are_none(): void
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->inCategory($category)->create();

        $response = $this->get('/'.$service->slug);

        $response->assertOk();
        $response->assertDontSee(__('patient_results.consent_note'));
    }

    public function test_unpublished_and_unconsented_results_never_appear(): void
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->inCategory($category)->create();

        PatientResult::factory()->inCategory($category)->unpublished()->create();
        PatientResult::factory()->inCategory($category)->unconsented()->create();
        // Defence in depth: published flag set but consent missing — scope must still exclude it.
        PatientResult::factory()->inCategory($category)->create(['consent_confirmed' => false]);

        $response = $this->get('/'.$service->slug);

        $response->assertOk();
        $response->assertDontSee(__('patient_results.consent_note'));
    }

    public function test_results_pinned_to_a_service_only_show_on_that_service(): void
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->inCategory($category)->create();
        $sibling = Service::factory()->inCategory($category)->create();

        $pinned = PatientResult::factory()->forService($service)->create();

        $this->assertTrue($service->patientResults()->pluck('id')->contains($pinned->id));
        $this->assertFalse($sibling->patientResults()->pluck('id')->contains($pinned->id));
    }

    public function test_shared_category_results_show_on_every_service_in_the_category(): void
    {
        $category = ServiceCategory::factory()->create();
        $serviceA = Service::factory()->inCategory($category)->create();
        $serviceB = Service::factory()->inCategory($category)->create();

        $shared = PatientResult::factory()->inCategory($category)->create();

        $this->assertTrue($serviceA->patientResults()->pluck('id')->contains($shared->id));
        $this->assertTrue($serviceB->patientResults()->pluck('id')->contains($shared->id));
    }

    public function test_results_respect_manual_sort_order(): void
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->inCategory($category)->create();

        $second = PatientResult::factory()->inCategory($category)->create(['sort_order' => 5]);
        $first = PatientResult::factory()->inCategory($category)->create(['sort_order' => 1]);

        $this->assertSame(
            [$first->id, $second->id],
            $service->patientResults()->pluck('id')->all(),
        );
    }
}
