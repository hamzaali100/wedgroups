<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = $request->user();

        $canSignIn = $user->status === 'active'
            && in_array($user->role, ['admin', 'candidate', 'group_admin'], true);

        if (! $canSignIn) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'This account cannot sign in.',
            ]);
        }

        $request->session()->regenerate();

        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.users.index'));
        }

        if ($user->role === 'group_admin') {
            return redirect()->intended(route('group_admin.groups.index'));
        }

        return redirect()->intended(
            $user->candidate
                ? route('candidate.profile.show')
                : route('candidate.profile.create')
        );
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
