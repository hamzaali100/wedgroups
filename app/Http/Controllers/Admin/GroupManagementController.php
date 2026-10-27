<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignAdminToGroupRequest;
use App\Http\Requests\Admin\SaveAdminGroupRequest;
use App\Models\Group;
use App\Models\GroupAdmin;
use App\Support\GroupData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GroupManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $groups = Group::query()
            ->withCount(['admins', 'candidates'])
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $like = '%'.addcslashes(trim($search), '%_\\').'%';
                $query->where('name', 'like', $like);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Group $group) => [
                ...GroupData::summary($group),
                'admin_count' => $group->admins_count,
                'candidate_count' => $group->candidates_count,
            ]);

        return Inertia::render('Admin/Groups/Index', [
            'groups' => $groups,
            'filters' => [
                'search' => $filters['search'] ?? '',
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Groups/Create');
    }

    public function store(SaveAdminGroupRequest $request): RedirectResponse
    {
        $group = Group::query()->create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'status' => 'active',
            'male_pics_allowed' => $request->boolean('male_pics_allowed'),
            'female_pics_allowed' => $request->boolean('female_pics_allowed'),
        ]);

        return redirect()
            ->route('admin.groups.show', $group)
            ->with('success', 'Group created.');
    }

    public function show(Group $group): Response
    {
        $group->load(['admins.user:id,email,status']);

        $assigned = $group->admins->pluck('user_id');

        return Inertia::render('Admin/Groups/Show', [
            'group' => GroupData::detail($group),
            'admins' => $group->admins->map(fn (GroupAdmin $admin) => [
                'id' => $admin->user_id,
                'name' => $admin->name,
                'email' => $admin->user?->email,
                'status_label' => $admin->user?->status,
                'is_primary' => (bool) (int) $admin->pivot->is_primary,
            ]),
            'available_admins' => GroupAdmin::query()
                ->whereNotNull('approval_date')
                ->whereHas('user', fn ($query) => $query->where('role', 'group_admin')->where('status', 'active'))
                ->whereNotIn('user_id', $assigned)
                ->orderBy('name')
                ->get(['user_id', 'name'])
                ->map(fn (GroupAdmin $admin) => [
                    'id' => $admin->user_id,
                    'name' => $admin->name,
                ]),
        ]);
    }

    public function edit(Group $group): Response
    {
        return Inertia::render('Admin/Groups/Edit', [
            'group' => GroupData::summary($group),
        ]);
    }

    public function update(SaveAdminGroupRequest $request, Group $group): RedirectResponse
    {
        $group->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'male_pics_allowed' => $request->boolean('male_pics_allowed'),
            'female_pics_allowed' => $request->boolean('female_pics_allowed'),
        ]);

        return redirect()
            ->route('admin.groups.show', $group)
            ->with('success', 'Group details updated.');
    }

    public function activate(Group $group): RedirectResponse
    {
        if ($group->status === 'active') {
            throw ValidationException::withMessages([
                'status' => 'This group is already active.',
            ]);
        }

        $group->update(['status' => 'active']);

        return back()->with('success', 'Group activated.');
    }

    public function deactivate(Group $group): RedirectResponse
    {
        if ($group->status !== 'active') {
            throw ValidationException::withMessages([
                'status' => 'Only an active group can be deactivated.',
            ]);
        }

        $group->update(['status' => 'inactive']);

        return back()->with('success', 'Group deactivated. Its members can no longer see each other.');
    }

    public function destroy(Group $group): RedirectResponse
    {
        DB::transaction(function () use ($group): void {
            DB::table('candidate_group')->where('group_id', $group->id)->delete();
            DB::table('group_group_admin')->where('group_id', $group->id)->delete();
            $group->delete();
        });

        return redirect()
            ->route('admin.groups.index')
            ->with('success', 'Group deleted.');
    }

    public function addAdmin(AssignAdminToGroupRequest $request, Group $group): RedirectResponse
    {
        $admin = GroupAdmin::query()->with('user')->find($request->validated('group_admin_id'));

        if ($admin === null || $admin->approval_date === null || $admin->user?->role !== 'group_admin' || $admin->user->status !== 'active') {
            throw ValidationException::withMessages([
                'group_admin_id' => 'Choose an approved, active group admin.',
            ]);
        }

        if ($group->admins()->whereKey($admin->user_id)->exists()) {
            throw ValidationException::withMessages([
                'group_admin_id' => 'This admin already belongs to the group.',
            ]);
        }

        $primary = $request->boolean('is_primary');

        if ($primary) {
            DB::table('group_group_admin')->where('group_id', $group->id)->update(['is_primary' => false]);
        }

        $group->admins()->attach($admin->user_id, [
            'is_primary' => $primary,
            'approval_date' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Admin added to the group.');
    }

    public function removeAdmin(Group $group, GroupAdmin $groupAdmin): RedirectResponse
    {
        abort_unless($group->admins()->whereKey($groupAdmin->user_id)->exists(), 404);

        $group->admins()->detach($groupAdmin->user_id);

        return back()->with('success', 'Admin removed from the group.');
    }
}
