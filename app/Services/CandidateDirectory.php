<?php

namespace App\Services;

use App\Models\Candidate;
use App\Support\CandidateProfileData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CandidateDirectory
{
    /**
     * @var array<int, string>
     */
    private const RELATIONS = [
        'religion:id,name',
        'sect:id,name',
        'ethnicityRecord:id,name',
        'country:id,name',
        'state:id,name',
        'city:id,name',
        'grewUpIn:id,name',
        'occupation:id,name',
        'nationalities:id,name',
    ];

    /**
     * @param  array<string, mixed>  $filters
     */
    public function browse(Candidate $viewer, array $filters): LengthAwarePaginator
    {
        $query = Candidate::query()
            ->visibleTo($viewer)
            ->whereNotExists(function ($passed) use ($viewer): void {
                $passed->selectRaw('1')
                    ->from('candidate_candidates_passed')
                    ->whereColumn('candidate_candidates_passed.passed_candidate_id', 'candidates.user_id')
                    ->where('candidate_candidates_passed.candidate_id', $viewer->user_id);
            })
            ->with(['country:id,name', 'city:id,name']);

        $this->applyFilters($query, $filters);

        return $query
            ->orderByDesc('candidates.updated_at')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Candidate $candidate) => CandidateProfileData::card($candidate));
    }

    public function findViewable(Candidate $viewer, int $candidateId): ?Candidate
    {
        return Candidate::query()
            ->visibleTo($viewer)
            ->with(self::RELATIONS)
            ->find($candidateId);
    }

    public function hasActiveGroup(Candidate $viewer): bool
    {
        return DB::table('candidate_group')
            ->join('groups', 'groups.id', '=', 'candidate_group.group_id')
            ->where('candidate_group.candidate_id', $viewer->user_id)
            ->where('candidate_group.status', 'active')
            ->where('groups.status', 'active')
            ->exists();
    }

    public function hasPassed(Candidate $viewer, Candidate $other): bool
    {
        return $this->hasSaved($viewer, $other, 'candidate_candidates_passed', 'passed_candidate_id');
    }

    public function hasLiked(Candidate $viewer, Candidate $other): bool
    {
        return $this->hasSaved($viewer, $other, 'candidate_candidates_liked', 'liked_candidate_id');
    }

    public function hasFavorited(Candidate $viewer, Candidate $other): bool
    {
        return $this->hasSaved($viewer, $other, 'candidate_candidates_favorited', 'favorited_candidate_id');
    }

    public function pass(Candidate $viewer, int $candidateId): void
    {
        $this->save($viewer, $candidateId, 'candidate_candidates_passed', 'passed_candidate_id');
    }

    public function undoPass(Candidate $viewer, int $candidateId): void
    {
        $this->remove($viewer, $candidateId, 'candidate_candidates_passed', 'passed_candidate_id');
    }

    public function like(Candidate $viewer, int $candidateId): void
    {
        $this->save($viewer, $candidateId, 'candidate_candidates_liked', 'liked_candidate_id');
    }

    public function unlike(Candidate $viewer, int $candidateId): void
    {
        $this->remove($viewer, $candidateId, 'candidate_candidates_liked', 'liked_candidate_id');
    }

    public function favorite(Candidate $viewer, int $candidateId): void
    {
        $this->save($viewer, $candidateId, 'candidate_candidates_favorited', 'favorited_candidate_id');
    }

    public function unfavorite(Candidate $viewer, int $candidateId): void
    {
        $this->remove($viewer, $candidateId, 'candidate_candidates_favorited', 'favorited_candidate_id');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function passed(Candidate $viewer): Collection
    {
        return $this->saved($viewer, 'candidate_candidates_passed', 'passed_candidate_id');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function liked(Candidate $viewer): Collection
    {
        return $this->saved($viewer, 'candidate_candidates_liked', 'liked_candidate_id');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function favorited(Candidate $viewer): Collection
    {
        return $this->saved($viewer, 'candidate_candidates_favorited', 'favorited_candidate_id');
    }

    private function hasSaved(Candidate $viewer, Candidate $other, string $table, string $column): bool
    {
        return DB::table($table)
            ->where('candidate_id', $viewer->user_id)
            ->where($column, $other->user_id)
            ->exists();
    }

    private function save(Candidate $viewer, int $candidateId, string $table, string $column): void
    {
        $other = Candidate::query()->visibleTo($viewer)->find($candidateId);
        abort_if($other === null, 404);

        $alreadySaved = DB::table($table)
            ->where('candidate_id', $viewer->user_id)
            ->where($column, $other->user_id)
            ->exists();

        if ($alreadySaved) {
            return;
        }

        DB::table($table)->insert([
            'candidate_id' => $viewer->user_id,
            $column => $other->user_id,
            'created_at' => now(),
        ]);
    }

    private function remove(Candidate $viewer, int $candidateId, string $table, string $column): void
    {
        $deleted = DB::table($table)
            ->where('candidate_id', $viewer->user_id)
            ->where($column, $candidateId)
            ->delete();

        abort_if($deleted === 0, 404);
    }

    /**
     * Saved candidates who still share an active group. Hidden or paused profiles stay on the list without their details.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function saved(Candidate $viewer, string $table, string $column): Collection
    {
        return Candidate::query()
            ->select('candidates.*')
            ->sharingGroupWith($viewer)
            ->join($table.' as saved_rows', function ($join) use ($viewer, $column): void {
                $join->on('saved_rows.'.$column, '=', 'candidates.user_id')
                    ->where('saved_rows.candidate_id', $viewer->user_id);
            })
            ->with('country:id,name')
            ->orderByDesc('saved_rows.created_at')
            ->get()
            ->map(function (Candidate $candidate): array {
                $available = $candidate->profile_visibility === 'visible'
                    && $candidate->profile_status === 'active';

                return [
                    'id' => $candidate->user_id,
                    'display_name' => $candidate->display_name,
                    'available' => $available,
                    'detail' => $available
                        ? collect([
                            CandidateProfileData::age($candidate).' years',
                            Candidate::GENDERS[$candidate->gender] ?? null,
                            $candidate->country?->name,
                        ])->filter()->implode(' · ')
                        : 'This profile is not available.',
                ];
            });
    }

    /**
     * @param  Builder<Candidate>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters($query, array $filters): void
    {
        if (! empty($filters['display_name'])) {
            $name = addcslashes((string) $filters['display_name'], '%_\\');
            $query->where('candidates.display_name', 'like', '%'.$name.'%');
        }

        foreach (['gender', 'marital_status', 'education_level', 'country_id'] as $column) {
            if (! empty($filters[$column])) {
                $query->where('candidates.'.$column, $filters[$column]);
            }
        }

        if (! empty($filters['religion_id'])) {
            $query->where('candidates.religion_id', $filters['religion_id']);
        }

        if (! empty($filters['age_min'])) {
            $query->where('candidates.year_of_birth', '<=', now()->year - (int) $filters['age_min']);
        }

        if (! empty($filters['age_max'])) {
            $query->where('candidates.year_of_birth', '>=', now()->year - (int) $filters['age_max']);
        }

        if (! empty($filters['height_min'])) {
            $query->where('candidates.height', '>=', (int) $filters['height_min']);
        }

        if (! empty($filters['height_max'])) {
            $query->where('candidates.height', '<=', (int) $filters['height_max']);
        }
    }
}
