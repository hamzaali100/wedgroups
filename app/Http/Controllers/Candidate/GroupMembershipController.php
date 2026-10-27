<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupMembershipController extends Controller
{
    public function index(Request $request): Response
    {
        $groups = $request->user()->candidate
            ->groups()
            ->wherePivot('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (Group $group) => [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'status' => Group::STATUSES[$group->status] ?? $group->status,
            ]);

        return Inertia::render('Candidate/Groups/Index', [
            'groups' => $groups,
        ]);
    }

    public function destroy(Request $request, int $group): RedirectResponse
    {
        $candidate = $request->user()->candidate;

        $belongs = $candidate->groups()
            ->where('groups.id', $group)
            ->wherePivot('status', 'active')
            ->exists();

        abort_unless($belongs, 404);

        $candidate->groups()->updateExistingPivot($group, [
            'status' => 'inactive',
        ]);

        return redirect()
            ->route('candidate.groups.index')
            ->with('success', 'You have left the group. Its members are no longer visible to you.');
    }
}
