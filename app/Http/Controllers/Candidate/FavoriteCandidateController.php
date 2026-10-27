<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Services\CandidateDirectory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FavoriteCandidateController extends Controller
{
    public function index(Request $request, CandidateDirectory $directory): Response
    {
        return Inertia::render('Candidate/Favorites/Index', [
            'favorites' => $directory->favorited($request->user()->candidate),
        ]);
    }

    public function store(Request $request, CandidateDirectory $directory, int $candidate): RedirectResponse
    {
        $directory->favorite($request->user()->candidate, $candidate);

        return back()->with('success', 'Candidate added to favorites.');
    }

    public function destroy(Request $request, CandidateDirectory $directory, int $candidate): RedirectResponse
    {
        $directory->unfavorite($request->user()->candidate, $candidate);

        return back()->with('success', 'Candidate removed from favorites.');
    }
}
