<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\UpdatePartnerRequirementsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartnerRequirementsController extends Controller
{
    public function edit(Request $request): Response
    {
        $candidate = $request->user()->candidate;

        return Inertia::render('Candidate/Profile/Partner', [
            'details' => [
                'partner_age_min' => $candidate->partner_age_min ?? '',
                'partner_age_max' => $candidate->partner_age_max ?? '',
                'partner_height_min' => $candidate->partner_height_min ?? '',
                'partner_height_max' => $candidate->partner_height_max ?? '',
                'partner_requirement_details' => $candidate->partner_requirement_details ?? '',
            ],
        ]);
    }

    public function update(UpdatePartnerRequirementsRequest $request): RedirectResponse
    {
        $request->user()->candidate()->update($request->validated());

        return redirect()
            ->route('candidate.profile.show')
            ->with('success', 'Partner requirements updated.');
    }
}
