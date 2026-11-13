<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Enums\WorkplaceType;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vacancy>
 */
class VacancyFactory extends Factory
{
    protected $model = Vacancy::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->jobTitle(),
            'slug' => fn (array $attributes) => Str::slug($attributes['title']).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->paragraph(),
            'requirements' => fake()->optional()->paragraph(),
            'employment_type' => EmploymentType::FullTime,
            'workplace_type' => WorkplaceType::Onsite,
            'location' => fake()->city(),
            'salary_min' => fake()->numberBetween(30000, 60000),
            'salary_max' => fake()->numberBetween(60001, 120000),
            'deadline' => now()->addMonth(),
            'status' => VacancyStatus::Draft,
            'created_by' => User::factory(),
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => [
            'status' => VacancyStatus::Open,
            'published_at' => now(),
        ]);
    }
}
