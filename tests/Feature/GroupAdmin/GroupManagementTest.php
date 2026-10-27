<?php

namespace Tests\Feature\GroupAdmin;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GroupManagementTest extends TestCase
{
    use InteractsWithGroupAdmins;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedReferences();
    }

    public function test_guests_and_other_roles_cannot_manage_groups(): void
    {
        $this->get('/manage/groups')->assertRedirect('/login');

        $candidate = User::factory()->create();
        $this->actingAs($candidate)->get('/manage/groups')->assertForbidden();

        $bare = User::factory()->create([
            'role' => 'group_admin',
            'email' => 'bare@example.com',
        ]);

        $this->actingAs($bare)->get('/manage/groups')->assertRedirect(route('group_admin.profile.edit'));
    }

    public function test_an_admin_can_create_and_view_a_group(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/manage/groups', [
            'name' => 'Doctors',
            'description' => 'Doctors only',
            'male_pics_allowed' => true,
            'female_pics_allowed' => false,
            'status' => 'suspended',
        ])->assertRedirect();

        $group = Group::query()->where('name', 'Doctors')->first();

        $this->assertNotNull($group);
        $this->assertSame('active', $group->status);
        $this->assertTrue($group->male_pics_allowed);
        $this->assertFalse($group->female_pics_allowed);
        $this->assertDatabaseHas('group_group_admin', [
            'group_id' => $group->id,
            'user_id' => $admin->id,
            'is_primary' => true,
            'approved_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/manage/groups/'.$group->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GroupAdmin/Groups/Show')
                ->where('group.name', 'Doctors')
                ->where('group.description', 'Doctors only')
                ->where('group.status', 'active')
                ->where('group.is_primary', true)
                ->where('group.admin_count', 1));
    }

    public function test_group_details_can_be_edited_without_changing_status(): void
    {
        $admin = $this->admin();
        $group = $this->ownedGroup($admin);

        $this->actingAs($admin)->put('/manage/groups/'.$group->id, [
            'name' => 'Surgeons',
            'description' => 'Surgeons only',
            'male_pics_allowed' => false,
            'female_pics_allowed' => true,
            'status' => 'suspended',
        ])->assertRedirect(route('group_admin.groups.show', $group));

        $group->refresh();

        $this->assertSame('Surgeons', $group->name);
        $this->assertSame('Surgeons only', $group->description);
        $this->assertFalse($group->male_pics_allowed);
        $this->assertTrue($group->female_pics_allowed);
        $this->assertSame('active', $group->status);
    }

    public function test_create_group_requires_a_name_and_description(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/manage/groups', [
            'name' => '',
            'description' => '',
        ])->assertSessionHasErrors(['name', 'description']);
    }

    public function test_an_admin_can_deactivate_and_activate_a_group(): void
    {
        $admin = $this->admin();
        $group = $this->ownedGroup($admin);
        $viewer = $this->member('Aisha', $group);
        $match = $this->member('Hira', $group);

        $this->actingAs($viewer)->get('/candidates/'.$match->id)->assertOk();

        $this->actingAs($admin)
            ->put('/manage/groups/'.$group->id.'/deactivate')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('inactive', $group->refresh()->status);
        $this->actingAs($viewer)->get('/candidates/'.$match->id)->assertNotFound();

        $this->actingAs($admin)
            ->put('/manage/groups/'.$group->id.'/activate')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('active', $group->refresh()->status);
        $this->actingAs($viewer)->get('/candidates/'.$match->id)->assertOk();
    }

    public function test_a_suspended_group_cannot_be_activated_or_deactivated_by_the_admin(): void
    {
        $admin = $this->admin();
        $group = $this->ownedGroup($admin, ['status' => 'suspended']);

        $this->actingAs($admin)
            ->put('/manage/groups/'.$group->id.'/activate')
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)
            ->put('/manage/groups/'.$group->id.'/deactivate')
            ->assertSessionHasErrors('status');

        $this->assertSame('suspended', $group->refresh()->status);
    }

    public function test_an_admin_can_view_add_and_remove_other_admins(): void
    {
        $admin = $this->admin();
        $other = $this->admin('Hina');
        $group = $this->ownedGroup($admin);
        $outsider = $this->ownedGroup($other, [
            'name' => 'Bankers',
            'description' => 'Bankers only',
        ]);

        $this->actingAs($admin)
            ->get('/manage/groups/'.$group->id.'/admins')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GroupAdmin/Groups/Admins')
                ->has('admins', 1)
                ->where('admins.0.email', 'sana@example.com')
                ->where('admins.0.is_you', true));

        $this->actingAs($admin)->post('/manage/groups/'.$group->id.'/admins', [
            'email' => 'Hina@example.com',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('group_group_admin', [
            'group_id' => $group->id,
            'user_id' => $other->id,
            'is_primary' => false,
            'approved_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/manage/groups/'.$group->id.'/admins')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('admins', 2));

        $this->actingAs($admin)
            ->delete('/manage/groups/'.$group->id.'/admins/'.$admin->id)
            ->assertSessionHasErrors('admin');

        $this->assertDatabaseHas('group_group_admin', [
            'group_id' => $group->id,
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->delete('/manage/groups/'.$group->id.'/admins/'.$other->id)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('group_group_admin', [
            'group_id' => $group->id,
            'user_id' => $other->id,
        ]);

        $this->actingAs($admin)->post('/manage/groups/'.$group->id.'/admins', [
            'email' => 'aisha@example.com',
        ])->assertSessionHasErrors('email');

        $this->actingAs($admin)->get('/manage/groups/'.$outsider->id)->assertNotFound();
        $this->actingAs($admin)->get('/manage/groups/'.$outsider->id.'/admins')->assertNotFound();
    }
}
