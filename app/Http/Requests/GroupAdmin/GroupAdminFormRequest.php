<?php

namespace App\Http\Requests\GroupAdmin;

use Illuminate\Foundation\Http\FormRequest;

abstract class GroupAdminFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->role === 'group_admin'
            && $user->status === 'active';
    }
}
