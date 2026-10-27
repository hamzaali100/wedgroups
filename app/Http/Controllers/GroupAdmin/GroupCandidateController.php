<?php

namespace App\Http\Controllers\GroupAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupAdmin\AddGroupCandidateRequest;
use App\Http\Requests\GroupAdmin\FilterGroupCandidatesRequest;
use App\Http\Requests\GroupAdmin\UpdateGroupNotesRequest;
use App\Models\Candidate;
use App\Models\Group;
use App\Models\User;
use App\Services\GroupRoster;
use App\Models\CandidatePhoto;
use App\Support\CandidatePhotoApproval;
use App\Support\CandidatePhotoUrls;
use App\Support\CandidateProfileData;
use App\Support\ManagedGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GroupCandidateController extends Controller
{
    public function index(FilterGroupCandidatesRequest $request, GroupRoster $roster, int $group): Response
    {
        $managed = ManagedGroup::find($request->user(), $group);
        $filters = $request->validated();

        return Inertia::render('GroupAdmin/Candidates/Index', [
            'group' => [
                'id' => $managed->id,
                'name' => $managed->name,
            ],
            'candidates' => $roster->list($managed, $filters),
            'filters' => $filters,
            'genders' => Candidate::GENDERS,
            'maritalStatuses' => Candidate::MARITAL_STATUSES,
            'educationLevels' => Candidate::EDUCATION_LEVELS,
            'membershipStatuses' => Group::MEMBERSHIP_STATUSES,
        ]);
    }

    public function store(AddGroupCandidateRequest $request, int $group): RedirectResponse
    {
        $managed = ManagedGroup::find($request->user(), $group);
        $email = strtolower($request->validated('email'));

        $user = User::query()->whereRaw('lower(email) = ?', [$email])->first();

        if ($user === null || $user->role !== 'candidate' || $user->status !== 'active' || $user->candidate === null) {
            throw ValidationException::withMessages([
                'email' => 'No active candidate account uses that email.',
            ]);
        }

        if ($managed->candidates()->whereKey($user->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'This candidate is already in the group.',
            ]);
        }

        $managed->candidates()->attach($user->id, [
            'status' => 'pending',
        ]);

        return back()->with('success', 'Candidate added. Approve them when they should be visible in the group.');
    }

    public function show(Request $request, int $group, int $candidate): Response
    {
        $managed = ManagedGroup::find($request->user(), $group);
        $member = ManagedGroup::candidate($managed, $candidate);
        $member->load([
            'religion:id,name',
            'sect:id,name',
            'ethnicityRecord:id,name',
            'country:id,name',
            'state:id,name',
            'city:id,name',
            'grewUpIn:id,name',
            'occupation:id,name',
            'nationalities:id,name',
        ]);

        $photosAllowed = ($member->gender === 'male' && $managed->male_pics_allowed)
            || ($member->gender === 'female' && $managed->female_pics_allowed);

        $allPhotos = $member->photos()->with('approver:id,name')->get();
        $photos = $photosAllowed
            ? $allPhotos->where('review_status', 'approved')->map(
                fn (CandidatePhoto $photo) => CandidatePhotoUrls::viewerCard($photo, (bool) $member->photos_blurred)
            )->values()->all()
            : [];

        return Inertia::render('GroupAdmin/Candidates/Show', [
            'group' => [
                'id' => $managed->id,
                'name' => $managed->name,
            ],
            'profile' => CandidateProfileData::publicView($member),
            'membership' => [
                'status' => $member->pivot->status,
                'label' => Group::MEMBERSHIP_STATUSES[$member->pivot->status] ?? $member->pivot->status,
                'notes' => $member->pivot->notes,
            ],
            'photos_allowed' => $photosAllowed,
            'photos_message' => $this->photosMessage($member, $photosAllowed, $photos),
            'photos' => $photos,
            'review_photos' => $allPhotos->map(
                fn (CandidatePhoto $photo) => CandidatePhotoUrls::reviewCard($photo)
            )->values()->all(),
        ]);
    }

    public function destroy(Request $request, int $group, int $candidate): RedirectResponse
    {
        $managed = ManagedGroup::find($request->user(), $group);
        ManagedGroup::candidate($managed, $candidate);

        $managed->candidates()->detach($candidate);

        return redirect()
            ->route('group_admin.groups.candidates.index', $managed)
            ->with('success', 'Candidate removed from the group.');
    }

    public function approvePhoto(Request $request, int $group, int $candidate, int $photo): RedirectResponse
    {
        $managed = ManagedGroup::find($request->user(), $group);
        $member = ManagedGroup::candidate($managed, $candidate);
        $record = $member->photos()->whereKey($photo)->first();

        abort_if($record === null, 404);

        $recorded = CandidatePhotoApproval::approve($record, $request->user());

        return back()->with('success', $recorded ? 'Photo approved.' : 'This photo is already approved.');
    }

    public function approve(Request $request, int $group, int $candidate): RedirectResponse
    {
        return $this->changeMembership($request, $group, $candidate, 'pending', [
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ], 'Candidate approved for the group.', 'Only a pending candidate can be approved.');
    }

    public function suspend(Request $request, int $group, int $candidate): RedirectResponse
    {
        return $this->changeMembership($request, $group, $candidate, 'active', [
            'status' => 'inactive',
        ], 'Candidate suspended in this group.', 'Only an active candidate can be suspended.');
    }

    public function reactivate(Request $request, int $group, int $candidate): RedirectResponse
    {
        return $this->changeMembership($request, $group, $candidate, 'inactive', [
            'status' => 'active',
        ], 'Candidate reactivated in this group.', 'Only a suspended candidate can be reactivated.');
    }

    public function updateNotes(UpdateGroupNotesRequest $request, int $group, int $candidate): RedirectResponse
    {
        $managed = ManagedGroup::find($request->user(), $group);
        $member = ManagedGroup::candidate($managed, $candidate);
        $existed = filled($member->pivot->notes);

        $managed->candidates()->updateExistingPivot($candidate, [
            'notes' => $request->validated('notes'),
        ]);

        return back()->with('success', $existed ? 'Notes updated.' : 'Notes added.');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function changeMembership(Request $request, int $group, int $candidate, string $from, array $attributes, string $success, string $failure): RedirectResponse
    {
        $managed = ManagedGroup::find($request->user(), $group);
        $member = ManagedGroup::candidate($managed, $candidate);

        if ($member->pivot->status !== $from) {
            throw ValidationException::withMessages([
                'membership' => $failure,
            ]);
        }

        $managed->candidates()->updateExistingPivot($candidate, $attributes);

        return back()->with('success', $success);
    }

    /**
     * @param  array<int, mixed>  $photos
     */
    private function photosMessage(Candidate $candidate, bool $photosAllowed, array $photos): ?string
    {
        if (! $photosAllowed) {
            $gender = $candidate->gender === 'male' ? 'male' : 'female';

            return 'This group does not allow '.$gender.' photos.';
        }

        if ($photos === []) {
            return 'This candidate has not added photos.';
        }

        return null;
    }
}
