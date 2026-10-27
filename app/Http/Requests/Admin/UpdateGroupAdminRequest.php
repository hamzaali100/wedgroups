<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class UpdateGroupAdminRequest extends AdminFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $groupAdmin = $this->route('groupAdmin');

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($groupAdmin?->user_id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
