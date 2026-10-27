<?php

namespace Tests\Feature\Candidate;

use App\Models\Candidate;
use App\Models\Country;
use App\Models\Group;
use App\Models\Religion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CandidateAccessTest extends TestCase
{
    use RefreshDatabase;

    private Group $group;

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

        $this->group = Group::query()->create([
            'name' => 'Doctors',
            'description' => 'Doctors only',
        ]);
    }

    public function test_a_candidate_cannot_view_someone_outside_their_group(): void
    {
        $viewer = $this->member('Aisha', ['phone_number' => '03001111111', 'real_name' => 'Aisha Private']);
        $stranger = $this->member('Hira', [
            'phone_number' => '03002222222',
            'real_name' => 'Hira Secret',
            'disabilities' => 'Private health note',
        ], Group::query()->create([
            'name' => 'Bankers',
            'description' => 'Bankers only',
        ]));

        $this->actingAs($viewer)
            ->get('/candidates/'.$stranger->id)
            ->assertNotFound();

        $this->actingAs($viewer)
            ->get('/candidates')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Candidate/Discovery/Index')
                ->has('candidates.data', 0));

        $this->actingAs($viewer)
            ->post('/candidates/'.$stranger->id.'/pass')
            ->assertNotFound();
        $this->actingAs($viewer)
            ->post('/candidates/'.$stranger->id.'/like')
            ->assertNotFound();
        $this->actingAs($viewer)
            ->post('/candidates/'.$stranger->id.'/favorite')
            ->assertNotFound();

        $this->assertDatabaseMissing('candidate_candidates_passed', [
            'candidate_id' => $viewer->id,
            'passed_candidate_id' => $stranger->id,
        ]);
        $this->assertDatabaseMissing('candidate_candidates_liked', [
            'candidate_id' => $viewer->id,
            'liked_candidate_id' => $stranger->id,
        ]);
        $this->assertDatabaseMissing('candidate_candidates_favorited', [
            'candidate_id' => $viewer->id,
            'favorited_candidate_id' => $stranger->id,
        ]);
    }

    public function test_a_candidate_can_view_and_filter_people_in_the_same_active_group(): void
    {
        $viewer = $this->member('Aisha');
        $match = $this->member('Hira', [
            'gender' => 'male',
            'phone_number' => '03002222222',
            'real_name' => 'Hira Secret',
            'disabilities' => 'Private health note',
            'parent_contact_number' => '03003333333',
            'year_of_birth' => now()->year - 40,
        ]);
        $this->member('Sara', [
            'gender' => 'female',
            'year_of_birth' => now()->year - 20,
        ]);

        $this->actingAs($viewer)
            ->get('/candidates/'.$match->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Candidate/Discovery/Show')
                ->where('profile.display_name', 'Hira')
                ->missing('profile.real_name')
                ->missing('profile.phone_number')
                ->missing('profile.email'))
            ->assertDontSee('Hira Secret')
            ->assertDontSee('03002222222')
            ->assertDontSee('Private health note')
            ->assertDontSee('03003333333');

        $this->actingAs($viewer)
            ->get('/candidates?gender=male&age_min=30')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('candidates.data', 1)
                ->where('candidates.data.0.display_name', 'Hira')
                ->missing('candidates.data.0.real_name'));
    }

    public function test_pending_hidden_paused_and_suspended_profiles_stay_closed(): void
    {
        $viewer = $this->member('Aisha');
        $pending = $this->member('Pending', [], $this->group, 'pending');
        $hidden = $this->member('Hidden', ['profile_visibility' => 'hidden']);
        $paused = $this->member('Paused', ['profile_status' => 'paused']);

        $closedGroup = Group::query()->create([
            'name' => 'Closed',
            'description' => 'Not active',
            'status' => 'suspended',
        ]);
        $closedGroup->candidates()->attach($viewer->id, ['status' => 'active']);
        $suspended = $this->member('Suspended', [], $closedGroup);

        $this->actingAs($viewer)->get('/candidates/'.$pending->id)->assertNotFound();
        $this->actingAs($viewer)->get('/candidates/'.$hidden->id)->assertNotFound();
        $this->actingAs($viewer)->get('/candidates/'.$paused->id)->assertNotFound();
        $this->actingAs($viewer)->get('/candidates/'.$suspended->id)->assertNotFound();
        $this->actingAs($viewer)->get('/candidates/'.$viewer->id)->assertNotFound();
    }

    public function test_passing_hides_a_candidate_until_it_is_undone(): void
    {
        $viewer = $this->member('Aisha');
        $match = $this->member('Hira');

        $this->actingAs($viewer)->post('/candidates/'.$match->id.'/pass')->assertRedirect();

        $this->actingAs($viewer)
            ->get('/candidates')
            ->assertInertia(fn (Assert $page) => $page->has('candidates.data', 0));

        $this->actingAs($viewer)
            ->get('/passed')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Candidate/Passed/Index')
                ->where('passed.0.display_name', 'Hira')
                ->where('passed.0.available', true));

        $this->actingAs($viewer)
            ->get('/candidates/'.$match->id)
            ->assertOk();

        $this->actingAs($viewer)->delete('/candidates/'.$match->id.'/pass')->assertRedirect();

        $this->actingAs($viewer)
            ->get('/candidates')
            ->assertInertia(fn (Assert $page) => $page
                ->has('candidates.data', 1)
                ->where('candidates.data.0.display_name', 'Hira'));
    }

    public function test_likes_and_favorites_stay_inside_the_shared_group(): void
    {
        $viewer = $this->member('Aisha');
        $match = $this->member('Hira', [
            'real_name' => 'Hira Secret',
            'phone_number' => '03002222222',
        ]);

        $this->actingAs($viewer)->post('/candidates/'.$match->id.'/like')->assertRedirect();
        $this->actingAs($viewer)->post('/candidates/'.$match->id.'/favorite')->assertRedirect();

        $this->actingAs($viewer)
            ->get('/candidates')
            ->assertInertia(fn (Assert $page) => $page->has('candidates.data', 1));

        $this->actingAs($viewer)
            ->get('/likes')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Candidate/Likes/Index')
                ->where('liked.0.display_name', 'Hira')
                ->where('liked.0.available', true))
            ->assertDontSee('Hira Secret')
            ->assertDontSee('03002222222');

        $this->actingAs($viewer)
            ->get('/favorites')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Candidate/Favorites/Index')
                ->where('favorites.0.display_name', 'Hira'));

        $this->actingAs($viewer)
            ->get('/candidates/'.$match->id)
            ->assertInertia(fn (Assert $page) => $page
                ->where('liked', true)
                ->where('favorited', true));

        $this->actingAs($viewer)->delete('/candidates/'.$match->id.'/like')->assertRedirect();
        $this->actingAs($viewer)->delete('/candidates/'.$match->id.'/favorite')->assertRedirect();

        $this->actingAs($viewer)
            ->get('/likes')
            ->assertInertia(fn (Assert $page) => $page->has('liked', 0));
        $this->actingAs($viewer)
            ->get('/favorites')
            ->assertInertia(fn (Assert $page) => $page->has('favorites', 0));

        $this->actingAs($viewer)->delete('/candidates/'.$match->id.'/like')->assertNotFound();
    }

    public function test_leaving_a_group_removes_access_to_its_members(): void
    {
        $viewer = $this->member('Aisha');
        $match = $this->member('Hira');

        $this->actingAs($viewer)->delete('/groups/'.$this->group->id)->assertRedirect();

        $this->assertDatabaseHas('candidate_group', [
            'group_id' => $this->group->id,
            'candidate_id' => $viewer->id,
            'status' => 'inactive',
        ]);

        $this->actingAs($viewer)->get('/candidates/'.$match->id)->assertNotFound();
        $this->actingAs($viewer)->delete('/groups/99999')->assertNotFound();
    }

    public function test_account_changes_cannot_alter_role_and_deactivation_signs_the_candidate_out(): void
    {
        $user = $this->member('Aisha');

        $this->actingAs($user)->put('/account', [
            'name' => 'Aisha Khan',
            'email' => 'aisha@example.com',
            'role' => 'admin',
            'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Aisha Khan',
            'email' => 'aisha@example.com',
            'role' => 'candidate',
        ]);

        $this->actingAs($user)->put('/profile/personal', [
            'real_name' => 'Aisha Khan',
            'display_name' => 'Aisha',
            'year_of_birth' => 1996,
            'month_of_birth' => 4,
            'gender' => 'female',
            'height' => 165,
            'marital_status' => 'never_married',
            'religion_id' => 1,
            'country_id' => 'PAK',
            'phone_number' => '03001111111',
            'profile_visibility' => 'hidden',
            'user_id' => 999,
        ])->assertRedirect();

        $this->assertDatabaseHas('candidates', [
            'user_id' => $user->id,
            'phone_number' => '03001111111',
            'profile_visibility' => 'visible',
        ]);

        $this->actingAs($user)->put('/account/password', [
            'current_password' => 'password',
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertRedirect();

        $this->actingAs($user)->post('/account/deactivate', [
            'current_password' => 'NewPassword1',
            'confirm' => true,
        ])->assertRedirect('/login');

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'inactive',
        ]);

        $this->post('/login', [
            'email' => 'aisha@example.com',
            'password' => 'NewPassword1',
        ])->assertSessionHasErrors('email');
    }

    public function test_other_roles_and_guests_cannot_browse(): void
    {
        $this->get('/candidates')->assertRedirect('/login');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/candidates')->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private function member(string $displayName, array $profile = [], ?Group $group = null, string $membership = 'active'): User
    {
        $user = User::factory()->create([
            'name' => $displayName,
            'email' => strtolower($displayName).'@example.com',
        ]);

        Candidate::factory()->create([
            'user_id' => $user->id,
            'display_name' => $displayName,
            'real_name' => $displayName.' Khan',
            ...$profile,
        ]);

        ($group ?? $this->group)->candidates()->attach($user->id, [
            'status' => $membership,
        ]);

        return $user;
    }
}
