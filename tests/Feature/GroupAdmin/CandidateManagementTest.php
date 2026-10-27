<?php

namespace Tests\Feature\GroupAdmin;

use App\Models\Candidate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CandidateManagementTest extends TestCase
{
    use InteractsWithGroupAdmins;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedReferences();
    }

    public function test_an_admin_can_add_approve_suspend_reactivate_and_remove_a_candidate(): void
    {
        $admin = $this->admin();
        $group = $this->ownedGroup($admin);
        $candidate = $this->member('Hira', $group, [], 'pending');

        $this->actingAs($admin)->post('/manage/groups/'.$group->id.'/candidates', [
            'email' => 'hira@example.com',
        ])->assertSessionHasErrors('email');

        $fresh = $this->member('Sara', $group);
        $group->candidates()->detach($fresh->id);

        $this->actingAs($admin)->post('/manage/groups/'.$group->id.'/candidates', [
            'email' => 'Sara@example.com',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('candidate_group', [
            'group_id' => $group->id,
            'candidate_id' => $fresh->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->put('/manage/groups/'.$group->id.'/candidates/'.$candidate->id.'/suspend')
            ->assertSessionHasErrors('membership');

        $this->actingAs($admin)
            ->put('/manage/groups/'.$group->id.'/candidates/'.$candidate->id.'/approve')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('candidate_group', [
            'group_id' => $group->id,
            'candidate_id' => $candidate->id,
            'status' => 'active',
            'approved_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->put('/manage/groups/'.$group->id.'/candidates/'.$candidate->id.'/approve')
            ->assertSessionHasErrors('membership');

        $this->actingAs($admin)
            ->put('/manage/groups/'.$group->id.'/candidates/'.$candidate->id.'/suspend')
            ->assertRedirect();

        $this->assertDatabaseHas('candidate_group', [
            'group_id' => $group->id,
            'candidate_id' => $candidate->id,
            'status' => 'inactive',
        ]);

        $this->actingAs($admin)
            ->put('/manage/groups/'.$group->id.'/candidates/'.$candidate->id.'/reactivate')
            ->assertRedirect();

        $this->assertDatabaseHas('candidate_group', [
            'group_id' => $group->id,
            'candidate_id' => $candidate->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->delete('/manage/groups/'.$group->id.'/candidates/'.$candidate->id)
            ->assertRedirect(route('group_admin.groups.candidates.index', $group));

        $this->assertDatabaseMissing('candidate_group', [
            'group_id' => $group->id,
            'candidate_id' => $candidate->id,
        ]);
    }

    public function test_only_an_active_candidate_account_can_be_added(): void
    {
        $admin = $this->admin();
        $group = $this->ownedGroup($admin);

        $this->actingAs($admin)->post('/manage/groups/'.$group->id.'/candidates', [
            'email' => 'sana@example.com',
        ])->assertSessionHasErrors('email');

        $this->actingAs($admin)->post('/manage/groups/'.$group->id.'/candidates', [
            'email' => 'missing@example.com',
        ])->assertSessionHasErrors('email');
    }

    public function test_an_admin_can_search_and_filter_candidates_in_the_group(): void
    {
        $admin = $this->admin();
        $group = $this->ownedGroup($admin);
        $other = $this->ownedGroup($admin, [
            'name' => 'Bankers',
            'description' => 'Bankers only',
        ]);

        $this->member('Hira', $group, [
            'gender' => 'male',
            'year_of_birth' => now()->year - 40,
        ]);
        $this->member('Sara', $group, [
            'gender' => 'female',
            'year_of_birth' => now()->year - 22,
        ], 'pending');
        $this->member('HiraOutside', $other, [
            'gender' => 'male',
            'display_name' => 'HiraOutside',
        ]);

        $this->actingAs($admin)
            ->get('/manage/groups/'.$group->id.'/candidates?display_name=Hir&gender=male&age_min=30')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GroupAdmin/Candidates/Index')
                ->has('candidates.data', 1)
                ->where('candidates.data.0.display_name', 'Hira')
                ->where('candidates.data.0.membership_status', 'active'));

        $this->actingAs($admin)
            ->get('/manage/groups/'.$group->id.'/candidates?membership_status=pending')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('candidates.data', 1)
                ->where('candidates.data.0.display_name', 'Sara'));

        $this->actingAs($admin)
            ->get('/manage/groups/'.$group->id.'/candidates?age_min=40&age_max=20')
            ->assertSessionHasErrors('age_max');
    }

    public function test_an_admin_can_view_a_profile_and_only_the_photos_the_group_allows(): void
    {
        $admin = $this->admin();
        $group = $this->ownedGroup($admin, [
            'male_pics_allowed' => true,
            'female_pics_allowed' => false,
        ]);
        $other = $this->admin('Hina');
        $closed = $this->ownedGroup($other, [
            'name' => 'Bankers',
            'description' => 'Bankers only',
        ]);

        $man = $this->member('Hira', $group, [
            'gender' => 'male',
            'real_name' => 'Hira Secret',
            'phone_number' => '03002222222',
        ]);
        $woman = $this->member('Sara', $group, [
            'gender' => 'female',
            'phone_number' => '03003333333',
        ]);

        $manPhoto = Candidate::query()->find($man->id)->photos()->create([
            'photo' => 'photos/hira.jpg',
            'photo_year' => 2024,
            'is_main' => true,
            'sort_order' => 1,
            'review_status' => 'approved',
        ]);
        Candidate::query()->find($woman->id)->photos()->create([
            'photo' => 'photos/sara-private.jpg',
            'photo_year' => 2023,
            'is_main' => true,
            'sort_order' => 1,
            'review_status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->get('/manage/groups/'.$group->id.'/candidates/'.$man->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('GroupAdmin/Candidates/Show')
                ->where('profile.display_name', 'Hira')
                ->missing('profile.real_name')
                ->missing('profile.phone_number')
                ->where('photos_allowed', true)
                ->has('photos', 1)
                ->where('photos.0.url', route('photos.show', ['photo' => $manPhoto->id, 'v' => 0], false)))
            ->assertDontSee('Hira Secret')
            ->assertDontSee('03002222222');

        $this->actingAs($admin)
            ->get('/manage/groups/'.$group->id.'/candidates/'.$woman->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('photos_allowed', false)
                ->has('photos', 0)
                ->where('photos_message', 'This group does not allow female photos.'))
            ->assertDontSee('photos/sara-private.jpg')
            ->assertDontSee('03003333333');

        $this->actingAs($other)
            ->get('/manage/groups/'.$closed->id.'/candidates/'.$man->id)
            ->assertNotFound();
    }

    public function test_notes_belong_to_one_group_and_can_be_added_then_edited(): void
    {
        $admin = $this->admin();
        $group = $this->ownedGroup($admin);
        $second = $this->ownedGroup($admin, [
            'name' => 'Bankers',
            'description' => 'Bankers only',
        ]);
        $candidate = $this->member('Hira', $group);
        $second->candidates()->attach($candidate->id, ['status' => 'active']);
        $colleague = $this->admin('Hina');
        $group->admins()->attach($colleague->id, [
            'is_primary' => false,
            'approval_date' => now(),
            'approved_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get('/manage/groups/'.$group->id.'/candidates/'.$candidate->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('membership.notes', null));

        $this->actingAs($admin)->put('/manage/groups/'.$group->id.'/candidates/'.$candidate->id.'/notes', [
            'notes' => 'Prefers Lahore.',
        ])->assertRedirect()->assertSessionHas('success', 'Notes added.');

        $this->actingAs($colleague)
            ->get('/manage/groups/'.$group->id.'/candidates/'.$candidate->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('membership.notes', 'Prefers Lahore.'));

        $this->actingAs($admin)
            ->get('/manage/groups/'.$second->id.'/candidates/'.$candidate->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('membership.notes', null));

        $this->actingAs($admin)->put('/manage/groups/'.$group->id.'/candidates/'.$candidate->id.'/notes', [
            'notes' => 'Prefers Karachi.',
        ])->assertSessionHas('success', 'Notes updated.');

        $this->assertDatabaseHas('candidate_group', [
            'group_id' => $group->id,
            'candidate_id' => $candidate->id,
            'notes' => 'Prefers Karachi.',
        ]);

        $this->actingAs($admin)->put('/manage/groups/'.$group->id.'/candidates/'.$candidate->id.'/notes', [
            'notes' => '   ',
        ])->assertSessionHasErrors('notes');
    }
}
