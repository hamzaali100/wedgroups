<?php

namespace App\Http\Controllers\GroupAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupAdmin\AddAdministratorRequest;
use App\Models\GroupAdmin;
use App\Models\User;
use App\Support\ManagedGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GroupAdministratorController extends Controller
{
    public function index(Request $request, int $group): Response
    {
        $managed = ManagedGroup::find($request->user(), $group);

        $admins = $managed->admins()
            ->with('user:id,name,email')
            ->orderBy('group_admins.name')
            ->get()
            ->map(fn (GroupAdmin $admin) => [
                'id' => $admin->user_id,
                'name' => $admin->name,
                'email' => $admin->user?->email,
                'phone' => $admin->phone,
                'whatsapp' => $admin->whatsapp,
                'is_primary' => (bool) (int) $admin->pivot->is_primary,
                'is_you' => $admin->user_id === $request->user()->id,
            ]);

        return Inertia::render('GroupAdmin/Groups/Admins', [
            'group' => [
                'id' => $managed->id,
                'name' => $managed->name,
            ],
            'admins' => $admins,
        ]);
    }

    public function store(AddAdministratorRequest $request, int $group): RedirectResponse
    {
        $managed = ManagedGroup::find($request->user(), $group);
        $email = strtolower($request->validated('email'));

        $user = User::query()->whereRaw('lower(email) = ?', [$email])->first();
        $profile = $user?->role === 'group_admin' && $user->status === 'active'
            ? $user->groupAdmin
            : null;

        if ($profile === null) {
            throw ValidationException::withMessages([
                'email' => 'No active group admin account uses that email.',
            ]);
        }

        $already = $managed->admins()->whereKey($profile->user_id)->exists();

        if ($already) {
            throw ValidationException::withMessages([
                'email' => 'This admin already belongs to the group.',
            ]);
        }

        $managed->admins()->attach($profile->user_id, [
            'is_primary' => false,
            'approval_date' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Admin added to the group.');
    }

    public function destroy(Request $request, int $group, int $admin): RedirectResponse
    {
        $managed = ManagedGroup::find($request->user(), $group);
        $other = $managed->admins()->whereKey($admin)->first();

        abort_if($other === null, 404);

        if ($other->user_id === $request->user()->id) {
            throw ValidationException::withMessages([
                'admin' => 'You cannot remove yourself from the group.',
            ]);
        }

        if ($managed->admins()->count() <= 1) {
            throw ValidationException::withMessages([
                'admin' => 'A group must keep at least one admin.',
            ]);
        }

        $wasPrimary = (bool) (int) $other->pivot->is_primary;

        $managed->admins()->detach($other->user_id);

        if ($wasPrimary) {
            $managed->admins()->updateExistingPivot($request->user()->id, [
                'is_primary' => true,
            ]);
        }

        return back()->with('success', 'Admin removed from the group.');
    }
}
