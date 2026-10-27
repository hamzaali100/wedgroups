<?php

namespace App\Support;

use App\Models\Candidate;
use Illuminate\Support\Facades\DB;

class CandidateProfileDeletion
{
    public static function delete(Candidate $candidate): void
    {
        DB::transaction(function () use ($candidate): void {
            $id = $candidate->user_id;

            DB::table('candidate_photos')->where('candidate_id', $id)->delete();
            DB::table('candidate_nationality')->where('candidate_id', $id)->delete();
            DB::table('candidate_group')->where('candidate_id', $id)->delete();
            DB::table('group_admin_candidate')->where('candidate_id', $id)->delete();

            foreach ([
                'candidate_candidates_liked' => 'liked_candidate_id',
                'candidate_candidates_favorited' => 'favorited_candidate_id',
                'candidate_candidates_passed' => 'passed_candidate_id',
                'candidate_candidates_hidden' => 'hidden_candidate_id',
                'candidate_candidates_unmatched' => 'unmatched_candidate_id',
            ] as $table => $other) {
                DB::table($table)->where('candidate_id', $id)->orWhere($other, $id)->delete();
            }

            $candidate->delete();
        });
    }
}
