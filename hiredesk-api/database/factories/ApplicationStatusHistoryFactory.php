<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationStatusHistory>
 */
class ApplicationStatusHistoryFactory extends Factory
{
    protected $model = ApplicationStatusHistory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'old_status' => ApplicationStatus::New,
            'new_status' => ApplicationStatus::Screening,
            'actor_type' => User::class,
            'actor_id' => User::factory(),
            'note' => fake()->sentence(),
        ];
    }
}
