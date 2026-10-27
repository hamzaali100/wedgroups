<?php

namespace App\Support;

use App\Models\GroupAdmin;
use Illuminate\Support\Facades\DB;

class GroupAdminActivity
{
    /**
     * @return array<int, array{at: string, text: string}>
     */
    public static function for(GroupAdmin $admin): array
    {
        $events = [[
            'at' => self::stamp($admin->created_at),
            'text' => 'Account created',
        ]];

        if ($admin->approval_date !== null) {
            $events[] = [
                'at' => self::stamp($admin->approval_date),
                'text' => 'Account approved',
            ];
        }

        foreach ($admin->groups as $group) {
            $primary = (int) $group->pivot->is_primary === 1 ? ' as primary admin' : '';
            $events[] = [
                'at' => self::stamp($group->pivot->created_at),
                'text' => 'Added to '.$group->name.$primary,
            ];
        }

        $approvals = DB::table('candidate_group')
            ->join('groups', 'groups.id', '=', 'candidate_group.group_id')
            ->join('candidates', 'candidates.user_id', '=', 'candidate_group.candidate_id')
            ->where('candidate_group.approved_by', $admin->user_id)
            ->whereNotNull('candidate_group.approved_at')
            ->orderByDesc('candidate_group.approved_at')
            ->limit(20)
            ->get(['candidates.display_name', 'groups.name as group_name', 'candidate_group.approved_at']);

        foreach ($approvals as $approval) {
            $events[] = [
                'at' => self::stamp($approval->approved_at),
                'text' => 'Approved '.$approval->display_name.' for '.$approval->group_name,
            ];
        }

        $notes = DB::table('group_admin_candidate')
            ->join('candidates', 'candidates.user_id', '=', 'group_admin_candidate.candidate_id')
            ->where('group_admin_candidate.group_admin_id', $admin->user_id)
            ->orderByDesc('group_admin_candidate.updated_at')
            ->limit(20)
            ->get(['candidates.display_name', 'group_admin_candidate.notes', 'group_admin_candidate.updated_at']);

        foreach ($notes as $note) {
            $detail = $note->notes ? ': '.$note->notes : '';
            $events[] = [
                'at' => self::stamp($note->updated_at),
                'text' => 'Note on '.$note->display_name.$detail,
            ];
        }

        usort($events, fn (array $left, array $right) => strcmp($right['at'], $left['at']));

        return $events;
    }

    private static function stamp(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i');
        }

        return substr((string) $value, 0, 16);
    }
}
