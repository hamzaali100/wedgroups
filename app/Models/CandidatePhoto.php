<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['candidate_id', 'photo', 'blurred_photo', 'photo_year', 'is_main', 'sort_order', 'review_status'])]
class CandidatePhoto extends Model
{
    public const REVIEW_STATUSES = [
        'pending' => 'Waiting for approval',
        'approved' => 'Approved',
    ];

    protected $table = 'candidate_photos';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'photo_year' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id', 'user_id');
    }
}
