<?php

namespace App\Support;

use App\Models\Candidate;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdminReports
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return [
            'users' => self::users(),
            'candidates' => self::candidates(),
            'groups' => self::groups(),
            'matching' => self::matching(),
            'activity' => self::activity(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function users(): array
    {
        return [
            'total' => User::query()->count(),
            'by_role' => self::labeled(User::ROLES, self::counts(User::query(), 'role')),
            'by_status' => self::labeled(User::STATUSES, self::counts(User::query(), 'status')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function candidates(): array
    {
        return [
            'total' => Candidate::query()->count(),
            'by_review' => self::labeled(Candidate::REVIEW_STATUSES, self::counts(Candidate::query(), 'review_status')),
            'by_gender' => self::labeled(Candidate::GENDERS, self::counts(Candidate::query(), 'gender')),
            'by_visibility' => self::labeled(Candidate::PROFILE_VISIBILITIES, self::counts(Candidate::query(), 'profile_visibility')),
            'by_profile_status' => self::labeled(Candidate::PROFILE_STATUSES, self::counts(Candidate::query(), 'profile_status')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function groups(): array
    {
        $memberships = self::counts(DB::table('candidate_group'), 'status');

        return [
            'total' => Group::query()->count(),
            'by_status' => self::labeled(Group::STATUSES, self::counts(Group::query(), 'status')),
            'memberships' => self::labeled(Group::MEMBERSHIP_STATUSES, $memberships),
            'active_memberships' => (int) ($memberships['active'] ?? 0),
        ];
    }

    /**
     * @return array<string, int>
     */
    private static function matching(): array
    {
        $mutual = (int) DB::table('candidate_candidates_liked as left_like')
            ->join('candidate_candidates_liked as right_like', function ($join): void {
                $join->on('left_like.candidate_id', '=', 'right_like.liked_candidate_id')
                    ->on('left_like.liked_candidate_id', '=', 'right_like.candidate_id');
            })
            ->count();

        return [
            'likes' => DB::table('candidate_candidates_liked')->count(),
            'mutual_likes' => intdiv($mutual, 2),
            'favorites' => DB::table('candidate_candidates_favorited')->count(),
            'passes' => DB::table('candidate_candidates_passed')->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private static function activity(): array
    {
        $since = now()->subDays(30);

        return [
            'users_30_days' => User::query()->where('created_at', '>=', $since)->count(),
            'profiles_30_days' => Candidate::query()->where('created_at', '>=', $since)->count(),
            'likes_30_days' => DB::table('candidate_candidates_liked')->where('created_at', '>=', $since)->count(),
            'favorites_30_days' => DB::table('candidate_candidates_favorited')->where('created_at', '>=', $since)->count(),
            'passes_30_days' => DB::table('candidate_candidates_passed')->where('created_at', '>=', $since)->count(),
            'group_assignments_30_days' => DB::table('group_group_admin')->where('created_at', '>=', $since)->count(),
            'profile_approvals' => Candidate::query()->where('review_status', 'approved')->count(),
        ];
    }

    /**
     * @param  Builder<Model>|\Illuminate\Database\Query\Builder  $query
     * @return array<string, int>
     */
    private static function counts($query, string $column): array
    {
        return $query
            ->select($column)
            ->selectRaw('count(*) as total')
            ->groupBy($column)
            ->pluck('total', $column)
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    /**
     * @param  array<string, string>  $labels
     * @param  array<string, int>  $counts
     * @return array<int, array{label: string, total: int}>
     */
    private static function labeled(array $labels, array $counts): array
    {
        $rows = [];

        foreach ($labels as $key => $label) {
            $rows[] = [
                'label' => $label,
                'total' => (int) ($counts[$key] ?? 0),
            ];
        }

        return $rows;
    }
}
