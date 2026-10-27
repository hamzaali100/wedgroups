<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'status', 'google_id'])]
#[Hidden(['password', 'remember_token', 'google_id'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = [
        'admin' => 'Admin',
        'group_admin' => 'Group admin',
        'candidate' => 'Candidate',
        'relative' => 'Relative',
    ];

    /**
     * Roles a person may choose while creating their own account.
     * Admin and relative are assigned from inside the app, not at sign-up.
     *
     * @var array<string, string>
     */
    public const SIGNUP_ROLES = [
        'candidate' => 'Candidate',
        'group_admin' => 'Group admin',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canSignIn(): bool
    {
        return $this->status === 'active'
            && in_array($this->role, ['admin', 'candidate', 'group_admin'], true);
    }

    public function homeRoute(): string
    {
        if ($this->role === 'admin') {
            return route('admin.users.index');
        }

        if ($this->role === 'group_admin') {
            return route('group_admin.groups.index');
        }

        if ($this->role === 'candidate') {
            return $this->candidate
                ? route('candidate.profile.show')
                : route('candidate.profile.create');
        }

        return route('home');
    }

    public function candidate(): HasOne
    {
        return $this->hasOne(Candidate::class, 'user_id');
    }

    public function groupAdmin(): HasOne
    {
        return $this->hasOne(GroupAdmin::class, 'user_id');
    }
}
