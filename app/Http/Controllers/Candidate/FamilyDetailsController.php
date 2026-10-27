<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\UpdateFamilyDetailsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FamilyDetailsController extends Controller
{
    public function edit(Request $request): Response
    {
        $candidate = $request->user()->candidate;

        return Inertia::render('Candidate/Profile/Family', [
            'details' => [
                'total_siblings' => $candidate->total_siblings,
                'siblings_description' => $candidate->siblings_description ?? '',
                'father_occupation' => $candidate->father_occupation ?? '',
                'mother_occupation' => $candidate->mother_occupation ?? '',
                'family_details' => $candidate->family_details ?? '',
                'parent_contact_number' => $candidate->parent_contact_number ?? '',
            ],
        ]);
    }

    public function update(UpdateFamilyDetailsRequest $request): RedirectResponse
    {
        $request->user()->candidate()->update($request->validated());

        return redirect()
            ->route('candidate.profile.show')
            ->with('success', 'Family details updated.');
    }
}
