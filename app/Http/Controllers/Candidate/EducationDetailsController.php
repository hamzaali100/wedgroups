<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\UpdateEducationDetailsRequest;
use App\Models\Candidate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EducationDetailsController extends Controller
{
    public function edit(Request $request): Response
    {
        $candidate = $request->user()->candidate;

        return Inertia::render('Candidate/Profile/Education', [
            'details' => [
                'education_level' => $candidate->education_level,
                'universities_names' => $candidate->universities_names ?? '',
                'schools_names' => $candidate->schools_names ?? '',
            ],
            'educationLevels' => Candidate::EDUCATION_LEVELS,
        ]);
    }

    public function update(UpdateEducationDetailsRequest $request): RedirectResponse
    {
        $request->user()->candidate()->update($request->validated());

        return redirect()
            ->route('candidate.profile.show')
            ->with('success', 'Education details updated.');
    }
}
