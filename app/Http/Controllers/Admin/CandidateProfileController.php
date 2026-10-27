<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddCandidateToGroupRequest;
use App\Http\Requests\Admin\RejectCandidateProfileRequest;
use App\Http\Requests\Admin\UpdateCandidateProfileRequest;
use App\Models\Candidate;
use App\Models\CandidatePhoto;
use App\Models\Country;
use App\Models\Group;
use App\Models\Religion;
use App\Models\User;
use App\Support\CandidateProfileData;
use App\Support\CandidateProfileDeletion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CandidateProfileController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const RELATIONS = [
        'user:id,name,email,status',
        'religion:id,name',
        'sect:id,name',
        'ethnicityRecord:id,name',
        'country:id,name',
        'state:id,name',
        'city:id,name',
        'grewUpIn:id,name',
        'occupation:id,name',
        'nationalities:id,name',
        'groups',
    ];

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'review_status' => ['nullable', Rule::in(array_keys(Candidate::REVIEW_STATUSES))],
        ]);

        $candidates = Candidate::query()
            ->with('user:id,email,status')
            ->when($filters['review_status'] ?? null, fn ($query, $status) => $query->where('review_status', $status))
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $like = '%'.addcslashes(trim($search), '%_\\').'%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('display_name', 'like', $like)
                        ->orWhere('real_name', 'like', $like)
                        ->orWhereHas('user', fn ($users) => $users->where('email', 'like', $like));
                });
            })
            ->orderBy('display_name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Candidate $candidate) => [
                'id' => $candidate->user_id,
                'display_name' => $candidate->display_name,
                'real_name' => $candidate->real_name,
                'email' => $candidate->user?->email,
                'review_label' => Candidate::REVIEW_STATUSES[$candidate->review_status] ?? $candidate->review_status,
                'profile_status_label' => Candidate::PROFILE_STATUSES[$candidate->profile_status] ?? $candidate->profile_status,
                'account_status_label' => User::STATUSES[$candidate->user?->status] ?? $candidate->user?->status,
            ]);

        return Inertia::render('Admin/Candidates/Index', [
            'candidates' => $candidates,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'review_status' => $filters['review_status'] ?? '',
            ],
            'reviewStatuses' => Candidate::REVIEW_STATUSES,
        ]);
    }

    public function show(Candidate $candidate): Response
    {
        $candidate->load([...self::RELATIONS, 'photos']);

        return Inertia::render('Admin/Candidates/Show', [
            'candidateId' => $candidate->user_id,
            'account' => [
                'email' => $candidate->user?->email,
                'account_status' => User::STATUSES[$candidate->user?->status] ?? $candidate->user?->status,
                'review_status' => $candidate->review_status,
                'review_label' => Candidate::REVIEW_STATUSES[$candidate->review_status] ?? $candidate->review_status,
                'rejection_reason' => $candidate->rejection_reason,
            ],
            'profile' => CandidateProfileData::owner($candidate),
            'photos' => $candidate->photos->map(fn (CandidatePhoto $photo) => [
                'id' => $photo->id,
                'url' => $this->photoUrl($photo->photo),
                'year' => $photo->photo_year,
                'is_main' => $photo->is_main,
                'review_status' => $photo->review_status,
                'review_label' => CandidatePhoto::REVIEW_STATUSES[$photo->review_status] ?? $photo->review_status,
            ]),
            'groups' => $candidate->groups->map(fn (Group $group) => [
                'id' => $group->id,
                'name' => $group->name,
                'status_label' => Group::STATUSES[$group->status] ?? $group->status,
                'membership_label' => Group::MEMBERSHIP_STATUSES[$group->pivot->status] ?? $group->pivot->status,
            ]),
            'available_groups' => Group::query()
                ->whereNotIn('id', $candidate->groups->pluck('id'))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Group $group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                ]),
        ]);
    }

    public function edit(Candidate $candidate): Response
    {
        return Inertia::render('Admin/Candidates/Edit', [
            'candidate' => [
                'id' => $candidate->user_id,
                'display_name' => $candidate->display_name,
                'real_name' => $candidate->real_name,
                'year_of_birth' => $candidate->year_of_birth,
                'month_of_birth' => $candidate->month_of_birth,
                'gender' => $candidate->gender,
                'height' => $candidate->height,
                'marital_status' => $candidate->marital_status,
                'religion_id' => $candidate->religion_id,
                'country_id' => $candidate->country_id,
                'education_level' => $candidate->education_level,
                'universities_names' => $candidate->universities_names ?? '',
                'schools_names' => $candidate->schools_names ?? '',
                'monthly_income' => $candidate->monthly_income ?? '',
                'career_details' => $candidate->career_details ?? '',
                'total_siblings' => $candidate->total_siblings,
                'siblings_description' => $candidate->siblings_description ?? '',
                'father_occupation' => $candidate->father_occupation ?? '',
                'mother_occupation' => $candidate->mother_occupation ?? '',
                'family_details' => $candidate->family_details ?? '',
                'parent_contact_number' => $candidate->parent_contact_number ?? '',
                'phone_number' => $candidate->phone_number ?? '',
                'other_details' => $candidate->other_details ?? '',
                'disabilities' => $candidate->disabilities ?? '',
                'partner_requirement_details' => $candidate->partner_requirement_details ?? '',
                'profile_visibility' => $candidate->profile_visibility,
                'profile_status' => $candidate->profile_status,
            ],
            'religions' => Religion::query()->orderBy('name')->get(['id', 'name']),
            'countries' => Country::query()->orderBy('name')->get(['id', 'name']),
            'genders' => Candidate::GENDERS,
            'maritalStatuses' => Candidate::MARITAL_STATUSES,
            'educationLevels' => Candidate::EDUCATION_LEVELS,
            'profileStatuses' => Candidate::PROFILE_STATUSES,
            'profileVisibilities' => Candidate::PROFILE_VISIBILITIES,
        ]);
    }

    public function update(UpdateCandidateProfileRequest $request, Candidate $candidate): RedirectResponse
    {
        $candidate->update($request->validated());

        return redirect()
            ->route('admin.candidates.show', $candidate)
            ->with('success', 'Candidate profile updated.');
    }

    public function approve(Candidate $candidate): RedirectResponse
    {
        $candidate->update([
            'review_status' => 'approved',
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Candidate profile approved.');
    }

    public function reject(RejectCandidateProfileRequest $request, Candidate $candidate): RedirectResponse
    {
        $candidate->update([
            'review_status' => 'rejected',
            'rejection_reason' => $request->validated('rejection_reason'),
        ]);

        return back()->with('success', 'Candidate profile rejected.');
    }

    public function suspend(Candidate $candidate): RedirectResponse
    {
        if (! in_array($candidate->review_status, ['pending', 'approved'], true)) {
            throw ValidationException::withMessages([
                'review_status' => 'Only a waiting or approved profile can be suspended.',
            ]);
        }

        $candidate->update(['review_status' => 'suspended']);

        return back()->with('success', 'Candidate profile suspended.');
    }

    public function reactivate(Candidate $candidate): RedirectResponse
    {
        if ($candidate->review_status !== 'suspended') {
            throw ValidationException::withMessages([
                'review_status' => 'Only a suspended profile can be reactivated.',
            ]);
        }

        $candidate->update([
            'review_status' => 'approved',
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Candidate profile reactivated.');
    }

    public function destroy(Candidate $candidate): RedirectResponse
    {
        CandidateProfileDeletion::delete($candidate);

        return redirect()
            ->route('admin.candidates.index')
            ->with('success', 'Candidate profile deleted. The user account remains.');
    }

    public function approvePhoto(Candidate $candidate, CandidatePhoto $photo): RedirectResponse
    {
        abort_unless($photo->candidate_id === $candidate->user_id, 404);

        $photo->update(['review_status' => 'approved']);

        return back()->with('success', 'Photo approved.');
    }

    public function addGroup(AddCandidateToGroupRequest $request, Candidate $candidate): RedirectResponse
    {
        $this->guardActiveCandidate($candidate);

        $groupId = (int) $request->validated('group_id');

        if ($candidate->groups()->whereKey($groupId)->exists()) {
            throw ValidationException::withMessages([
                'group_id' => 'This candidate is already in that group.',
            ]);
        }

        $status = $request->validated('status');

        $candidate->groups()->attach($groupId, [
            'status' => $status,
            'approved_at' => $status === 'active' ? now() : null,
            'approved_by' => null,
        ]);

        return back()->with('success', 'Candidate added to the group.');
    }

    public function removeGroup(Candidate $candidate, Group $group): RedirectResponse
    {
        abort_unless($candidate->groups()->whereKey($group->id)->exists(), 404);

        $candidate->groups()->detach($group->id);

        return back()->with('success', 'Candidate removed from the group.');
    }

    private function guardActiveCandidate(Candidate $candidate): void
    {
        $user = $candidate->user;

        if ($user === null || $user->role !== 'candidate' || $user->status !== 'active') {
            throw ValidationException::withMessages([
                'group_id' => 'Only an active candidate account can be added to a group.',
            ]);
        }
    }

    private function photoUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return '/'.ltrim($path, '/');
    }
}
