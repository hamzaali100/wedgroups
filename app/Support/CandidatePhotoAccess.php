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

        if ($user->role === 'group_admin' && self::managesCandidateGroup($user, $candidate)) {
            return 'clear';
        }

        $allowed = $user->role === 'candidate' && self::candidateCanView($user, $photo, $candidate);

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

    /**
     * A group admin may review photos of a candidate who belongs to one of their groups.
     */
    private static function managesCandidateGroup(User $user, Candidate $candidate): bool
    {
        if ($user->groupAdmin === null) {
            return false;
        }

        return DB::table('group_group_admin')
            ->join('candidate_group', 'candidate_group.group_id', '=', 'group_group_admin.group_id')
            ->where('group_group_admin.user_id', $user->id)
            ->where('candidate_group.candidate_id', $candidate->user_id)
            ->exists();
    }
}
