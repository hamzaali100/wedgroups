<?php

namespace App\Http\Requests\GroupAdmin;

use App\Models\Candidate;
use App\Models\Group;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FilterGroupCandidatesRequest extends GroupAdminFormRequest
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
            'education_level' => ['nullable', Rule::in(array_keys(Candidate::EDUCATION_LEVELS))],
            'membership_status' => ['nullable', Rule::in(array_keys(Group::MEMBERSHIP_STATUSES))],
            'age_min' => ['nullable', 'integer', 'between:18,80'],
            'age_max' => ['nullable', 'integer', 'between:18,80'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled(['age_min', 'age_max']) && (int) $this->input('age_max') < (int) $this->input('age_min')) {
                $validator->errors()->add('age_max', 'Maximum age must be at least the minimum age.');
            }
        });
    }
}
