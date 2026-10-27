<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Group;
use App\Support\CandidateProfileData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GroupRoster
{
    /**
     * Candidates already attached to this group, searched and filtered for its admins.
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(Group $group, array $filters): LengthAwarePaginator
    {
        $query = $group->candidates()->with(['country:id,name', 'city:id,name']);

        if (! empty($filters['display_name'])) {
            $name = addcslashes((string) $filters['display_name'], '%_\\');
            $query->where('candidates.display_name', 'like', '%'.$name.'%');
        }

        foreach (['gender', 'marital_status', 'education_level'] as $column) {
            if (! empty($filters[$column])) {
                $query->where('candidates.'.$column, $filters[$column]);
            }
        }

        if (! empty($filters['membership_status'])) {
            $query->wherePivot('status', $filters['membership_status']);
        }

        if (! empty($filters['age_min'])) {
            $query->where('candidates.year_of_birth', '<=', now()->year - (int) $filters['age_min']);
        }

        if (! empty($filters['age_max'])) {
            $query->where('candidates.year_of_birth', '>=', now()->year - (int) $filters['age_max']);
        }

        return $query
            ->orderBy('candidates.display_name')
            ->paginate(12)
            ->withQueryString()
            ->through(function (Candidate $candidate) {
                return [
                    ...CandidateProfileData::card($candidate),
                    'membership_status' => $candidate->pivot->status,
                    'membership_label' => Group::MEMBERSHIP_STATUSES[$candidate->pivot->status] ?? $candidate->pivot->status,
                ];
            });
    }
}
