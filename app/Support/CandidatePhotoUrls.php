<?php

namespace App\Support;

use App\Models\CandidatePhoto;

class CandidatePhotoUrls
{
    /**
     * @return array<string, mixed>
     */
    public static function ownerCard(CandidatePhoto $photo): array
    {
        return [
            'id' => $photo->id,
            'url' => self::forOwner($photo),
            'blurred_url' => $photo->blurred_photo ? self::forOwner($photo, 'blurred') : null,
            'year' => $photo->photo_year,
            'is_main' => $photo->is_main,
            ...self::reviewFields($photo),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function reviewCard(CandidatePhoto $photo): array
    {
        return [
            'id' => $photo->id,
            'url' => self::forOwner($photo),
            'year' => $photo->photo_year,
            'is_main' => $photo->is_main,
            ...self::reviewFields($photo),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function reviewFields(CandidatePhoto $photo): array
    {
        $photo->loadMissing('approver:id,name');

        return [
            'review_status' => $photo->review_status,
            'review_label' => CandidatePhoto::REVIEW_STATUSES[$photo->review_status] ?? $photo->review_status,
            'approved_by' => $photo->approver?->name,
            'approved_at' => $photo->approved_at?->timezone(config('app.timezone'))->format('j M Y, H:i'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function viewerCard(CandidatePhoto $photo, bool $photosBlurred): array
    {
        return [
            'id' => $photo->id,
            'url' => self::forViewer($photo, $photosBlurred),
            'year' => $photo->photo_year,
            'is_main' => $photo->is_main,
        ];
    }

    public static function forOwner(CandidatePhoto $photo, string $variant = 'clear'): string
    {
        return route('photos.show', [
            'photo' => $photo->getKey(),
            'variant' => $variant,
        ], absolute: false);
    }

    /**
     * `v` only keeps the browser from mixing clear and blurred copies.
     * The server decides which file to send.
     */
    public static function forViewer(CandidatePhoto $photo, bool $photosBlurred): string
    {
        return route('photos.show', [
            'photo' => $photo->getKey(),
            'v' => $photosBlurred ? 1 : 0,
        ], absolute: false);
    }
}
