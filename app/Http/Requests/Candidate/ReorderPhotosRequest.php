<?php

namespace App\Http\Requests\Candidate;

class ReorderPhotosRequest extends CandidateFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order' => ['required', 'array', 'min:1', 'max:6'],
            'order.*' => ['integer', 'distinct'],
        ];
    }
}
