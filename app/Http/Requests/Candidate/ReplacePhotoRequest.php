<?php

namespace App\Http\Requests\Candidate;

class ReplacePhotoRequest extends CandidateFormRequest
{
    use ValidatesCandidatePhotoUpload;
}
