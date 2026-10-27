<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rules\Password;

class ResetUserPasswordRequest extends AdminFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];
    }
}
