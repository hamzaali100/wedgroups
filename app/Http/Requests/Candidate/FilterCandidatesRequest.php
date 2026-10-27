<?php

namespace App\Http\Requests\Candidate;

use App\Models\Candidate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FilterCandidatesRequest extends CandidateFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'display_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(array_keys(Candidate::GENDERS))],
            'marital_status' => ['nullable', Rule::in(array_keys(Candidate::MARITAL_STATUSES))],
            'religion_id' => ['nullable', 'integer', 'exists:religions,id'],
            'country_id' => ['nullable', 'string', 'size:3', 'exists:countries,id'],
            'education_level' => ['nullable', Rule::in(array_keys(Candidate::EDUCATION_LEVELS))],
            'age_min' => ['nullable', 'integer', 'between:18,80'],
            'age_max' => ['nullable', 'integer', 'between:18,80'],
            'height_min' => ['nullable', 'integer', 'between:120,230'],
            'height_max' => ['nullable', 'integer', 'between:120,230'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled(['age_min', 'age_max']) && (int) $this->input('age_max') < (int) $this->input('age_min')) {
                $validator->errors()->add('age_max', 'Maximum age must be at least the minimum age.');
            }

            if ($this->filled(['height_min', 'height_max']) && (int) $this->input('height_max') < (int) $this->input('height_min')) {
                $validator->errors()->add('height_max', 'Maximum height must be at least the minimum height.');
            }
        });
    }
}
