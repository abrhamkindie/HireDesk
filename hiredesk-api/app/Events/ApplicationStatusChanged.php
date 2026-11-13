<?php

namespace App\Events;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Application $application,
        public ApplicationStatus $oldStatus,
        public ApplicationStatus $newStatus,
        public ?Model $actor = null,
    ) {}
}
