<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\StoreCandidateProfileRequest;
use App\Models\Candidate;
use App\Models\CandidatePhoto;
use App\Models\Country;
use App\Models\Religion;
use App\Support\CandidatePhotoUrls;
use App\Support\CandidateProfileData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const RELATIONS = [
        'religion:id,name',
        'sect:id,name',
        'ethnicityRecord:id,name',
        'country:id,name',
        'state:id,name',
        'city:id,name',
        'grewUpIn:id,name',
        'occupation:id,name',
        'nationalities:id,name',
    ];

    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->user()->candidate()->exists()) {
            return redirect()->route('candidate.profile.show');
        }

        return Inertia::render('Candidate/Profile/Create', [
            'countries' => Country::query()->orderBy('name')->get(['id', 'name']),
            'religions' => Religion::query()->orderBy('name')->get(['id', 'name']),
            'genders' => Candidate::GENDERS,
            'maritalStatuses' => Candidate::MARITAL_STATUSES,
            'educationLevels' => Candidate::EDUCATION_LEVELS,
        ]);
    }

    public function store(StoreCandidateProfileRequest $request): RedirectResponse
    {
        $request->user()->candidate()->create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
            'review_status' => 'pending',
        ]);

        return redirect()
            ->route('candidate.profile.show')
            ->with('success', 'Your profile has been created and is waiting for approval.');
    }

    public function show(Request $request): Response|RedirectResponse
    {
        $candidate = $request->user()->candidate()->with([...self::RELATIONS, 'photos.approver:id,name'])->first();

        if ($candidate === null) {
            return redirect()->route('candidate.profile.create');
        }

        return Inertia::render('Candidate/Profile/Show', [
            'profile' => CandidateProfileData::owner($candidate),
            'profileStatuses' => Candidate::PROFILE_STATUSES,
            'profileVisibilities' => Candidate::PROFILE_VISIBILITIES,
            'photos' => $candidate->photos->map(fn (CandidatePhoto $photo) => CandidatePhotoUrls::ownerCard($photo))->values(),
            'photosBlurred' => $candidate->photos_blurred,
        ]);
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'profile_status' => ['required', Rule::in(array_keys(Candidate::PROFILE_STATUSES))],
        ]);

        $request->user()->candidate()->update($data);

        return back()->with('success', 'Profile status updated.');
    }

    public function updateVisibility(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'profile_visibility' => ['required', Rule::in(array_keys(Candidate::PROFILE_VISIBILITIES))],
        ]);

        $request->user()->candidate()->update($data);

        return back()->with('success', 'Profile visibility updated.');
    }
}
