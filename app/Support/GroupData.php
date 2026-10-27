<?php

namespace App\Support;

use App\Models\Group;

class GroupData
{
    /**
     * @return array<string, mixed>
     */
    public static function summary(Group $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description,
            'status' => $group->status,
            'status_label' => Group::STATUSES[$group->status] ?? $group->status,
            'male_pics_allowed' => $group->male_pics_allowed,
            'female_pics_allowed' => $group->female_pics_allowed,
            'is_primary' => (bool) (int) ($group->pivot?->is_primary ?? false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function detail(Group $group): array
    {
        return [
            ...self::summary($group),
            'admin_count' => $group->admins()->count(),
            'candidate_count' => $group->candidates()->count(),
            'pending_count' => $group->candidates()->wherePivot('status', 'pending')->count(),
            'active_count' => $group->candidates()->wherePivot('status', 'active')->count(),
            'suspended_count' => $group->candidates()->wherePivot('status', 'inactive')->count(),
        ];
    }
}
