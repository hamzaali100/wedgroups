<?php

namespace App\Support;

use App\Models\CandidatePhoto;
use App\Models\User;

class CandidatePhotoApproval
{
    /**
     * Record the first approval. A later approval leaves the original person and time in place.
     */
    public static function approve(CandidatePhoto $photo, User $approver): bool
    {
        if ($photo->review_status === 'approved') {
            return false;
        }

        $photo->update([
            'review_status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return true;
    }
}
