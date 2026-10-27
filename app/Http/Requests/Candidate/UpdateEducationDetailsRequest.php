<?php

namespace App\Http\Requests\Candidate;

use App\Models\Candidate;
use Illuminate\Validation\Rule;

class UpdateEducationDetailsRequest extends CandidateFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'education_level' => ['required', Rule::in(array_keys(Candidate::EDUCATION_LEVELS))],
            'universities_names' => ['nullable', 'string', 'max:500'],
            'schools_names' => ['nullable', 'string', 'max:500'],
        ];
    }
}
