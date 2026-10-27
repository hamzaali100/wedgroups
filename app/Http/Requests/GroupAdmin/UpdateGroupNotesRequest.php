<?php

namespace App\Http\Requests\GroupAdmin;

class UpdateGroupNotesRequest extends GroupAdminFormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->exists('notes')) {
            return;
        }

        $notes = trim((string) $this->input('notes'));

        $this->merge([
            'notes' => $notes === '' ? null : $notes,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notes' => ['required', 'string', 'max:1000'],
        ];
    }
}
