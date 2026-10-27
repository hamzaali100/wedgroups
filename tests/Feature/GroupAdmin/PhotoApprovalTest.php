<?php

namespace Tests\Feature\GroupAdmin;

use App\Models\CandidatePhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PhotoApprovalTest extends TestCase
{
    use InteractsWithGroupAdmins;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seedReferences();
    }

    public function test_a_group_admin_of_the_candidates_group_can_approve_a_photo_and_the_approval_is_recorded(): void
    {
        $admin = $this->admin();
        $group = $this->ownedGroup($admin, ['male_pics_allowed' => true]);
        $candidate = $this->member('Hira', $group, ['gender' => 'male']);
        $photo = $this->pendingPhoto($candidate);

        $this->actingAs($admin)
            ->get('/photos/'.$photo->id.'?variant=clear')
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');

        $this->actingAs($admin)
            ->get('/manage/groups/'.$group->id.'/candidates/'.$candidate->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('photos', 0)
                ->has('review_photos', 1)
                ->where('review_photos.0.review_status', 'pending')
                ->where('review_photos.0.approved_by', null));

        $this->actingAs($admin)
            ->post('/manage/groups/'.$group->id.'/candidates/'.$candidate->id.'/photos/'.$photo->id.'/approve')
            ->assertRedirect()
            ->assertSessionHas('success', 'Photo approved.');

        $photo->refresh();
        $this->assertSame('approved', $photo->review_status);
        $this->assertSame($admin->id, $photo->approved_by);
        $this->assertNotNull($photo->approved_at);

        $this->actingAs($admin)
            ->get('/manage/groups/'.$group->id.'/candidates/'.$candidate->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('photos', 1)
                ->where('review_photos.0.approved_by', 'Sana')
                ->where('review_photos.0.approved_at', $photo->approved_at->timezone(config('app.timezone'))->format('j M Y, H:i')));

        $platform = \App\Models\User::factory()->create(['role' => 'admin', 'name' => 'Platform']);
        $this->travel(1)->days();
        $this->actingAs($platform)
            ->post('/admin/candidates/'.$candidate->id.'/photos/'.$photo->id.'/approve')
            ->assertRedirect()
            ->assertSessionHas('success', 'This photo is already approved.');

        $this->assertSame($admin->id, $photo->fresh()->approved_by);
    }

    public function test_a_group_admin_outside_the_candidates_groups_cannot_approve_or_open_the_photo(): void
    {
        $admin = $this->admin();
        $group = $this->ownedGroup($admin);
        $candidate = $this->member('Hira', $group, ['gender' => 'male']);
        $photo = $this->pendingPhoto($candidate);

        $outsider = $this->admin('Hina');
        $this->ownedGroup($outsider, [
            'name' => 'Bankers',
            'description' => 'Bankers only',
        ]);

        $this->actingAs($outsider)
            ->post('/manage/groups/'.$group->id.'/candidates/'.$candidate->id.'/photos/'.$photo->id.'/approve')
            ->assertNotFound();

        $this->actingAs($outsider)
            ->get('/photos/'.$photo->id)
            ->assertNotFound();

        $this->assertSame('pending', $photo->fresh()->review_status);
        $this->assertNull($photo->fresh()->approved_by);
    }

    public function test_replacing_a_photo_clears_the_recorded_approval(): void
    {
        $admin = $this->admin();
        $group = $this->ownedGroup($admin);
        $candidate = $this->member('Hira', $group, ['gender' => 'male', 'year_of_birth' => 1996]);
        $photo = $this->pendingPhoto($candidate);

        $this->actingAs($admin)
            ->post('/manage/groups/'.$group->id.'/candidates/'.$candidate->id.'/photos/'.$photo->id.'/approve')
            ->assertRedirect();

        $this->actingAs($candidate)
            ->post('/profile/photos/'.$photo->id.'/replace', [
                'photo' => $this->image(),
                'photo_year' => 2024,
                'crop_x' => 0,
                'crop_y' => 0,
                'crop_width' => 120,
                'crop_height' => 160,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $photo->refresh();
        $this->assertSame('pending', $photo->review_status);
        $this->assertNull($photo->approved_by);
        $this->assertNull($photo->approved_at);
    }

    private function pendingPhoto(\App\Models\User $candidate): CandidatePhoto
    {
        $stored = app(\App\Services\CandidatePhotoProcessor::class)->store(
            $this->image(),
            $candidate->id,
            false,
            ['x' => 0, 'y' => 0, 'width' => 120, 'height' => 160],
        );

        return CandidatePhoto::query()->create([
            'candidate_id' => $candidate->id,
            'photo' => $stored->photo,
            'photo_year' => 2024,
            'is_main' => true,
            'sort_order' => 1,
            'review_status' => 'pending',
        ]);
    }

    private function image(): UploadedFile
    {
        $image = imagecreatetruecolor(160, 200);
        $color = imagecolorallocate($image, 40, 90, 140);
        imagefilledrectangle($image, 0, 0, 159, 199, $color);
        $path = tempnam(sys_get_temp_dir(), 'wgphoto');
        imagejpeg($image, $path, 90);
        imagedestroy($image);

        return new UploadedFile($path, 'portrait.jpg', 'image/jpeg', null, true);
    }
}
