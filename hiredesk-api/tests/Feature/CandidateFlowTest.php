<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_register(): void
    {
        $this->postJson('/api/candidate/register', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'tests',
        ])
            ->assertCreated()
            ->assertJsonStructure(['candidate' => ['id', 'email'], 'token']);

        $this->assertDatabaseHas('candidates', ['email' => 'ada@example.com']);
    }

    public function test_public_listing_only_returns_open_vacancies(): void
    {
        Vacancy::factory()->open()->create();
        Vacancy::factory()->create();

        $this->getJson('/api/vacancies')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_candidate_can_apply_and_withdraw(): void
    {
        $candidate = $this->candidateWithResume();
        $vacancy = Vacancy::factory()->open()->create();

        $response = $this->actingAs($candidate, 'sanctum')
            ->postJson("/api/vacancies/{$vacancy->slug}/apply", [
                'cover_letter' => 'I would like to join.',
            ]);

        $response->assertCreated()->assertJsonPath('data.status', 'new');

        $applicationId = $response->json('data.id');

        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $applicationId,
            'new_status' => 'new',
            'actor_type' => Candidate::class,
            'actor_id' => $candidate->getKey(),
        ]);

        $this->actingAs($candidate, 'sanctum')
            ->patchJson("/api/candidate/applications/{$applicationId}/withdraw")
            ->assertOk()
            ->assertJsonPath('data.status', 'withdrawn');
    }

    public function test_candidate_cannot_apply_twice(): void
    {
        $candidate = $this->candidateWithResume();
        $vacancy = Vacancy::factory()->open()->create();

        Application::factory()->create([
            'vacancy_id' => $vacancy->getKey(),
            'candidate_id' => $candidate->getKey(),
        ]);

        $this->actingAs($candidate, 'sanctum')
            ->postJson("/api/vacancies/{$vacancy->slug}/apply")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('vacancy');
    }

    public function test_candidate_cannot_withdraw_another_candidates_application(): void
    {
        $application = Application::factory()->create();
        $other = Candidate::factory()->create();

        $this->actingAs($other, 'sanctum')
            ->patchJson("/api/candidate/applications/{$application->id}/withdraw")
            ->assertForbidden();
    }

    private function candidateWithResume(): Candidate
    {
        $candidate = Candidate::factory()->create();
        $candidate->profile()->create(['resume_path' => 'resumes/cv.pdf']);

        return $candidate;
    }
}
