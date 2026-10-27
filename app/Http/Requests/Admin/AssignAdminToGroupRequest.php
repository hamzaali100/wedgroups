<?php

namespace App\Http\Requests\Admin;

class AssignAdminToGroupRequest extends AdminFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'group_admin_id' => ['required', 'integer', 'exists:group_admins,user_id'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }
}
