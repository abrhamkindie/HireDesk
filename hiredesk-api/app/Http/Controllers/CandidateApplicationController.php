<?php

namespace App\Http\Controllers;

use App\Actions\TransitionApplicationStatus;
use App\Enums\ApplicationStatus;
use App\Enums\VacancyStatus;
use App\Http\Requests\ApplyToVacancyRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Vacancy;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class CandidateApplicationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $applications = $this->candidate($request)
            ->applications()
            ->with(['vacancy', 'statusHistory'])
            ->latest()
            ->paginate(15);

        return ApplicationResource::collection($applications);
    }

    public function store(
        ApplyToVacancyRequest $request,
        Vacancy $vacancy,
    ): JsonResponse {
        $candidate = $this->candidate($request);

        if ($vacancy->status !== VacancyStatus::Open
            || ($vacancy->deadline !== null && $vacancy->deadline->isPast())) {
            throw ValidationException::withMessages([
                'vacancy' => 'This vacancy is not open for applications.',
            ]);
        }

        if ($candidate->applications()->where('vacancy_id', $vacancy->getKey())->exists()) {
            throw ValidationException::withMessages([
                'vacancy' => 'You have already applied to this vacancy.',
            ]);
        }

        $resumePath = $candidate->profile?->resume_path;
        $resumeDisk = null;

        if ($file = $request->file('resume')) {
            $resumePath = $file->store('resumes/'.$candidate->getKey(), 'local');
            $resumeDisk = 'local';
        }

        try {
            $application = $candidate->applications()->create([
                'vacancy_id' => $vacancy->getKey(),
                'cover_letter' => $request->validated('cover_letter'),
                'resume_path' => $resumePath,
                'resume_disk' => $resumeDisk,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'vacancy' => 'You have already applied to this vacancy.',
            ]);
        }

        return (new ApplicationResource(
            $application->load(['vacancy', 'statusHistory'])
        ))->response()->setStatusCode(201);
    }

    public function withdraw(
        Request $request,
        Application $application,
        TransitionApplicationStatus $transition,
    ): ApplicationResource {
        $candidate = $this->candidate($request);

        $this->authorize('withdraw', $application);

        $application = $transition->handle(
            $application,
            ApplicationStatus::Withdrawn,
            $candidate,
            'Withdrawn by candidate.',
        );

        return new ApplicationResource($application->load(['vacancy', 'statusHistory']));
    }

    private function candidate(Request $request): Candidate
    {
        $user = $request->user();

        abort_unless($user instanceof Candidate, 403);

        return $user;
    }
}
