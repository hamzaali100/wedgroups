<?php

namespace App\Http\Controllers\GroupAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupAdmin\SaveGroupRequest;
use App\Models\Group;
use App\Support\GroupData;
use App\Support\ManagedGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function index(Request $request): Response
    {
        $groups = $request->user()->groupAdmin
            ->groups()
            ->orderBy('name')
            ->get()
            ->map(fn (Group $group) => GroupData::summary($group));

        return Inertia::render('GroupAdmin/Groups/Index', [
            'groups' => $groups,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('GroupAdmin/Groups/Create');
    }

    public function store(SaveGroupRequest $request): RedirectResponse
    {
        $group = Group::query()->create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'status' => 'active',
            'male_pics_allowed' => $request->boolean('male_pics_allowed'),
            'female_pics_allowed' => $request->boolean('female_pics_allowed'),
        ]);

        $group->admins()->attach($request->user()->id, [
            'is_primary' => true,
            'approval_date' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('group_admin.groups.show', $group)
            ->with('success', 'Group created.');
    }

    public function show(Request $request, int $group): Response
    {
        $managed = ManagedGroup::find($request->user(), $group);

        return Inertia::render('GroupAdmin/Groups/Show', [
            'group' => GroupData::detail($managed),
        ]);
    }

    public function edit(Request $request, int $group): Response
    {
        $managed = ManagedGroup::find($request->user(), $group);

        return Inertia::render('GroupAdmin/Groups/Edit', [
            'group' => GroupData::summary($managed),
        ]);
    }

    public function update(SaveGroupRequest $request, int $group): RedirectResponse
    {
        $managed = ManagedGroup::find($request->user(), $group);

        $managed->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'male_pics_allowed' => $request->boolean('male_pics_allowed'),
            'female_pics_allowed' => $request->boolean('female_pics_allowed'),
        ]);

        return redirect()
            ->route('group_admin.groups.show', $managed)
            ->with('success', 'Group details updated.');
    }

    public function activate(Request $request, int $group): RedirectResponse
    {
        $managed = ManagedGroup::find($request->user(), $group);

        if ($managed->status !== 'inactive') {
            throw ValidationException::withMessages([
                'status' => 'Only an inactive group can be activated.',
            ]);
        }

        $managed->update(['status' => 'active']);

        return back()->with('success', 'Group activated.');
    }

    public function deactivate(Request $request, int $group): RedirectResponse
    {
        $managed = ManagedGroup::find($request->user(), $group);

        if ($managed->status !== 'active') {
            throw ValidationException::withMessages([
                'status' => 'Only an active group can be deactivated.',
            ]);
        }

        $managed->update(['status' => 'inactive']);

        return back()->with('success', 'Group deactivated. Its members can no longer see each other.');
    }
}
