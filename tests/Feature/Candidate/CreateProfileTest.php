<?php

namespace Tests\Feature\Candidate;

use App\Models\Country;
use App\Models\Religion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CreateProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Country::query()->create([
            'id' => 'PAK',
            'name' => 'Pakistan',
            'nationality' => 'Pakistani',
        ]);

        Religion::query()->create([
            'id' => 1,
            'name' => 'Islam',
        ]);
    }

    public function test_guest_is_sent_to_login_before_creating_a_profile(): void
    {
        $this->get('/profile/create')->assertRedirect('/login');
    }

    public function test_another_role_cannot_open_the_candidate_profile_form(): void
    {
        $admin = User::factory()->create(['role' => 'group_admin']);

        $this->actingAs($admin)->get('/profile/create')->assertForbidden();
    }

    public function test_registration_requires_a_chosen_role_and_rejects_any_other(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Register')
                ->where('roles.candidate', 'Candidate')
                ->where('roles.group_admin', 'Group admin')
                ->missing('roles.admin')
                ->missing('roles.relative'));

        $this->post('/register', [
            'name' => 'Aisha Khan',
            'email' => 'aisha@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'status' => 'suspended',
        ])->assertSessionHasErrors('role');

        $this->post('/register', [
            'name' => 'Aisha Khan',
            'email' => 'aisha@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'admin',
            'status' => 'suspended',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseCount('users', 0);

        $this->post('/register', [
            'name' => 'Aisha Khan',
            'email' => 'aisha@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'candidate',
            'status' => 'suspended',
        ])->assertRedirect(route('candidate.profile.create'));

        $this->assertDatabaseHas('users', [
            'email' => 'aisha@example.com',
            'role' => 'candidate',
            'status' => 'active',
        ]);
    }

    public function test_registration_can_create_a_group_admin_when_that_role_is_chosen(): void
    {
        $this->post('/register', [
            'name' => 'Sana Ali',
            'email' => 'sana@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'group_admin',
        ])->assertRedirect(route('group_admin.groups.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'sana@example.com',
            'role' => 'group_admin',
            'status' => 'active',
        ]);
    }

    public function test_candidate_can_create_one_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profile', $this->profilePayload());

        $response->assertRedirect(route('candidate.profile.show'));
        $this->assertDatabaseHas('candidates', [
            'user_id' => $user->id,
            'display_name' => 'Aisha',
            'real_name' => 'Aisha Khan',
        ]);

        $this->actingAs($user)->post('/profile', $this->profilePayload())->assertForbidden();
    }

    public function test_profile_form_rejects_invalid_values(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile', ['display_name' => 'Aisha', 'user_id' => 999])
            ->assertSessionHasErrors([
                'real_name',
                'year_of_birth',
                'month_of_birth',
                'gender',
                'height',
                'marital_status',
                'religion_id',
                'country_id',
                'education_level',
            ]);

        $this->assertDatabaseCount('candidates', 0);
    }

    public function test_candidate_sees_only_their_own_profile(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($owner)->post('/profile', $this->profilePayload([
            'display_name' => 'Aisha',
        ]));

        $this->actingAs($other)->post('/profile', $this->profilePayload([
            'display_name' => 'Hira',
            'real_name' => 'Hira Malik',
        ]));

        $this->actingAs($owner)
            ->get('/profile')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Candidate/Profile/Show')
                ->where('profile.display_name', 'Aisha')
                ->missing('profile.email'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function profilePayload(array $overrides = []): array
    {
        return array_merge([
            'real_name' => 'Aisha Khan',
            'display_name' => 'Aisha',
            'year_of_birth' => 1996,
            'month_of_birth' => 4,
            'gender' => 'female',
            'height' => 165,
            'marital_status' => 'never_married',
            'religion_id' => 1,
            'country_id' => 'PAK',
            'education_level' => 'bachelors_degree',
        ], $overrides);
    }
}
