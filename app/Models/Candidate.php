<?php

namespace App\Models;

use Database\Factories\CandidateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'real_name',
    'display_name',
    'year_of_birth',
    'month_of_birth',
    'gender',
    'height',
    'marital_status',
    'religion_id',
    'sect_id',
    'ethnicity_id',
    'other_details',
    'disabilities',
    'country_id',
    'state_id',
    'city_id',
    'residence_details',
    'grew_up_in',
    'total_siblings',
    'siblings_description',
    'father_occupation',
    'mother_occupation',
    'family_details',
    'parent_contact_number',
    'ethnicity',
    'caste',
    'profile_visibility',
    'profile_status',
    'review_status',
    'rejection_reason',
    'photos_blurred',
    'education_level',
    'universities_names',
    'schools_names',
    'monthly_income',
    'occupation_id',
    'career_details',
    'linkedin',
    'willing_to_move_abroad',
    'has_kids',
    'wants_children',
    'phone_number',
    'contact_person',
    'contact_relationship',
    'contact_phone_number',
    'contact_email',
    'partner_age_min',
    'partner_age_max',
    'partner_height_min',
    'partner_height_max',
    'partner_requirement_details',
])]
#[Hidden([
    'real_name',
    'disabilities',
    'residence_details',
    'phone_number',
    'parent_contact_number',
    'contact_person',
    'contact_relationship',
    'contact_phone_number',
    'contact_email',
    'linkedin',
])]
class Candidate extends Model
{
    /** @use HasFactory<CandidateFactory> */
    use HasFactory;

    public const GENDERS = [
        'male' => 'Male',
        'female' => 'Female',
    ];

    public const MARITAL_STATUSES = [
        'never_married' => 'Never married',
        'married' => 'Married',
        'divorced' => 'Divorced',
        'widowed' => 'Widowed',
        'nikah_break' => 'Nikah break',
        'separated' => 'Separated',
    ];

    public const EDUCATION_LEVELS = [
        'secondary_school' => 'Secondary school',
        'high_school' => 'High school',
        'bachelors_degree' => "Bachelor's degree",
        'masters' => "Master's",
        'phd' => 'PhD',
    ];

    public const PROFILE_STATUSES = [
        'active' => 'Active',
        'paused' => 'Paused',
    ];

    public const PROFILE_VISIBILITIES = [
        'visible' => 'Visible to my groups',
        'hidden' => 'Hidden',
    ];

    public const REVIEW_STATUSES = [
        'pending' => 'Waiting for approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'suspended' => 'Suspended',
    ];

    public const WANTS_CHILDREN = [
        'yes' => 'Yes',
        'no' => 'No',
        'undecided' => 'Undecided',
    ];

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'photos_blurred' => 'boolean',
            'willing_to_move_abroad' => 'boolean',
            'has_kids' => 'boolean',
            'year_of_birth' => 'integer',
            'month_of_birth' => 'integer',
            'height' => 'integer',
        ];
    }

    /**
     * Another candidate is visible only when both people have an active
     * membership in the same active group, and the profile is open.
     *
     * @param  Builder<Candidate>  $query
     * @return Builder<Candidate>
     */
    public function scopeSharingGroupWith(Builder $query, Candidate $viewer): Builder
    {
        return $query
            ->where('candidates.user_id', '!=', $viewer->user_id)
            ->whereHas('user', function (Builder $user): void {
                $user->where('role', 'candidate')->where('status', 'active');
            })
            ->whereExists(function ($shared) use ($viewer): void {
                $shared->selectRaw('1')
                    ->from('candidate_group as mine')
                    ->join('candidate_group as theirs', 'theirs.group_id', '=', 'mine.group_id')
                    ->join('groups', 'groups.id', '=', 'mine.group_id')
                    ->whereColumn('theirs.candidate_id', 'candidates.user_id')
                    ->where('mine.candidate_id', $viewer->user_id)
                    ->where('mine.status', 'active')
                    ->where('theirs.status', 'active')
                    ->where('groups.status', 'active');
            });
    }

    /**
     * @param  Builder<Candidate>  $query
     * @return Builder<Candidate>
     */
    public function scopeVisibleTo(Builder $query, Candidate $viewer): Builder
    {
        return $query
            ->sharingGroupWith($viewer)
            ->where('candidates.profile_visibility', 'visible')
            ->where('candidates.profile_status', 'active')
            ->where('candidates.review_status', 'approved');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class);
    }

    public function sect(): BelongsTo
    {
        return $this->belongsTo(Sect::class);
    }

    public function ethnicityRecord(): BelongsTo
    {
        return $this->belongsTo(Ethnicity::class, 'ethnicity_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function grewUpIn(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'grew_up_in');
    }

    public function occupation(): BelongsTo
    {
        return $this->belongsTo(Occupation::class);
    }

    public function nationalities(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'candidate_nationality', 'candidate_id', 'nationality_id')
            ->withPivot('created_at');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'candidate_group', 'candidate_id', 'group_id')
            ->withPivot([
                'status',
                'approved_at',
                'approved_by',
                'group_terms_accepted_at',
                'group_terms_version',
                'notes',
            ])
            ->withTimestamps();
    }

    public function photos(): HasMany
    {
        return $this->hasMany(CandidatePhoto::class, 'candidate_id', 'user_id')
            ->orderBy('sort_order');
    }
}
