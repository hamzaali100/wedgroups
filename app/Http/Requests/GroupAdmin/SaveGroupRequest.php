<?php

namespace App\Http\Requests\GroupAdmin;

class SaveGroupRequest extends GroupAdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'male_pics_allowed' => $this->boolean('male_pics_allowed'),
            'female_pics_allowed' => $this->boolean('female_pics_allowed'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:150'],
            'male_pics_allowed' => ['boolean'],
            'female_pics_allowed' => ['boolean'],
        ];
    }
}
