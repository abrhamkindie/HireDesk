<?php

namespace App\Http\Controllers;

use App\Actions\TransitionApplicationStatus;
use App\Enums\ApplicationStatus;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminApplicationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Application::class);

        $applications = Application::query()
            ->with(['candidate.profile', 'vacancy'])
            ->when($request->filled('status'), fn (Builder $query) => $query
                ->where('status', $request->input('status')))
            ->when($request->filled('vacancy_id'), fn (Builder $query) => $query
                ->where('vacancy_id', $request->integer('vacancy_id')))
            ->when($request->filled('candidate_id'), fn (Builder $query) => $query
                ->where('candidate_id', $request->integer('candidate_id')))
            ->when($request->filled('search'), fn (Builder $query) => $query
                ->whereHas('candidate', fn (Builder $candidate) => $candidate
                    ->where('name', 'like', '%'.$request->input('search').'%')
                    ->orWhere('email', 'like', '%'.$request->input('search').'%')))
            ->latest()
            ->paginate($this->perPage($request));

        return ApplicationResource::collection($applications);
    }

    public function show(Application $application): ApplicationResource
    {
        $this->authorize('view', $application);

        return new ApplicationResource(
            $application->load(['candidate.profile', 'vacancy', 'statusHistory'])
        );
    }

    public function updateStatus(
        UpdateApplicationStatusRequest $request,
        Application $application,
        TransitionApplicationStatus $transition,
    ): ApplicationResource {
        $this->authorize('updateStatus', $application);

        $validated = $request->validated();

        $application = $transition->handle(
            $application,
            ApplicationStatus::from($validated['status']),
            $request->user(),
            $validated['note'] ?? null,
        );

        return new ApplicationResource(
            $application->load(['candidate.profile', 'vacancy', 'statusHistory'])
        );
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 15), 1), 100);
    }
}
