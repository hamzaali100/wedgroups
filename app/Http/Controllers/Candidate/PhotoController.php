<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\ReorderPhotosRequest;
use App\Http\Requests\Candidate\ReplacePhotoRequest;
use App\Http\Requests\Candidate\StorePhotoRequest;
use App\Http\Requests\Candidate\UpdatePhotoBlurRequest;
use App\Models\Candidate;
use App\Models\CandidatePhoto;
use App\Services\CandidatePhotoProcessor;
use App\Support\CandidatePhotoUrls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PhotoController extends Controller
{
    public function index(Request $request): Response
    {
        $candidate = $request->user()->candidate;

        return Inertia::render('Candidate/Profile/Photos', $this->page($candidate));
    }

    public function store(StorePhotoRequest $request, CandidatePhotoProcessor $photos): RedirectResponse
    {
        $candidate = $request->user()->candidate;
        $stored = $photos->store(
            $request->file('photo'),
            $candidate->user_id,
            $request->boolean('blur') || $candidate->photos_blurred,
        );

        try {
            DB::transaction(function () use ($request, $candidate, $stored): void {
                $locked = Candidate::query()->whereKey($candidate->user_id)->lockForUpdate()->first();
                $existing = $locked->photos()->count();

                if ($existing >= CandidatePhotoProcessor::MAX_PHOTOS) {
                    throw ValidationException::withMessages([
                        'photo' => 'You can add up to '.CandidatePhotoProcessor::MAX_PHOTOS.' photos.',
                    ]);
                }

                $locked->photos()->create([
                    'photo' => $stored->photo,
                    'blurred_photo' => $stored->blurredPhoto,
                    'photo_year' => $request->integer('photo_year'),
                    'is_main' => $existing === 0,
                    'sort_order' => ((int) $locked->photos()->max('sort_order')) + 1,
                    'review_status' => 'pending',
                ]);
            });
        } catch (\Throwable $exception) {
            $photos->deletePaths($candidate->user_id, [$stored->photo, $stored->blurredPhoto]);
            throw $exception;
        }

        return back()->with('success', 'Photo uploaded. It stays hidden until an admin approves it.');
    }

    public function update(ReplacePhotoRequest $request, CandidatePhotoProcessor $photos, int $photo): RedirectResponse
    {
        $candidate = $request->user()->candidate;
        $record = $this->owned($candidate, $photo);
        $previous = [$record->photo, $record->blurred_photo];
        $stored = $photos->store(
            $request->file('photo'),
            $candidate->user_id,
            $request->boolean('blur') || $candidate->photos_blurred,
        );

        try {
            $record->update([
                'photo' => $stored->photo,
                'blurred_photo' => $stored->blurredPhoto,
                'photo_year' => $request->integer('photo_year'),
                'review_status' => 'pending',
            ]);
        } catch (\Throwable $exception) {
            $photos->deletePaths($candidate->user_id, [$stored->photo, $stored->blurredPhoto]);
            throw $exception;
        }

        $photos->deletePaths($candidate->user_id, $previous);

        return back()->with('success', 'Photo replaced. It stays hidden until an admin approves it.');
    }

    public function destroy(Request $request, CandidatePhotoProcessor $photos, int $photo): RedirectResponse
    {
        $candidate = $request->user()->candidate;
        $record = $this->owned($candidate, $photo);
        $wasMain = $record->is_main;

        $photos->deletePaths($candidate->user_id, [$record->photo, $record->blurred_photo]);
        $record->delete();

        if ($wasMain) {
            $candidate->photos()->orderBy('sort_order')->first()?->update(['is_main' => true]);
        }

        return back()->with('success', 'Photo deleted.');
    }

    public function main(Request $request, int $photo): RedirectResponse
    {
        $candidate = $request->user()->candidate;
        $record = $this->owned($candidate, $photo);

        DB::transaction(function () use ($candidate, $record): void {
            $candidate->photos()->update(['is_main' => false]);
            $record->update(['is_main' => true]);
        });

        return back()->with('success', 'Main photo updated.');
    }

    public function reorder(ReorderPhotosRequest $request): RedirectResponse
    {
        $candidate = $request->user()->candidate;
        $order = array_map('intval', $request->validated('order'));

        DB::transaction(function () use ($candidate, $order): void {
            $photos = $candidate->photos()->lockForUpdate()->get()->keyBy('id');
            $owned = $photos->keys()->map(fn ($id) => (int) $id)->sort()->values()->all();
            $given = collect($order)->sort()->values()->all();

            if ($owned !== $given) {
                throw ValidationException::withMessages([
                    'order' => 'Choose an order that includes each of your photos once.',
                ]);
            }

            foreach ($order as $index => $id) {
                $photos[$id]->update(['sort_order' => 200 + $index]);
            }

            foreach ($order as $index => $id) {
                $photos[$id]->update(['sort_order' => $index + 1]);
            }
        });

        return back()->with('success', 'Photo order updated.');
    }

    public function updateBlur(UpdatePhotoBlurRequest $request, CandidatePhotoProcessor $photos): RedirectResponse
    {
        $candidate = $request->user()->candidate;
        $blurred = $request->boolean('photos_blurred');

        if ($blurred) {
            foreach ($candidate->photos()->get() as $photo) {
                if ($photo->blurred_photo !== null) {
                    continue;
                }

                $path = $photos->blurStored($photo);

                if ($path === null) {
                    throw ValidationException::withMessages([
                        'photos_blurred' => 'A saved photo could not be blurred. Replace that photo and try again.',
                    ]);
                }

                $photo->update(['blurred_photo' => $path]);
            }
        }

        $candidate->update(['photos_blurred' => $blurred]);

        return back()->with('success', $blurred
            ? 'Other people will see the blurred photos.'
            : 'Other people will see the clear photos.');
    }

    /**
     * @return array<string, mixed>
     */
    private function page(Candidate $candidate): array
    {
        $candidate->load('photos');

        return [
            'photos' => $candidate->photos->map(fn (CandidatePhoto $photo) => CandidatePhotoUrls::ownerCard($photo))->values(),
            'photosBlurred' => $candidate->photos_blurred,
            'maxPhotos' => CandidatePhotoProcessor::MAX_PHOTOS,
            'minYear' => $candidate->year_of_birth,
            'maxYear' => (int) now()->year,
        ];
    }

    private function owned(Candidate $candidate, int $photo): CandidatePhoto
    {
        $record = $candidate->photos()->whereKey($photo)->first();

        abort_if($record === null, 404);

        return $record;
    }
}
