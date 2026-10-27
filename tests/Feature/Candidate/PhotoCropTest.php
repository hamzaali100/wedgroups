<?php

namespace Tests\Feature\Candidate;

use App\Models\Candidate;
use App\Models\CandidatePhoto;
use App\Models\Country;
use App\Models\Religion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PhotoCropTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        Country::query()->create([
            'id' => 'PAK',
            'name' => 'Pakistan',
            'nationality' => 'Pakistani',
        ]);

        Religion::query()->create([
            'id' => 1,
            'name' => 'Islam',
        ]);

        $this->user = User::factory()->create();
        Candidate::factory()->create([
            'user_id' => $this->user->id,
            'year_of_birth' => 1996,
        ]);
    }

    public function test_the_photo_page_asks_for_a_crop_before_saving(): void
    {
        $this->actingAs($this->user)
            ->get('/profile/photos')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Candidate/Profile/Photos')
                ->where('maxPhotos', 6)
                ->has('photos', 0));
    }

    public function test_an_upload_keeps_only_the_cropped_area(): void
    {
        $this->actingAs($this->user)
            ->post('/profile/photos', [
                'photo' => $this->splitImage(),
                'photo_year' => 2024,
                'blur' => '0',
                'crop_x' => 0,
                'crop_y' => 40,
                'crop_width' => 150,
                'crop_height' => 220,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $photo = CandidatePhoto::query()->first();
        $bytes = Storage::disk('local')->get($photo->photo);
        $info = getimagesizefromstring($bytes);

        $this->assertSame(150, $info[0]);
        $this->assertSame(220, $info[1]);
        $this->assertSame(IMAGETYPE_JPEG, $info[2]);
        $this->assertStringStartsWith("\xFF\xD8\xFF", $bytes);
        $this->assertStringNotContainsString('<?php', $bytes);

        [$red, $green, $blue] = $this->colorAt($bytes, 70, 100);
        $this->assertGreaterThan(180, $red);
        $this->assertLessThan(80, $blue);
        $this->assertLessThan(80, $green);
    }

    public function test_a_crop_outside_the_photo_is_rejected_and_nothing_is_stored(): void
    {
        $this->actingAs($this->user)
            ->post('/profile/photos', [
                'photo' => $this->splitImage(),
                'photo_year' => 2024,
                'crop_x' => 300,
                'crop_y' => 0,
                'crop_width' => 150,
                'crop_height' => 200,
            ])
            ->assertSessionHasErrors('photo');

        $this->assertDatabaseCount('candidate_photos', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_a_photo_cannot_be_saved_without_a_crop(): void
    {
        $this->actingAs($this->user)
            ->post('/profile/photos', [
                'photo' => $this->splitImage(),
                'photo_year' => 2024,
            ])
            ->assertSessionHasErrors(['crop_x', 'crop_y', 'crop_width', 'crop_height']);

        $this->assertDatabaseCount('candidate_photos', 0);
    }

    private function splitImage(): UploadedFile
    {
        $image = imagecreatetruecolor(400, 400);
        $red = imagecolorallocate($image, 220, 20, 20);
        $blue = imagecolorallocate($image, 20, 40, 220);
        imagefilledrectangle($image, 0, 0, 199, 399, $red);
        imagefilledrectangle($image, 200, 0, 399, 399, $blue);

        $path = tempnam(sys_get_temp_dir(), 'wgphoto');
        imagejpeg($image, $path, 95);
        imagedestroy($image);

        return new UploadedFile($path, 'portrait.jpg', 'image/jpeg', null, true);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function colorAt(string $bytes, int $x, int $y): array
    {
        $image = imagecreatefromstring($bytes);
        $color = imagecolorat($image, $x, $y);
        imagedestroy($image);

        return [($color >> 16) & 255, ($color >> 8) & 255, $color & 255];
    }
}
