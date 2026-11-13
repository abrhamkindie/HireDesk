<?php

namespace App\Observers;

use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class ApplicationObserver
{
    public function created(Application $application): void
    {
        $actor = Auth::guard('sanctum')->user();

        $application->statusHistory()->create([
            'old_status' => null,
            'new_status' => $application->status,
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
            'note' => 'Application submitted.',
        ]);
    }
}
