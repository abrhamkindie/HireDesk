<?php

namespace App\Http\Controllers;

use App\Enums\VacancyStatus;
use App\Http\Resources\VacancyResource;
use App\Models\Vacancy;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicVacancyController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $vacancies = Vacancy::query()
            ->open()
            ->withCount('applications')
            ->latest('published_at')
            ->paginate(15);

        return VacancyResource::collection($vacancies);
    }

    public function show(Vacancy $vacancy): VacancyResource
    {
        abort_unless($vacancy->status === VacancyStatus::Open, 404);

        return new VacancyResource($vacancy->loadCount('applications'));
    }
}
