<?php

namespace App\Http\Requests\Candidate;

class UpdateFamilyDetailsRequest extends CandidateFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'total_siblings' => ['required', 'integer', 'between:0,30'],
            'siblings_description' => ['nullable', 'string', 'max:500'],
            'father_occupation' => ['nullable', 'string', 'max:150'],
            'mother_occupation' => ['nullable', 'string', 'max:150'],
            'family_details' => ['nullable', 'string', 'max:500'],
            'parent_contact_number' => ['nullable', 'string', 'max:30'],
        ];
    }
}
