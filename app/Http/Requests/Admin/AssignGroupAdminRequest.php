<?php

namespace App\Http\Requests\Admin;

class AssignGroupAdminRequest extends AdminFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }
}
