<?php

namespace App\Http\Requests\Candidate;

class StorePhotoRequest extends CandidateFormRequest
{
    use ValidatesCandidatePhotoUpload;
}
