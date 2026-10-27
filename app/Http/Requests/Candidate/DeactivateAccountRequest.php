<?php

namespace App\Http\Requests\Candidate;

class DeactivateAccountRequest extends CandidateFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'confirm' => ['accepted'],
        ];
    }
}
