<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetUserPasswordRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserAccountController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(array_keys(User::ROLES))],
        ]);

        $users = User::query()
            ->select(['id', 'name', 'email', 'role', 'status', 'created_at'])
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->where('role', $role))
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $like = '%'.addcslashes(trim($search), '%_\\').'%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => User::ROLES[$user->role] ?? $user->role,
                'status' => $user->status,
                'status_label' => User::STATUSES[$user->status] ?? $user->status,
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'role' => $filters['role'] ?? '',
            ],
            'roles' => User::ROLES,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => User::ROLES,
            'statuses' => [
                'active' => 'Active',
                'suspended' => 'Suspended',
            ],
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'status' => $data['status'],
        ]);

        if ($user->role === 'group_admin') {
            $user->groupAdmin()->create([
                'name' => $user->name,
                'phone' => $data['phone'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
                'description' => $data['description'] ?? null,
                'approval_date' => null,
            ]);
        }

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User account created.');
    }

    public function show(Request $request, User $user): Response
    {
        $user->load([
            'candidate:user_id,display_name,real_name,profile_status,profile_visibility',
            'groupAdmin:user_id',
        ]);

        return Inertia::render('Admin/Users/Show', [
            'account' => $this->account($request, $user),
            'roles' => User::ROLES,
        ]);
    }

    public function edit(Request $request, User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'account' => $this->account($request, $user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $user->groupAdmin?->update(['name' => $data['name']]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User details updated.');
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $this->guardNotSelf($request, $user);

        $user->update(['status' => 'suspended']);
        $this->endSessions($user);

        return back()->with('success', 'User account suspended.');
    }

    public function reactivate(Request $request, User $user): RedirectResponse
    {
        $this->guardNotSelf($request, $user);

        $user->update(['status' => 'active']);

        return back()->with('success', 'User account reactivated.');
    }

    public function resetPassword(ResetUserPasswordRequest $request, User $user): RedirectResponse
    {
        $this->guardNotSelf($request, $user);

        $user->update([
            'password' => $request->validated('password'),
        ]);
        $this->endSessions($user);

        return back()->with('success', 'Password reset. Existing sign-ins for this account were ended.');
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $this->guardNotSelf($request, $user);

        $role = $request->validated('role');

        if ($user->role === 'group_admin' && $role !== 'group_admin' && $user->groupAdmin?->groups()->exists()) {
            throw ValidationException::withMessages([
                'role' => 'Remove this person from their groups before changing the role.',
            ]);
        }

        $user->update(['role' => $role]);

        if ($role === 'group_admin' && $user->groupAdmin === null) {
            $user->groupAdmin()->create([
                'name' => $user->name,
                'approval_date' => null,
            ]);
        }

        return back()->with('success', 'User role updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function account(Request $request, User $user): array
    {
        $candidate = $user->relationLoaded('candidate') ? $user->candidate : $user->candidate()->first();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'role_label' => User::ROLES[$user->role] ?? $user->role,
            'status' => $user->status,
            'status_label' => User::STATUSES[$user->status] ?? $user->status,
            'created_at' => $user->created_at?->format('Y-m-d H:i'),
            'is_you' => $user->id === $request->user()->id,
            'candidate' => $candidate ? [
                'display_name' => $candidate->display_name,
                'real_name' => $candidate->real_name,
                'profile_status' => Candidate::PROFILE_STATUSES[$candidate->profile_status] ?? $candidate->profile_status,
                'profile_visibility' => Candidate::PROFILE_VISIBILITIES[$candidate->profile_visibility] ?? $candidate->profile_visibility,
            ] : null,
            'group_admin_id' => $user->groupAdmin?->user_id,
        ];
    }

    private function guardNotSelf(Request $request, User $user): void
    {
        abort_if($request->user()->id === $user->id, 403);
    }

    private function endSessions(User $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->delete();
    }
}
