<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends CandidateFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];
    }
}
