<?php

namespace App\Support;

readonly class StoredCandidatePhoto
{
    public function __construct(
        public string $photo,
        public ?string $blurredPhoto,
    ) {}
}
