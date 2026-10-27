<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\FilterCandidatesRequest;
use App\Models\Candidate;
use App\Models\CandidatePhoto;
use App\Models\Country;
use App\Models\Religion;
use App\Services\CandidateDirectory;
use App\Support\CandidatePhotoAccess;
use App\Support\CandidatePhotoUrls;
use App\Support\CandidateProfileData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscoveryController extends Controller
{
    public function index(FilterCandidatesRequest $request, CandidateDirectory $directory): Response
    {
        $viewer = $request->user()->candidate;
        $filters = $request->validated();

        return Inertia::render('Candidate/Discovery/Index', [
            'candidates' => $directory->browse($viewer, $filters),
            'filters' => $filters,
            'hasGroup' => $directory->hasActiveGroup($viewer),
            'genders' => Candidate::GENDERS,
            'maritalStatuses' => Candidate::MARITAL_STATUSES,
            'educationLevels' => Candidate::EDUCATION_LEVELS,
            'religions' => Religion::query()->orderBy('name')->get(['id', 'name']),
            'countries' => Country::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Request $request, CandidateDirectory $directory, int $candidate): Response
    {
        $viewer = $request->user()->candidate;
        $profile = $directory->findViewable($viewer, $candidate);

        abort_if($profile === null, 404);

        $photos = null;

        if (CandidatePhotoAccess::sharedGroupAllowsPhotos($viewer, $profile)) {
            $photos = $profile->photos()
                ->where('review_status', 'approved')
                ->get()
                ->map(fn (CandidatePhoto $photo) => CandidatePhotoUrls::viewerCard($photo, (bool) $profile->photos_blurred))
                ->values();
        }

        return Inertia::render('Candidate/Discovery/Show', [
            'profile' => CandidateProfileData::publicView($profile),
            'photos' => $photos,
            'passed' => $directory->hasPassed($viewer, $profile),
            'liked' => $directory->hasLiked($viewer, $profile),
            'favorited' => $directory->hasFavorited($viewer, $profile),
        ]);
    }
}
