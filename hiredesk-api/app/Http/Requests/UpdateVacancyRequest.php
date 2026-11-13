<?php

namespace App\Http\Requests;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Enums\WorkplaceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVacancyRequest extends FormRequest
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
        $salaryMaxRules = ['sometimes', 'nullable', 'numeric', 'min:0'];

        if ($this->filled('salary_min')) {
            $salaryMaxRules[] = 'gte:salary_min';
        }

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'requirements' => ['sometimes', 'nullable', 'string'],
            'employment_type' => ['sometimes', 'required', Rule::enum(EmploymentType::class)],
            'workplace_type' => ['sometimes', 'required', Rule::enum(WorkplaceType::class)],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'salary_min' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'salary_max' => $salaryMaxRules,
            'deadline' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'required', Rule::enum(VacancyStatus::class)],
        ];
    }
}
