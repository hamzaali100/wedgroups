<?php

namespace Tests\Feature\Admin;

use App\Models\Candidate;
use App\Models\Country;
use App\Models\Group;
use App\Models\Religion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
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

    public function test_candidate_profiles_need_approval_and_admin_review_hides_private_data_from_peers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $viewer = User::factory()->create(['email' => 'viewer@example.com']);
        Candidate::factory()->create([
            'user_id' => $viewer->id,
            'display_name' => 'Viewer',
            'real_name' => 'Viewer Khan',
            'gender' => 'male',
        ]);

        $subject = User::factory()->create(['email' => 'hira@example.com']);
        $this->actingAs($subject)->post('/profile', [
            'real_name' => 'Secret Khan',
            'display_name' => 'Hira',
            'year_of_birth' => 1996,
            'month_of_birth' => 4,
            'gender' => 'male',
            'height' => 175,
            'marital_status' => 'never_married',
            'religion_id' => 1,
            'country_id' => 'PAK',
            'education_level' => 'bachelors_degree',
        ])->assertRedirect();

        $profile = Candidate::query()->find($subject->id);
        $this->assertSame('pending', $profile->review_status);

        $group = Group::query()->create([
            'name' => 'Doctors',
            'description' => 'Doctors only',
            'male_pics_allowed' => true,
        ]);
        $group->candidates()->attach($viewer->id, ['status' => 'active']);
        $group->candidates()->attach($subject->id, ['status' => 'active']);

        $this->actingAs($viewer)->get('/candidates/'.$subject->id)->assertNotFound();

        $this->actingAs($admin)->get('/admin/candidates/'.$subject->id)
            ->assertOk()
            ->assertSee('Secret Khan');

        $this->actingAs($admin)->put('/admin/candidates/'.$subject->id, [
            'real_name' => 'Secret Khan',
            'display_name' => 'Hira Ali',
            'year_of_birth' => 1996,
            'month_of_birth' => 4,
            'gender' => 'male',
            'height' => 175,
            'marital_status' => 'never_married',
            'religion_id' => 1,
            'country_id' => 'PAK',
            'education_level' => 'masters',
            'profile_visibility' => 'visible',
            'profile_status' => 'active',
            'review_status' => 'rejected',
            'rejection_reason' => 'Should be ignored',
        ])->assertRedirect();

        $this->assertSame('pending', $profile->fresh()->review_status);
        $this->assertSame('Hira Ali', $profile->fresh()->display_name);
        $this->assertNull($profile->fresh()->rejection_reason);

        $this->actingAs($admin)->post('/admin/candidates/'.$subject->id.'/reject', [])
            ->assertSessionHasErrors('rejection_reason');

        $this->actingAs($admin)->post('/admin/candidates/'.$subject->id.'/reject', [
            'rejection_reason' => 'Details do not match the group.',
        ])->assertRedirect();

        $this->actingAs($viewer)->get('/candidates/'.$subject->id)->assertNotFound();

        $this->actingAs($admin)->post('/admin/candidates/'.$subject->id.'/approve')->assertRedirect();
        $this->actingAs($viewer)->get('/candidates/'.$subject->id)->assertOk()->assertDontSee('Secret Khan');

        $this->actingAs($admin)->post('/admin/candidates/'.$subject->id.'/suspend')->assertRedirect();
        $this->assertSame('active', $subject->fresh()->status);
        $this->actingAs($viewer)->get('/candidates/'.$subject->id)->assertNotFound();

        $this->actingAs($admin)->post('/admin/candidates/'.$subject->id.'/reactivate')->assertRedirect();
        $this->actingAs($viewer)->get('/candidates/'.$subject->id)->assertOk();

        $profile->photos()->create([
            'photo' => 'photos/hira-private.jpg',
            'photo_year' => 2024,
            'is_main' => true,
            'sort_order' => 1,
        ]);

        $groupAdmin = User::factory()->create(['role' => 'group_admin', 'email' => 'sana@example.com']);
        $groupAdmin->groupAdmin()->create([
            'name' => 'Sana',
            'approval_date' => now(),
        ]);
        $group->admins()->attach($groupAdmin->id, [
            'is_primary' => true,
            'approval_date' => now(),
            'approved_by' => $admin->id,
        ]);

        $this->actingAs($groupAdmin)
            ->get('/manage/groups/'.$group->id.'/candidates/'.$subject->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('photos', 0));

        $photoId = $profile->photos()->first()->id;
        $this->actingAs($admin)
            ->post('/admin/candidates/'.$subject->id.'/photos/'.$photoId.'/approve')
            ->assertRedirect();

        $approved = DB::table('candidate_photos')->where('id', $photoId)->first();
        $this->assertSame('approved', $approved->review_status);
        $this->assertSame($admin->id, (int) $approved->approved_by);
        $this->assertNotNull($approved->approved_at);

        $this->actingAs($groupAdmin)
            ->get('/manage/groups/'.$group->id.'/candidates/'.$subject->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('photos', 1)
                ->where('photos.0.url', route('photos.show', ['photo' => $photoId, 'v' => 0], false)));

        $other = Group::query()->create([
            'name' => 'Bankers',
            'description' => 'Bankers only',
        ]);
        $this->actingAs($admin)->post('/admin/candidates/'.$subject->id.'/groups', [
            'group_id' => $other->id,
            'status' => 'pending',
        ])->assertRedirect();
        $this->assertDatabaseHas('candidate_group', [
            'group_id' => $other->id,
            'candidate_id' => $subject->id,
            'status' => 'pending',
        ]);
        $this->actingAs($admin)
            ->delete('/admin/candidates/'.$subject->id.'/groups/'.$other->id)
            ->assertRedirect();
        $this->assertDatabaseMissing('candidate_group', [
            'group_id' => $other->id,
            'candidate_id' => $subject->id,
        ]);

        DB::table('candidate_candidates_liked')->insert([
            'candidate_id' => $viewer->id,
            'liked_candidate_id' => $subject->id,
            'created_at' => now(),
        ]);

        $this->actingAs($admin)->delete('/admin/candidates/'.$subject->id)->assertRedirect('/admin/candidates');
        $this->assertDatabaseMissing('candidates', ['user_id' => $subject->id]);
        $this->assertDatabaseHas('users', ['id' => $subject->id, 'role' => 'candidate']);
        $this->assertDatabaseMissing('candidate_candidates_liked', ['liked_candidate_id' => $subject->id]);
    }

    public function test_an_admin_can_manage_any_group_and_its_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/groups', [
            'name' => 'Armed forces',
            'description' => 'Service circle',
            'male_pics_allowed' => true,
            'female_pics_allowed' => false,
            'status' => 'suspended',
        ])->assertRedirect();

        $group = Group::query()->where('name', 'Armed forces')->first();
        $this->assertSame('active', $group->status);
        $this->assertDatabaseMissing('group_group_admin', ['group_id' => $group->id]);

        $this->actingAs($admin)->put('/admin/groups/'.$group->id, [
            'name' => 'Armed Forces only',
            'description' => 'Service circle',
            'male_pics_allowed' => true,
            'female_pics_allowed' => false,
            'status' => 'suspended',
        ])->assertRedirect();
        $this->assertSame('active', $group->fresh()->status);

        $this->actingAs($admin)->post('/admin/groups/'.$group->id.'/deactivate')->assertRedirect();
        $group->update(['status' => 'suspended']);
        $this->actingAs($admin)->post('/admin/groups/'.$group->id.'/activate')->assertRedirect();
        $this->assertSame('active', $group->fresh()->status);

        $unapproved = User::factory()->create(['role' => 'group_admin', 'email' => 'waiting@example.com']);
        $unapproved->groupAdmin()->create(['name' => 'Waiting']);
        $this->actingAs($admin)->post('/admin/groups/'.$group->id.'/admins', [
            'group_admin_id' => $unapproved->id,
        ])->assertSessionHasErrors('group_admin_id');

        $unapproved->groupAdmin->update(['approval_date' => now()]);
        $this->actingAs($admin)->post('/admin/groups/'.$group->id.'/admins', [
            'group_admin_id' => $unapproved->id,
            'is_primary' => true,
        ])->assertRedirect();
        $this->assertDatabaseHas('group_group_admin', [
            'group_id' => $group->id,
            'user_id' => $unapproved->id,
            'is_primary' => 1,
        ]);

        $member = User::factory()->create();
        Candidate::factory()->create(['user_id' => $member->id]);
        $group->candidates()->attach($member->id, ['status' => 'active']);

        $this->actingAs($admin)->delete('/admin/groups/'.$group->id.'/admins/'.$unapproved->id)->assertRedirect();
        $this->actingAs($admin)->delete('/admin/groups/'.$group->id)->assertRedirect('/admin/groups');
        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
        $this->assertDatabaseMissing('candidate_group', ['group_id' => $group->id]);
        $this->assertDatabaseHas('candidates', ['user_id' => $member->id]);
    }

    public function test_reports_are_aggregates_and_closed_to_other_roles(): void
    {
        $this->get('/admin/reports')->assertRedirect('/login');

        $candidate = User::factory()->create();
        Candidate::factory()->create([
            'user_id' => $candidate->id,
            'real_name' => 'Secret Report Name',
        ]);

        $this->actingAs($candidate)->get('/admin/candidates')->assertForbidden();
        $this->actingAs($candidate)->get('/admin/groups')->assertForbidden();
        $this->actingAs($candidate)->get('/admin/reports')->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/reports')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Reports/Index')
                ->where('reports.candidates.total', 1)
                ->where('reports.matching.likes', 0))
            ->assertDontSee('Secret Report Name');
    }
}
