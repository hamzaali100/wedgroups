<?php

namespace App\Support;

use App\Models\Candidate;
use App\Models\CandidatePhoto;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CandidatePhotoAccess
{
    /**
     * Which file this person may receive. A query string cannot override the result
     * for anyone other than the owner or a platform admin.
     */
    public static function variant(User $user, CandidatePhoto $photo, mixed $requested): ?string
    {
        if ($user->status !== 'active') {
            return null;
        }

        $candidate = $photo->candidate;

        if ($candidate === null) {
            return null;
        }

        $asked = is_string($requested) ? $requested : null;

        if ($user->role === 'candidate' && $user->id === $photo->candidate_id) {
            if ($asked === 'blurred') {
                return $photo->blurred_photo === null ? null : 'blurred';
            }

            return 'clear';
        }

        if ($user->role === 'admin') {
            if ($asked === 'blurred' && $photo->blurred_photo !== null) {
                return 'blurred';
            }

            return 'clear';
        }

        $allowed = ($user->role === 'group_admin' && self::groupAdminCanView($user, $photo, $candidate))
            || ($user->role === 'candidate' && self::candidateCanView($user, $photo, $candidate));

        if (! $allowed) {
            return null;
        }

        if ($candidate->photos_blurred) {
            return $photo->blurred_photo === null ? null : 'blurred';
        }

        return 'clear';
    }

    public static function sharedGroupAllowsPhotos(Candidate $viewer, Candidate $subject): bool
    {
        $column = $subject->gender === 'male' ? 'groups.male_pics_allowed' : 'groups.female_pics_allowed';

        return DB::table('candidate_group as mine')
            ->join('candidate_group as theirs', 'theirs.group_id', '=', 'mine.group_id')
            ->join('groups', 'groups.id', '=', 'mine.group_id')
            ->where('mine.candidate_id', $viewer->user_id)
            ->where('theirs.candidate_id', $subject->user_id)
            ->where('mine.status', 'active')
            ->where('theirs.status', 'active')
            ->where('groups.status', 'active')
            ->where($column, true)
            ->exists();
    }

    private static function candidateCanView(User $user, CandidatePhoto $photo, Candidate $candidate): bool
    {
        if ($photo->review_status !== 'approved' || $user->candidate === null) {
            return false;
        }

        $visible = Candidate::query()
            ->visibleTo($user->candidate)
            ->whereKey($candidate->user_id)
            ->exists();

        return $visible && self::sharedGroupAllowsPhotos($user->candidate, $candidate);
    }

    private static function groupAdminCanView(User $user, CandidatePhoto $photo, Candidate $candidate): bool
    {
        if ($photo->review_status !== 'approved' || $user->groupAdmin === null) {
            return false;
        }

        $column = $candidate->gender === 'male' ? 'groups.male_pics_allowed' : 'groups.female_pics_allowed';

        return DB::table('group_group_admin')
            ->join('groups', 'groups.id', '=', 'group_group_admin.group_id')
            ->join('candidate_group', function ($join) use ($photo): void {
                $join->on('candidate_group.group_id', '=', 'groups.id')
                    ->where('candidate_group.candidate_id', $photo->candidate_id);
            })
            ->where('group_group_admin.user_id', $user->id)
            ->where($column, true)
            ->exists();
    }
}
