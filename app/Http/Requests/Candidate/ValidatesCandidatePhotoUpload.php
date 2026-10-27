<?php

namespace App\Http\Requests\Candidate;

trait ValidatesCandidatePhotoUpload
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $birth = (int) $this->user()->candidate->year_of_birth;

        return [
            'photo' => ['required', 'file', 'max:8192', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp'],
            'photo_year' => ['required', 'integer', 'min:'.$birth, 'max:'.now()->year],
            'blur' => ['sometimes', 'boolean'],
            'crop_x' => ['required', 'integer', 'min:0', 'max:8000'],
            'crop_y' => ['required', 'integer', 'min:0', 'max:8000'],
            'crop_width' => ['required', 'integer', 'min:100', 'max:8000'],
            'crop_height' => ['required', 'integer', 'min:100', 'max:8000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.required' => 'Choose a photo to upload.',
            'photo.file' => 'Choose a photo to upload.',
            'photo.max' => 'Photos must be 8 MB or smaller.',
            'photo.mimes' => 'Only JPEG, PNG, and WebP photos can be uploaded.',
            'photo.extensions' => 'Only JPEG, PNG, and WebP photos can be uploaded.',
            'photo_year.min' => 'The photo year cannot be earlier than the year of birth.',
            'crop_x.required' => 'Crop the photo before saving it.',
            'crop_y.required' => 'Crop the photo before saving it.',
            'crop_width.required' => 'Crop the photo before saving it.',
            'crop_height.required' => 'Crop the photo before saving it.',
            'crop_width.min' => 'Crop the photo to an area at least 100 pixels wide and tall.',
            'crop_height.min' => 'Crop the photo to an area at least 100 pixels wide and tall.',
        ];
    }
}
