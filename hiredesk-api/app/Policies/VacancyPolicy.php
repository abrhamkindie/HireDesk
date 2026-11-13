<?php

namespace App\Policies;

use App\Enums\VacancyStatus;
use App\Models\Candidate;
use App\Models\User;
use App\Models\Vacancy;

class VacancyPolicy
{
    public function viewAny(User|Candidate $user): bool
    {
        return $user instanceof User && $user->isStaff();
    }

    public function view(User|Candidate $user, Vacancy $vacancy): bool
    {
        return $vacancy->status === VacancyStatus::Open
            || ($user instanceof User && $user->isStaff());
    }

    public function create(User|Candidate $user): bool
    {
        return $user instanceof User && $user->isStaff();
    }

    public function update(User|Candidate $user, Vacancy $vacancy): bool
    {
        return $user instanceof User && $user->isStaff();
    }

    public function delete(User|Candidate $user, Vacancy $vacancy): bool
    {
        return $user instanceof User && $user->isStaff();
    }
}
