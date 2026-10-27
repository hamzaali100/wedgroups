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

    public function test_registration_always_creates_a_candidate_account(): void
    {
        $response = $this->post('/register', [
            'name' => 'Aisha Khan',
            'email' => 'aisha@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'admin',
            'status' => 'suspended',
        ]);

        $response->assertRedirect(route('candidate.profile.create'));

        $this->assertDatabaseHas('users', [
            'email' => 'aisha@example.com',
            'role' => 'candidate',
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
