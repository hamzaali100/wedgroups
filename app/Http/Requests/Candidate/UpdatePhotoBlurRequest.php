<?php

namespace App\Http\Requests\Candidate;

class UpdatePhotoBlurRequest extends CandidateFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'photos_blurred' => ['required', 'boolean'],
        ];
    }
}
