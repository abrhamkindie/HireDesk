<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\CandidateProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateProfile>
 */
class CandidateProfileFactory extends Factory
{
    protected $model = CandidateProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone' => fake()->phoneNumber(),
            'location' => fake()->city(),
            'headline' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'resume_path' => 'resumes/'.fake()->uuid().'.pdf',
            'linkedin_url' => 'https://www.linkedin.com/in/'.fake()->slug(),
            'portfolio_url' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (CandidateProfile $profile) {
            if ($profile->candidate_id === null) {
                $profile->forceFill([
                    'candidate_id' => Candidate::factory()->create()->getKey(),
                ]);
            }
        });
    }
}
