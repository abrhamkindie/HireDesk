<?php

namespace Database\Factories;

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

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'old_status' => 'new',
            'new_status' => 'screening',
            'changed_by' => User::factory(),
            'note' => fake()->sentence(),
        ];
    }
}
