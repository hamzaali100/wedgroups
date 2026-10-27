<?php

namespace Tests\Feature\GroupAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use InteractsWithGroupAdmins;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedReferences();
    }

    public function test_a_group_admin_can_sign_in_and_view_their_profile(): void
    {
        $admin = $this->admin();

        $this->post('/login', [
            'email' => 'sana@example.com',
            'password' => 'password',
        ])->assertRedirect(route('group_admin.groups.index'));

        $this->actingAs($admin)
            ->get('/manage/profile')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GroupAdmin/Profile/Show')
                ->where('profile.name', 'Sana')
                ->where('profile.email', 'sana@example.com')
                ->where('profile.phone', '03001234567'));
    }

    public function test_an_admin_without_a_profile_can_create_one_and_then_manage_groups(): void
    {
        $bare = User::factory()->create([
            'name' => 'Bare Admin',
            'email' => 'bare@example.com',
            'role' => 'group_admin',
        ]);

        $this->actingAs($bare)->get('/manage/profile')->assertRedirect(route('group_admin.profile.edit'));

        $this->actingAs($bare)->put('/manage/profile', [
            'name' => 'Bare Admin',
            'email' => 'bare@example.com',
            'phone' => '03001112222',
            'whatsapp' => '03001112222',
            'description' => 'Looks after the doctors group.',
            'role' => 'admin',
            'status' => 'suspended',
        ])->assertRedirect(route('group_admin.profile.show'));

        $this->assertDatabaseHas('users', [
            'id' => $bare->id,
            'name' => 'Bare Admin',
            'email' => 'bare@example.com',
            'role' => 'group_admin',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('group_admins', [
            'user_id' => $bare->id,
            'name' => 'Bare Admin',
            'phone' => '03001112222',
            'description' => 'Looks after the doctors group.',
        ]);

        $this->actingAs($bare->fresh())->get('/manage/groups')->assertOk();
    }

    public function test_an_admin_can_edit_their_profile_and_change_their_password(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/manage/profile', [
            'name' => 'Sana Ali',
            'email' => 'sana.ali@example.com',
            'phone' => '03009998888',
            'whatsapp' => null,
            'description' => 'Karachi',
            'role' => 'candidate',
        ])->assertRedirect(route('group_admin.profile.show'));

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Sana Ali',
            'email' => 'sana.ali@example.com',
            'role' => 'group_admin',
        ]);

        $this->assertDatabaseHas('group_admins', [
            'user_id' => $admin->id,
            'name' => 'Sana Ali',
            'phone' => '03009998888',
            'whatsapp' => null,
        ]);

        $this->actingAs($admin)->put('/manage/profile/password', [
            'current_password' => 'wrong-password',
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($admin)->put('/manage/profile/password', [
            'current_password' => 'password',
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertRedirect()->assertSessionHas('success', 'Password updated.');

        $this->assertTrue(Hash::check('NewPassword1', $admin->refresh()->password));
    }
}
