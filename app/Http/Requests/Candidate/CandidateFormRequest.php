<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;

abstract class CandidateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->role === 'candidate'
            && $user->status === 'active'
            && $user->candidate !== null;
    }
}
