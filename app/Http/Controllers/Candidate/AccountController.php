<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\DeactivateAccountRequest;
use App\Http\Requests\Candidate\UpdateAccountRequest;
use App\Http\Requests\Candidate\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Candidate/Account/Edit', [
            'account' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }

    public function update(UpdateAccountRequest $request): RedirectResponse
    {
        $request->user()->update($request->safe()->only(['name', 'email']));

        return back()->with('success', 'Account details updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        $request->session()->regenerate();

        return back()->with('success', 'Password updated.');
    }

    public function deactivate(DeactivateAccountRequest $request): RedirectResponse
    {
        $request->user()->update(['status' => 'inactive']);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Your account has been deactivated.');
    }
}
