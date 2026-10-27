<?php

namespace App\Support;

use App\Models\Candidate;
use App\Models\Group;
use App\Models\User;

class ManagedGroup
{
    /**
     * A group this admin belongs to. Missing and unowned groups both look the same.
     */
    public static function find(User $user, int $groupId): Group
    {
        $group = $user->groupAdmin?->groups()->whereKey($groupId)->first();

        abort_if($group === null, 404);

        return $group;
    }

    public static function candidate(Group $group, int $candidateId): Candidate
    {
        $candidate = $group->candidates()->whereKey($candidateId)->first();

        abort_if($candidate === null, 404);

        return $candidate;
    }
}
