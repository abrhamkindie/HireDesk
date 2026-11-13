<?php

namespace App\Http\Requests;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Enums\WorkplaceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $salaryMaxRules = ['nullable', 'numeric', 'min:0'];

        if ($this->filled('salary_min')) {
            $salaryMaxRules[] = 'gte:salary_min';
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'workplace_type' => ['required', Rule::enum(WorkplaceType::class)],
            'location' => ['nullable', 'string', 'max:255'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => $salaryMaxRules,
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['required', Rule::enum(VacancyStatus::class)],
        ];
    }
}
