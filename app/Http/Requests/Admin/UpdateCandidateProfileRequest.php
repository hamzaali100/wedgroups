<?php

namespace App\Http\Requests\Admin;

use App\Models\Candidate;
use Illuminate\Validation\Rule;

class UpdateCandidateProfileRequest extends AdminFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'real_name' => ['required', 'string', 'max:150'],
            'display_name' => ['required', 'string', 'max:100'],
            'year_of_birth' => ['required', 'integer', 'min:1940', 'max:'.(now()->year - 18)],
            'month_of_birth' => ['required', 'integer', 'between:1,12'],
            'gender' => ['required', Rule::in(array_keys(Candidate::GENDERS))],
            'height' => ['required', 'integer', 'between:120,230'],
            'marital_status' => ['required', Rule::in(array_keys(Candidate::MARITAL_STATUSES))],
            'religion_id' => ['required', 'integer', 'exists:religions,id'],
            'country_id' => ['required', 'string', 'size:3', 'exists:countries,id'],
            'education_level' => ['required', Rule::in(array_keys(Candidate::EDUCATION_LEVELS))],
            'universities_names' => ['nullable', 'string', 'max:500'],
            'schools_names' => ['nullable', 'string', 'max:500'],
            'monthly_income' => ['nullable', 'string', 'max:300'],
            'career_details' => ['nullable', 'string', 'max:500'],
            'total_siblings' => ['nullable', 'integer', 'between:0,30'],
            'siblings_description' => ['nullable', 'string', 'max:500'],
            'father_occupation' => ['nullable', 'string', 'max:150'],
            'mother_occupation' => ['nullable', 'string', 'max:150'],
            'family_details' => ['nullable', 'string', 'max:500'],
            'parent_contact_number' => ['nullable', 'string', 'max:30'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'other_details' => ['nullable', 'string', 'max:700'],
            'disabilities' => ['nullable', 'string', 'max:500'],
            'partner_requirement_details' => ['nullable', 'string', 'max:500'],
            'profile_visibility' => ['required', Rule::in(array_keys(Candidate::PROFILE_VISIBILITIES))],
            'profile_status' => ['required', Rule::in(array_keys(Candidate::PROFILE_STATUSES))],
        ];
    }
}
