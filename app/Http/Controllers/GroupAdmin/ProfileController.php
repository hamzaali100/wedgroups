<?php

namespace App\Http\Controllers\GroupAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupAdmin\UpdatePasswordRequest;
use App\Http\Requests\GroupAdmin\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        $admin = $request->user()->groupAdmin;

        if ($admin === null) {
            return redirect()->route('group_admin.profile.edit');
        }

        return Inertia::render('GroupAdmin/Profile/Show', [
            'profile' => $this->payload($request, $admin->name, $admin->phone, $admin->whatsapp, $admin->description),
        ]);
    }

    public function edit(Request $request): Response
    {
        $admin = $request->user()->groupAdmin;

        return Inertia::render('GroupAdmin/Profile/Edit', [
            'profile' => $this->payload(
                $request,
                $admin->name ?? $request->user()->name,
                $admin->phone ?? '',
                $admin->whatsapp ?? '',
                $admin->description ?? '',
            ),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $request->user()->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $profile = [
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'description' => $data['description'] ?? null,
        ];

        $admin = $request->user()->groupAdmin;

        if ($admin === null) {
            $request->user()->groupAdmin()->create($profile);
        } else {
            $admin->update($profile);
        }

        return redirect()
            ->route('group_admin.profile.show')
            ->with('success', 'Profile updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        $request->session()->regenerate();

        return back()->with('success', 'Password updated.');
    }

    /**
     * @return array<string, ?string>
     */
    private function payload(Request $request, ?string $name, ?string $phone, ?string $whatsapp, ?string $description): array
    {
        return [
            'name' => $name,
            'email' => $request->user()->email,
            'phone' => $phone,
            'whatsapp' => $whatsapp,
            'description' => $description,
        ];
    }
}
