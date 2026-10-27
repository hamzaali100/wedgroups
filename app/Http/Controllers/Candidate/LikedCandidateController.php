<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Services\CandidateDirectory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LikedCandidateController extends Controller
{
    public function index(Request $request, CandidateDirectory $directory): Response
    {
        return Inertia::render('Candidate/Likes/Index', [
            'liked' => $directory->liked($request->user()->candidate),
        ]);
    }

    public function store(Request $request, CandidateDirectory $directory, int $candidate): RedirectResponse
    {
        $directory->like($request->user()->candidate, $candidate);

        return back()->with('success', 'Candidate liked.');
    }

    public function destroy(Request $request, CandidateDirectory $directory, int $candidate): RedirectResponse
    {
        $directory->unlike($request->user()->candidate, $candidate);

        return back()->with('success', 'Like removed.');
    }
}
