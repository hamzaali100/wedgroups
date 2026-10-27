<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleSignInTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_offers_google_without_choosing_a_role(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Login')
                ->where('googleUrl', route('auth.google.login')));
    }

    public function test_google_login_drops_any_role_chosen_earlier(): void
    {
        Socialite::fake('google');

        $this->withSession([GoogleAuthController::SIGNUP_ROLE => 'candidate'])
            ->get('/auth/google')
            ->assertRedirect('https://socialite.fake/google/authorize');

        $this->assertNull(session(GoogleAuthController::SIGNUP_ROLE));
    }

    public function test_google_signup_keeps_only_an_allowed_role(): void
    {
        Socialite::fake('google');

        $this->post('/auth/google', ['role' => 'admin'])->assertSessionHasErrors('role');
        $this->post('/auth/google', [])->assertSessionHasErrors('role');
        $this->assertNull(session(GoogleAuthController::SIGNUP_ROLE));
        $this->assertDatabaseCount('users', 0);

        $this->post('/auth/google', ['role' => 'group_admin'])
            ->assertRedirect('https://socialite.fake/google/authorize');

        $this->assertSame('group_admin', session(GoogleAuthController::SIGNUP_ROLE));
    }

    public function test_google_login_without_a_chosen_role_creates_nothing(): void
    {
        $this->fakeGoogle();

        $this->get('/auth/google/callback')
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('role');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_a_tampered_role_does_not_create_an_account(): void
    {
        $this->fakeGoogle();

        foreach (['admin', 'relative', ''] as $role) {
            $this->withSession([GoogleAuthController::SIGNUP_ROLE => $role])
                ->get('/auth/google/callback')
                ->assertRedirect(route('register'))
                ->assertSessionHasErrors('role');
        }

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_signup_creates_an_account_only_for_the_chosen_role(): void
    {
        $this->fakeGoogle([
            'id' => 'google-group-admin',
            'name' => 'Sana Ali',
            'email' => 'sana@example.com',
        ]);

        $this->withSession([GoogleAuthController::SIGNUP_ROLE => 'group_admin'])
            ->get('/auth/google/callback')
            ->assertRedirect(route('group_admin.groups.index'));

        $user = User::query()->where('email', 'sana@example.com')->first();

        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
        $this->assertSame('group_admin', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertSame('google-group-admin', $user->google_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull(session(GoogleAuthController::SIGNUP_ROLE));
        $this->assertDatabaseCount('users', 1);

        $this->actingAs($user)->get('/manage/profile/edit')->assertOk();
    }

    public function test_an_unconfirmed_google_email_does_not_create_an_account(): void
    {
        $this->fakeGoogle([
            'email' => 'aisha@example.com',
            'email_verified' => false,
        ]);

        $this->withSession([GoogleAuthController::SIGNUP_ROLE => 'candidate'])
            ->get('/auth/google/callback')
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('google');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_an_existing_account_can_sign_in_with_google_without_picking_a_role_again(): void
    {
        $user = User::factory()->create([
            'name' => 'Aisha Khan',
            'email' => 'aisha@example.com',
            'role' => 'candidate',
            'email_verified_at' => null,
        ]);

        $this->fakeGoogle();

        $this->withSession([GoogleAuthController::SIGNUP_ROLE => 'group_admin'])
            ->get('/auth/google/callback')
            ->assertRedirect(route('candidate.profile.create'));

        $user->refresh();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('candidate', $user->role);
        $this->assertSame('google-1', $user->google_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_an_inactive_account_is_not_given_access(): void
    {
        $user = User::factory()->create([
            'email' => 'aisha@example.com',
            'role' => 'candidate',
            'status' => 'suspended',
        ]);

        $this->fakeGoogle();

        $this->withSession([GoogleAuthController::SIGNUP_ROLE => 'group_admin'])
            ->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame('candidate', $user->fresh()->role);
        $this->assertNull($user->fresh()->google_id);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_google_does_not_link_an_email_that_already_belongs_to_another_google_account(): void
    {
        $user = User::factory()->create([
            'email' => 'aisha@example.com',
            'role' => 'candidate',
            'google_id' => 'original-google',
        ]);

        $this->fakeGoogle(['id' => 'other-google']);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame('original-google', $user->fresh()->google_id);
        $this->assertSame('candidate', $user->fresh()->role);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function fakeGoogle(array $attributes = []): void
    {
        Socialite::fake('google', SocialiteUser::fake(array_merge([
            'id' => 'google-1',
            'name' => 'Aisha Khan',
            'email' => 'aisha@example.com',
            'email_verified' => true,
        ], $attributes)));
    }
}
