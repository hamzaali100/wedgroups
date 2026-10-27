<?php

namespace Tests\Feature\Admin;

use App\Models\Candidate;
use App\Models\Country;
use App\Models\Group;
use App\Models\GroupAdmin;
use App\Models\Religion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAccountTest extends TestCase
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

    public function test_only_an_active_admin_can_open_admin_pages(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');

        $candidate = User::factory()->create();
        $this->actingAs($candidate)->get('/admin/users')->assertForbidden();

        $groupAdmin = User::factory()->create(['role' => 'group_admin']);
        $this->actingAs($groupAdmin)->get('/admin/group-admins')->assertForbidden();

        $suspended = User::factory()->create([
            'role' => 'admin',
            'status' => 'suspended',
        ]);
        $this->actingAs($suspended)->get('/admin/users')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_an_active_admin_signs_in_to_the_user_list(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect('/admin/users');

        $this->get('/admin/users')->assertOk();
    }

    public function test_admin_creates_edits_suspends_and_resets_a_user_without_changing_role_from_the_details_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Hira',
            'email' => 'hira@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'candidate',
            'status' => 'active',
        ])->assertRedirect();

        $user = User::query()->where('email', 'hira@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('Password1', $user->password));

        $this->actingAs($admin)->put('/admin/users/'.$user->id, [
            'name' => 'Hira Khan',
            'email' => 'hira@example.com',
            'role' => 'admin',
            'status' => 'suspended',
            'password' => 'Hacked123',
        ])->assertRedirect();

        $user->refresh();
        $this->assertSame('Hira Khan', $user->name);
        $this->assertSame('candidate', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertTrue(Hash::check('Password1', $user->password));

        $this->actingAs($admin)->post('/admin/users/'.$admin->id.'/suspend')->assertForbidden();
        $this->actingAs($admin)->put('/admin/users/'.$admin->id.'/role', [
            'role' => 'candidate',
        ])->assertForbidden();
        $this->actingAs($admin)->put('/admin/users/'.$admin->id.'/password', [
            'password' => 'Password2',
            'password_confirmation' => 'Password2',
        ])->assertForbidden();

        $this->actingAs($admin)->post('/admin/users/'.$user->id.'/suspend')->assertRedirect();
        $this->assertSame('suspended', $user->fresh()->status);

        $this->post('/logout');
        $this->post('/login', [
            'email' => 'hira@example.com',
            'password' => 'Password1',
        ])->assertSessionHasErrors('email');

        $this->actingAs($admin)->post('/admin/users/'.$user->id.'/reactivate')->assertRedirect();
        $this->actingAs($admin)->put('/admin/users/'.$user->id.'/password', [
            'password' => 'Password2',
            'password_confirmation' => 'Password2',
        ])->assertRedirect();

        $this->post('/logout');
        $this->post('/login', [
            'email' => 'hira@example.com',
            'password' => 'Password2',
        ])->assertRedirect();

        $this->actingAs($admin)->put('/admin/users/'.$user->id.'/role', [
            'role' => 'relative',
        ])->assertRedirect();

        $this->assertSame('relative', $user->fresh()->role);

        $this->post('/logout');
        $this->post('/login', [
            'email' => 'hira@example.com',
            'password' => 'Password2',
        ])->assertSessionHasErrors('email');
    }

    public function test_a_literal_percent_search_does_not_match_every_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Platform']);

        $this->actingAs($admin)
            ->get('/admin/users?search='.urlencode('%'))
            ->assertInertia(fn (Assert $page) => $page->has('users.data', 0));
    }

    public function test_group_admin_accounts_are_approved_assigned_and_their_activity_hides_private_names(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $group = Group::query()->create([
            'name' => 'Doctors',
            'description' => 'Doctors only',
        ]);
        $other = Group::query()->create([
            'name' => 'Bankers',
            'description' => 'Bankers only',
        ]);

        $this->actingAs($admin)->post('/admin/group-admins', [
            'name' => 'Imran',
            'email' => 'imran@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'phone' => '03001234567',
            'description' => 'Armed forces circle',
        ])->assertRedirect();

        $profile = GroupAdmin::query()->where('name', 'Imran')->first();
        $this->assertNotNull($profile);
        $this->assertNull($profile->approval_date);

        $this->actingAs($admin)->post('/admin/group-admins/'.$profile->user_id.'/groups', [
            'group_id' => $group->id,
        ])->assertSessionHasErrors('group_id');
        $this->assertDatabaseMissing('group_group_admin', [
            'user_id' => $profile->user_id,
        ]);

        $this->actingAs($admin)->post('/admin/group-admins/'.$profile->user_id.'/approve')->assertRedirect();
        $this->assertNotNull($profile->fresh()->approval_date);

        $this->actingAs($admin)->put('/admin/group-admins/'.$profile->user_id, [
            'name' => 'Imran Ali',
            'email' => 'imran.ali@example.com',
            'phone' => '03007654321',
            'whatsapp' => '03007654321',
            'description' => 'Updated circle',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $profile->user_id,
            'name' => 'Imran Ali',
            'email' => 'imran.ali@example.com',
            'role' => 'group_admin',
        ]);

        $this->actingAs($admin)->post('/admin/group-admins/'.$profile->user_id.'/groups', [
            'group_id' => $group->id,
            'is_primary' => true,
        ])->assertRedirect();

        $second = User::factory()->create(['role' => 'group_admin', 'name' => 'Sana']);
        $second->groupAdmin()->create([
            'name' => 'Sana',
            'approval_date' => now(),
        ]);
        $this->actingAs($admin)->post('/admin/group-admins/'.$second->id.'/groups', [
            'group_id' => $group->id,
            'is_primary' => true,
        ])->assertRedirect();

        $this->assertSame(0, (int) DB::table('group_group_admin')->where('user_id', $profile->user_id)->value('is_primary'));
        $this->assertSame(1, (int) DB::table('group_group_admin')->where('user_id', $second->id)->value('is_primary'));

        $this->actingAs($admin)->put('/admin/users/'.$profile->user_id.'/role', [
            'role' => 'relative',
        ])->assertSessionHasErrors('role');
        $this->assertSame('group_admin', $profile->user->fresh()->role);

        $candidate = User::factory()->create();
        Candidate::factory()->create([
            'user_id' => $candidate->id,
            'display_name' => 'Hira',
            'real_name' => 'Secret Khan',
        ]);

        DB::table('candidate_group')->insert([
            'group_id' => $group->id,
            'candidate_id' => $candidate->id,
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => $profile->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('group_admin_candidate')->insert([
            'group_admin_id' => $profile->user_id,
            'candidate_id' => $candidate->id,
            'notes' => 'Called the family',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin/group-admins/'.$profile->user_id)
            ->assertOk()
            ->assertSee('Hira')
            ->assertSee('Called the family')
            ->assertSee('Added to Doctors')
            ->assertDontSee('Secret Khan');

        $this->actingAs($admin)
            ->delete('/admin/group-admins/'.$profile->user_id.'/groups/'.$other->id)
            ->assertNotFound();

        $this->actingAs($admin)
            ->delete('/admin/group-admins/'.$profile->user_id.'/groups/'.$group->id)
            ->assertRedirect();

        $this->assertDatabaseMissing('group_group_admin', [
            'group_id' => $group->id,
            'user_id' => $profile->user_id,
        ]);

        $this->actingAs($admin)->post('/admin/users/'.$profile->user_id.'/suspend')->assertRedirect();
        $this->assertSame('suspended', $profile->user->fresh()->status);
        $this->actingAs($admin)->post('/admin/users/'.$profile->user_id.'/reactivate')->assertRedirect();
        $this->assertSame('active', $profile->user->fresh()->status);
    }
}
