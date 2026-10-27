<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'description', 'status', 'male_pics_allowed', 'female_pics_allowed'])]
class Group extends Model
{
    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ];

    public const MEMBERSHIP_STATUSES = [
        'pending' => 'Pending',
        'active' => 'Active',
        'inactive' => 'Suspended',
        'rejected' => 'Rejected',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'male_pics_allowed' => 'boolean',
            'female_pics_allowed' => 'boolean',
        ];
    }

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'candidate_group', 'group_id', 'candidate_id')
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

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(GroupAdmin::class, 'group_group_admin', 'group_id', 'user_id')
            ->withPivot(['is_primary', 'approval_date', 'approved_by'])
            ->withTimestamps();
    }
}
