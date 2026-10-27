<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\UpdateCareerDetailsRequest;
use App\Models\Occupation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CareerDetailsController extends Controller
{
    public function edit(Request $request): Response
    {
        $candidate = $request->user()->candidate;

        return Inertia::render('Candidate/Profile/Career', [
            'details' => [
                'occupation_id' => $candidate->occupation_id ?? '',
                'monthly_income' => $candidate->monthly_income ?? '',
                'career_details' => $candidate->career_details ?? '',
                'linkedin' => $candidate->linkedin ?? '',
            ],
            'occupations' => Occupation::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateCareerDetailsRequest $request): RedirectResponse
    {
        $request->user()->candidate()->update($request->validated());

        return redirect()
            ->route('candidate.profile.show')
            ->with('success', 'Career details updated.');
    }
}
