<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GoogleAuthController extends Controller
{
    public const SIGNUP_ROLE = 'google_signup_role';

    public function login(Request $request): Response
    {
        $request->session()->forget(self::SIGNUP_ROLE);

        return Inertia::location(Socialite::driver('google')->redirect());
    }

    public function signup(Request $request): Response
    {
        $data = $request->validate([
            'role' => ['required', 'string', Rule::in(array_keys(User::SIGNUP_ROLES))],
        ]);

        $request->session()->put(self::SIGNUP_ROLE, $data['role']);

        return Inertia::location(Socialite::driver('google')->redirect());
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')->withErrors([
                'google' => 'Google sign-in expired. Try again.',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'google' => 'Google sign-in could not be completed.',
            ]);
        }

        $googleId = trim((string) $googleUser->getId());

        if ($googleId === '') {
            return redirect()->route('login')->withErrors([
                'google' => 'Google sign-in could not be completed.',
            ]);
        }

        $email = trim((string) $googleUser->getEmail());
        $emailIsUsable = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $emailIsVerified = $this->googleEmailIsVerified($googleUser);

        $user = User::query()->where('google_id', $googleId)->first();

        if ($user === null && $emailIsUsable && $emailIsVerified) {
            $user = User::query()->where('email', $email)->first();
        }

        if ($user !== null) {
            $request->session()->forget(self::SIGNUP_ROLE);

            return $this->loginExisting($request, $user, $googleUser, $googleId);
        }

        $role = $request->session()->pull(self::SIGNUP_ROLE);

        if (! is_string($role) || ! array_key_exists($role, User::SIGNUP_ROLES)) {
            return redirect()->route('register')->withErrors([
                'role' => 'Choose a role on the sign-up page before continuing with Google.',
            ]);
        }

        if (! $emailIsUsable || ! $emailIsVerified) {
            return redirect()->route('register')->withErrors([
                'google' => 'Google did not share a confirmed email address, so no account was created.',
            ]);
        }

        $user = User::query()->create([
            'name' => $this->nameFromGoogle($googleUser, $email),
            'email' => $email,
            'password' => Str::random(40),
            'role' => $role,
            'status' => 'active',
            'google_id' => $googleId,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->to($user->homeRoute());
    }

    private function loginExisting(Request $request, User $user, SocialiteUser $googleUser, string $googleId): RedirectResponse
    {
        if (! $user->canSignIn()) {
            return redirect()->route('login')->withErrors([
                'email' => 'This account cannot sign in.',
            ]);
        }

        if ($user->google_id !== null && $user->google_id !== $googleId) {
            return redirect()->route('login')->withErrors([
                'email' => 'This email is already linked to a different Google account.',
            ]);
        }

        $changes = [];

        if ($user->google_id === null) {
            $changes['google_id'] = $googleId;
        }

        if ($user->email_verified_at === null && $this->googleEmailIsVerified($googleUser)) {
            $changes['email_verified_at'] = now();
        }

        if ($changes !== []) {
            $user->forceFill($changes)->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended($user->homeRoute());
    }

    private function googleEmailIsVerified(SocialiteUser $googleUser): bool
    {
        if (! $googleUser instanceof GoogleUser) {
            return false;
        }

        $raw = $googleUser->user ?? [];

        if (array_key_exists('email_verified', $raw)) {
            return $raw['email_verified'] === true;
        }

        if (array_key_exists('verified_email', $raw)) {
            return $raw['verified_email'] === true;
        }

        return false;
    }

    private function nameFromGoogle(SocialiteUser $googleUser, string $email): string
    {
        $name = trim((string) $googleUser->getName());

        if ($name === '') {
            $name = strstr($email, '@', true) ?: 'Member';
        }

        return mb_substr($name, 0, 255);
    }
}
