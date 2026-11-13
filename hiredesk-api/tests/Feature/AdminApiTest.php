<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('hr', 'web');
    }

    public function test_hr_can_create_a_vacancy(): void
    {
        $response = $this->actingAs($this->staff(), 'sanctum')->postJson('/api/admin/vacancies', [
            'title' => 'Backend Engineer',
            'description' => 'Build and maintain the API.',
            'employment_type' => 'fulltime',
            'workplace_type' => 'remote',
            'status' => 'open',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.slug', 'backend-engineer')
            ->assertJsonPath('data.workplace_type', 'remote');

        $this->assertNotNull($response->json('data.published_at'));
    }

    public function test_staff_can_log_in_and_use_the_token(): void
    {
        $hr = $this->staff();

        $response = $this->postJson('/api/staff/login', [
            'email' => $hr->email,
            'password' => 'password',
            'device_name' => 'phpunit',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'roles'], 'token']);

        $this->assertSame(['hr'], $response->json('user.roles'));

        $this->withToken($response->json('token'))
            ->getJson('/api/admin/applications')
            ->assertOk();
    }

    public function test_non_staff_user_cannot_log_in_as_staff(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/staff/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertForbidden();
    }

    public function test_staff_login_rejects_invalid_credentials(): void
    {
        $hr = $this->staff();

        $this->postJson('/api/staff/login', [
            'email' => $hr->email,
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_hr_can_list_applications(): void
    {
        Application::factory()->create();

        $this->actingAs($this->staff(), 'sanctum')
            ->getJson('/api/admin/applications')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_candidates_cannot_access_admin_endpoints(): void
    {
        $candidate = Candidate::factory()->create();

        $this->actingAs($candidate, 'sanctum')
            ->getJson('/api/admin/applications')
            ->assertForbidden();
    }

    public function test_staff_without_a_role_cannot_access_admin_endpoints(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/applications')
            ->assertForbidden();
    }

    public function test_hr_can_transition_an_application(): void
    {
        $application = Application::factory()->create();

        $this->actingAs($this->staff(), 'sanctum')
            ->patchJson("/api/admin/applications/{$application->id}/status", [
                'status' => 'screening',
                'note' => 'Proceeding to screening.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'screening');

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'screening',
        ]);
    }

    public function test_invalid_status_transition_returns_validation_error(): void
    {
        $application = Application::factory()->create();

        $this->actingAs($this->staff(), 'sanctum')
            ->patchJson("/api/admin/applications/{$application->id}/status", [
                'status' => 'hired',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    private function staff(string $role = 'hr'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
