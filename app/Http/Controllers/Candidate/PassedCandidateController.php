<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Services\CandidateDirectory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PassedCandidateController extends Controller
{
    public function index(Request $request, CandidateDirectory $directory): Response
    {
        return Inertia::render('Candidate/Passed/Index', [
            'passed' => $directory->passed($request->user()->candidate),
        ]);
    }

    public function store(Request $request, CandidateDirectory $directory, int $candidate): RedirectResponse
    {
        $directory->pass($request->user()->candidate, $candidate);

        return back()->with('success', 'Candidate passed. They are hidden from your browse list.');
    }

    public function destroy(Request $request, CandidateDirectory $directory, int $candidate): RedirectResponse
    {
        $directory->undoPass($request->user()->candidate, $candidate);

        return back()->with('success', 'Pass removed.');
    }
}
