<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVacancyRequest;
use App\Http\Requests\UpdateVacancyRequest;
use App\Http\Resources\VacancyResource;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminVacancyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Vacancy::class);

        $vacancies = Vacancy::query()
            ->when($request->filled('status'), fn (Builder $query) => $query
                ->where('status', $request->input('status')))
            ->when($request->filled('employment_type'), fn (Builder $query) => $query
                ->where('employment_type', $request->input('employment_type')))
            ->when($request->filled('search'), fn (Builder $query) => $query
                ->where('title', 'like', '%'.$request->input('search').'%'))
            ->withCount('applications')
            ->latest()
            ->paginate($this->perPage($request));

        return VacancyResource::collection($vacancies);
    }

    public function store(StoreVacancyRequest $request): JsonResponse
    {
        $this->authorize('create', Vacancy::class);

        $vacancy = Vacancy::create([
            ...$request->validated(),
            'created_by' => $request->user()->getKey(),
        ]);

        return (new VacancyResource($vacancy))->response()->setStatusCode(201);
    }

    public function show(Vacancy $vacancy): VacancyResource
    {
        $this->authorize('view', $vacancy);

        return new VacancyResource($vacancy->loadCount('applications'));
    }

    public function update(UpdateVacancyRequest $request, Vacancy $vacancy): VacancyResource
    {
        $this->authorize('update', $vacancy);

        $vacancy->update($request->validated());

        return new VacancyResource($vacancy);
    }

    public function destroy(Vacancy $vacancy): JsonResponse
    {
        $this->authorize('delete', $vacancy);

        $vacancy->delete();

        return response()->json(null, 204);
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 15), 1), 100);
    }
}
