<?php

namespace App\Http\Requests\Candidate;

class UpdateCareerDetailsRequest extends CandidateFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'occupation_id' => ['nullable', 'integer', 'exists:occupations,id'],
            'monthly_income' => ['nullable', 'string', 'max:300'],
            'career_details' => ['nullable', 'string', 'max:500'],
            'linkedin' => ['nullable', 'url', 'max:255'],
        ];
    }
}
