<?php

namespace Tests\Feature;

use App\Actions\TransitionApplicationStatus;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\Candidate;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_vacancies_use_created_by(): void
    {
        $hr = User::factory()->create();
        $vacancy = Vacancy::factory()->create(['created_by' => $hr->getKey()]);

        $this->assertTrue($hr->vacancies()->whereKey($vacancy->getKey())->exists());
    }

    public function test_vacancy_candidates_are_reachable_through_applications(): void
    {
        $vacancy = Vacancy::factory()->create();
        $candidate = Candidate::factory()->create();

        Application::factory()->create([
            'vacancy_id' => $vacancy->getKey(),
            'candidate_id' => $candidate->getKey(),
        ]);

        $this->assertTrue($vacancy->candidates()->whereKey($candidate->getKey())->exists());
        $this->assertSame('new', $vacancy->candidates()->first()->pivot->status);
    }

    public function test_history_can_be_attributed_to_a_candidate(): void
    {
        $candidate = Candidate::factory()->create();
        $application = Application::factory()->create(['candidate_id' => $candidate->getKey()]);

        app(TransitionApplicationStatus::class)
            ->handle($application, ApplicationStatus::Withdrawn, $candidate);

        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->getKey(),
            'new_status' => 'withdrawn',
            'actor_type' => Candidate::class,
            'actor_id' => $candidate->getKey(),
        ]);
    }

    public function test_status_history_cannot_be_updated(): void
    {
        $history = ApplicationStatusHistory::factory()->create();

        $this->expectException(LogicException::class);

        $history->update(['note' => 'Changed.']);
    }

    public function test_status_history_cannot_be_deleted(): void
    {
        $history = ApplicationStatusHistory::factory()->create();

        $this->expectException(LogicException::class);

        $history->delete();
    }
}
