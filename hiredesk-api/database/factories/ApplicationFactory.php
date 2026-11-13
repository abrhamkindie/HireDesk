<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vacancy_id' => Vacancy::factory(),
            'candidate_id' => Candidate::factory(),
            'cover_letter' => fake()->paragraph(),
            'resume_path' => 'resumes/'.fake()->uuid().'.pdf',
            'resume_disk' => 'local',
        ];
    }
}
