<?php

namespace Tests\Feature;

use App\Actions\TransitionApplicationStatus;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ApplicationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('hr', 'web');
    }

    public function test_creating_an_application_records_initial_history(): void
    {
        $application = Application::factory()->create();

        $this->assertSame(ApplicationStatus::New, $application->status);
        $this->assertNotNull($application->status_updated_at);

        $history = $application->statusHistory()->get();

        $this->assertCount(1, $history);
        $this->assertNull($history->first()->old_status);
        $this->assertSame(ApplicationStatus::New, $history->first()->new_status);
    }

    public function test_valid_transition_updates_status_and_records_actor(): void
    {
        $hr = User::factory()->create();
        $hr->assignRole('hr');

        $application = Application::factory()->create();

        $updated = app(TransitionApplicationStatus::class)
            ->handle($application, ApplicationStatus::Screening, $hr, 'Looks promising.');

        $this->assertSame(ApplicationStatus::Screening, $updated->status);

        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'old_status' => 'new',
            'new_status' => 'screening',
            'actor_type' => User::class,
            'actor_id' => $hr->id,
        ]);

        $this->assertDatabaseCount('application_status_histories', 2);
    }

    public function test_invalid_transition_is_rejected_without_changing_status(): void
    {
        $application = Application::factory()->create();

        try {
            app(TransitionApplicationStatus::class)->handle($application, ApplicationStatus::Hired);
            $this->fail('Expected a validation exception.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }

        $this->assertSame(ApplicationStatus::New, $application->fresh()->status);
        $this->assertDatabaseCount('application_status_histories', 1);
    }

    public function test_final_status_cannot_transition(): void
    {
        $hr = User::factory()->create();
        $hr->assignRole('hr');

        $application = Application::factory()->create();
        $transition = app(TransitionApplicationStatus::class);

        $transition->handle($application, ApplicationStatus::Rejected, $hr);

        $this->expectException(ValidationException::class);

        $transition->handle($application->fresh(), ApplicationStatus::Screening, $hr);
    }
}
