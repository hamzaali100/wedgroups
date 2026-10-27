<?php

namespace App\Http\Requests\Candidate;

use App\Models\Candidate;
use Illuminate\Validation\Rule;

class UpdatePersonalDetailsRequest extends CandidateFormRequest
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
            'sect_id' => [
                'nullable',
                'integer',
                Rule::exists('sects', 'id')->where('religion_id', $this->integer('religion_id')),
            ],
            'ethnicity_id' => [
                'nullable',
                'integer',
                Rule::exists('ethnicities', 'id')->where('country_id', $this->string('country_id')->toString()),
            ],
            'ethnicity' => ['nullable', 'string', 'max:100'],
            'caste' => ['nullable', 'string', 'max:100'],
            'other_details' => ['nullable', 'string', 'max:700'],
            'disabilities' => ['nullable', 'string', 'max:500'],
            'country_id' => ['required', 'string', 'size:3', 'exists:countries,id'],
            'state_id' => [
                'nullable',
                'string',
                'max:7',
                Rule::exists('states', 'id')->where('country_id', $this->string('country_id')->toString()),
            ],
            'city_id' => [
                'nullable',
                'string',
                'max:11',
                Rule::exists('cities', 'id')->where('state_id', (string) $this->input('state_id')),
            ],
            'residence_details' => ['nullable', 'string', 'max:150'],
            'grew_up_in' => ['nullable', 'string', 'size:3', 'exists:countries,id'],
            'nationality_ids' => ['array', 'max:5'],
            'nationality_ids.*' => ['string', 'size:3', 'distinct', 'exists:countries,id'],
            'has_kids' => ['nullable', 'boolean'],
            'wants_children' => ['nullable', Rule::in(array_keys(Candidate::WANTS_CHILDREN))],
            'willing_to_move_abroad' => ['nullable', 'boolean'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'contact_relationship' => ['nullable', 'string', 'max:50'],
            'contact_phone_number' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:254'],
        ];
    }
}
