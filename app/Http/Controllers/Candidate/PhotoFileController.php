<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\CandidatePhoto;
use App\Services\CandidatePhotoProcessor;
use App\Support\CandidatePhotoAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PhotoFileController extends Controller
{
    public function show(Request $request, CandidatePhotoProcessor $photos, CandidatePhoto $photo): Response
    {
        $variant = CandidatePhotoAccess::variant($request->user(), $photo, $request->query('variant'));

        abort_if($variant === null, 404);

        return $photos->inlineResponse($photo, $variant);
    }
}
