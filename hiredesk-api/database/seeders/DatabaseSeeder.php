<?php

namespace Database\Seeders;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Enums\WorkplaceType;
use App\Models\Candidate;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::findOrCreate('admin', 'web');
        $hrRole = Role::findOrCreate('hr', 'web');

        $admin = User::factory()->create([
            'name' => 'HireDesk Admin',
            'email' => 'admin@hiredesk.test',
        ]);
        $admin->assignRole($adminRole);

        $hr = User::factory()->create([
            'name' => 'HireDesk HR',
            'email' => 'hr@hiredesk.test',
        ]);
        $hr->assignRole($hrRole);

        $candidate = Candidate::factory()->create([
            'name' => 'Demo Candidate',
            'email' => 'candidate@hiredesk.test',
        ]);
        $candidate->profile()->create([
            'phone' => '+1 555 0100',
            'location' => 'Remote',
            'headline' => 'Laravel Developer',
            'bio' => 'Demo candidate seeded for API testing.',
            'resume_path' => 'resumes/demo-candidate.pdf',
        ]);

        Vacancy::factory()->create([
            'title' => 'Senior Laravel Engineer',
            'employment_type' => EmploymentType::FullTime,
            'workplace_type' => WorkplaceType::Remote,
            'status' => VacancyStatus::Open,
            'published_at' => now(),
            'created_by' => $admin->getKey(),
        ]);
    }
}
