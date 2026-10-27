<?php

namespace App\Http\Requests\GroupAdmin;

class AddGroupCandidateRequest extends GroupAdminFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
        ];
    }
}
