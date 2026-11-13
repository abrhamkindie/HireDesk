<?php

namespace App\Actions;

use App\Enums\ApplicationStatus;
use App\Events\ApplicationStatusChanged;
use App\Models\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionApplicationStatus
{
    public function handle(
        Application $application,
        ApplicationStatus $status,
        ?Model $actor = null,
        ?string $note = null,
    ): Application {
        return DB::transaction(function () use ($application, $status, $actor, $note) {
            $application = Application::query()
                ->lockForUpdate()
                ->findOrFail($application->getKey());

            $current = $application->status;

            if ($current === $status) {
                throw ValidationException::withMessages([
                    'status' => "The application is already in the {$status->value} stage.",
                ]);
            }

            if (! $current->canTransitionTo($status)) {
                throw ValidationException::withMessages([
                    'status' => "An application cannot move from {$current->value} to {$status->value}.",
                ]);
            }

            $application->forceFill([
                'status' => $status,
                'status_updated_at' => now(),
            ])->save();

            $application->statusHistory()->create([
                'old_status' => $current,
                'new_status' => $status,
                'actor_type' => $actor?->getMorphClass(),
                'actor_id' => $actor?->getKey(),
                'note' => $note,
            ]);

            event(new ApplicationStatusChanged($application, $current, $status, $actor));

            return $application->refresh();
        });
    }
}
