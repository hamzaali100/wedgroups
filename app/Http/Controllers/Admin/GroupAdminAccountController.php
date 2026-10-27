<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignGroupAdminRequest;
use App\Http\Requests\Admin\StoreGroupAdminRequest;
use App\Http\Requests\Admin\UpdateGroupAdminRequest;
use App\Models\Group;
use App\Models\GroupAdmin;
use App\Models\User;
use App\Support\GroupAdminActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GroupAdminAccountController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $admins = GroupAdmin::query()
            ->whereHas('user', fn ($query) => $query->where('role', 'group_admin'))
            ->with(['user:id,name,email,status'])
            ->withCount('groups')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $like = '%'.addcslashes(trim($search), '%_\\').'%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhereHas('user', fn ($users) => $users->where('email', 'like', $like));
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (GroupAdmin $admin) => [
                'id' => $admin->user_id,
                'name' => $admin->name,
                'email' => $admin->user?->email,
                'status' => $admin->user?->status,
                'status_label' => User::STATUSES[$admin->user?->status] ?? $admin->user?->status,
                'approved' => $admin->approval_date !== null,
                'groups_count' => $admin->groups_count,
            ]);

        return Inertia::render('Admin/GroupAdmins/Index', [
            'admins' => $admins,
            'filters' => [
                'search' => $filters['search'] ?? '',
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/GroupAdmins/Create');
    }

    public function store(StoreGroupAdminRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'group_admin',
            'status' => 'active',
        ]);

        $user->groupAdmin()->create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'description' => $data['description'] ?? null,
            'approval_date' => null,
        ]);

        return redirect()
            ->route('admin.group-admins.show', $user->groupAdmin)
            ->with('success', 'Group admin account created. Approve it before assigning a group.');
    }

    public function show(GroupAdmin $groupAdmin): Response
    {
        $groupAdmin->load([
            'user:id,name,email,status,role',
            'groups' => fn ($query) => $query->orderBy('name'),
        ]);

        $assigned = $groupAdmin->groups->pluck('id');

        return Inertia::render('Admin/GroupAdmins/Show', [
            'admin' => $this->payload($groupAdmin),
            'groups' => $groupAdmin->groups->map(fn (Group $group) => [
                'id' => $group->id,
                'name' => $group->name,
                'status_label' => Group::STATUSES[$group->status] ?? $group->status,
                'is_primary' => (bool) (int) $group->pivot->is_primary,
            ]),
            'available_groups' => Group::query()
                ->whereNotIn('id', $assigned)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Group $group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                ]),
            'activity' => GroupAdminActivity::for($groupAdmin),
        ]);
    }

    public function edit(GroupAdmin $groupAdmin): Response
    {
        $groupAdmin->load('user:id,email');

        return Inertia::render('Admin/GroupAdmins/Edit', [
            'admin' => [
                'id' => $groupAdmin->user_id,
                'name' => $groupAdmin->name,
                'email' => $groupAdmin->user?->email,
                'phone' => $groupAdmin->phone ?? '',
                'whatsapp' => $groupAdmin->whatsapp ?? '',
                'description' => $groupAdmin->description ?? '',
            ],
        ]);
    }

    public function update(UpdateGroupAdminRequest $request, GroupAdmin $groupAdmin): RedirectResponse
    {
        $data = $request->validated();

        $groupAdmin->user?->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $groupAdmin->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.group-admins.show', $groupAdmin)
            ->with('success', 'Group admin details updated.');
    }

    public function approve(GroupAdmin $groupAdmin): RedirectResponse
    {
        abort_unless($groupAdmin->user?->role === 'group_admin', 404);

        if ($groupAdmin->approval_date === null) {
            $groupAdmin->update(['approval_date' => now()]);
        }

        return back()->with('success', 'Group admin approved.');
    }

    public function addGroup(AssignGroupAdminRequest $request, GroupAdmin $groupAdmin): RedirectResponse
    {
        abort_unless($groupAdmin->user?->role === 'group_admin', 404);

        if ($groupAdmin->approval_date === null) {
            throw ValidationException::withMessages([
                'group_id' => 'Approve this group admin before adding them to a group.',
            ]);
        }

        $groupId = (int) $request->validated('group_id');

        if ($groupAdmin->groups()->whereKey($groupId)->exists()) {
            throw ValidationException::withMessages([
                'group_id' => 'This group admin already belongs to that group.',
            ]);
        }

        $primary = $request->boolean('is_primary');

        if ($primary) {
            DB::table('group_group_admin')
                ->where('group_id', $groupId)
                ->update(['is_primary' => false]);
        }

        $groupAdmin->groups()->attach($groupId, [
            'is_primary' => $primary,
            'approval_date' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Group admin added to the group.');
    }

    public function removeGroup(GroupAdmin $groupAdmin, Group $group): RedirectResponse
    {
        $assigned = $groupAdmin->groups()->whereKey($group->id)->exists();

        abort_unless($assigned, 404);

        $groupAdmin->groups()->detach($group->id);

        return back()->with('success', 'Group admin removed from the group.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(GroupAdmin $groupAdmin): array
    {
        $user = $groupAdmin->user;

        return [
            'id' => $groupAdmin->user_id,
            'name' => $groupAdmin->name,
            'email' => $user?->email,
            'phone' => $groupAdmin->phone,
            'whatsapp' => $groupAdmin->whatsapp,
            'description' => $groupAdmin->description,
            'approved' => $groupAdmin->approval_date !== null,
            'approved_at' => $groupAdmin->approval_date?->format('Y-m-d H:i'),
            'status' => $user?->status,
            'status_label' => User::STATUSES[$user?->status] ?? $user?->status,
            'user_id' => $user?->id,
        ];
    }
}
