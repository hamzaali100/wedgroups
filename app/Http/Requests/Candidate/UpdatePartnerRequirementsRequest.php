<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Validation\Validator;

class UpdatePartnerRequirementsRequest extends CandidateFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'partner_age_min' => ['nullable', 'integer', 'between:18,80'],
            'partner_age_max' => ['nullable', 'integer', 'between:18,80'],
            'partner_height_min' => ['nullable', 'integer', 'between:120,230'],
            'partner_height_max' => ['nullable', 'integer', 'between:120,230'],
            'partner_requirement_details' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $ageMin = $this->input('partner_age_min');
            $ageMax = $this->input('partner_age_max');

            if ($ageMin !== null && $ageMax !== null && (int) $ageMax < (int) $ageMin) {
                $validator->errors()->add('partner_age_max', 'Maximum age must be at least the minimum age.');
            }

            $heightMin = $this->input('partner_height_min');
            $heightMax = $this->input('partner_height_max');

            if ($heightMin !== null && $heightMax !== null && (int) $heightMax < (int) $heightMin) {
                $validator->errors()->add('partner_height_max', 'Maximum height must be at least the minimum height.');
            }
        });
    }
}
