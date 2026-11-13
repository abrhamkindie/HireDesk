<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User|Candidate $user): bool
    {
        return $user instanceof User && $user->isStaff();
    }

    public function view(User|Candidate $user, Application $application): bool
    {
        if ($user instanceof User) {
            return $user->isStaff();
        }

        return $application->candidate_id === $user->getKey();
    }

    public function updateStatus(User|Candidate $user, Application $application): bool
    {
        return $user instanceof User && $user->isStaff();
    }

    public function withdraw(User|Candidate $user, Application $application): bool
    {
        return $user instanceof Candidate
            && $application->candidate_id === $user->getKey()
            && ! $application->status->isFinal();
    }
}
