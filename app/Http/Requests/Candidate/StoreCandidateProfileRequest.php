<?php

namespace App\Http\Requests\Candidate;

use App\Models\Candidate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCandidateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->role === 'candidate'
            && $user->status === 'active'
            && ! $user->candidate()->exists();
    }

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
        ];
    }
}
