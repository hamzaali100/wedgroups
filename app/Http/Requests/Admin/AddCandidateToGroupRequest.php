<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class AddCandidateToGroupRequest extends AdminFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'status' => ['required', Rule::in(['pending', 'active'])],
        ];
    }
}
