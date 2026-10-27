<?php

namespace App\Services;

use App\Models\CandidatePhoto;
use App\Support\StoredCandidatePhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Stores a small JPEG and, when asked, a blurred copy.
 * The upload is fully decoded and the original bytes are discarded, so scripts,
 * metadata, and anything appended to the file are not kept.
 */
class CandidatePhotoProcessor
{
    public const MAX_PHOTOS = 6;

    public const MAX_UPLOAD_BYTES = 8 * 1024 * 1024;

    public const MAX_EDGE = 1600;

    public const MAX_DIMENSION = 8000;

    public const MAX_PIXELS = 20_000_000;

    public const MIN_EDGE = 100;

    public const CLEAR_MAX_BYTES = 750_000;

    public const BLUR_MAX_BYTES = 140_000;

    /**
     * @var array<int, string>
     */
    private const DANGEROUS_EXTENSIONS = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8',
        'exe', 'dll', 'js', 'mjs', 'html', 'htm', 'svg', 'shtml', 'xhtml',
        'cgi', 'htaccess', 'asp', 'aspx', 'bat', 'cmd', 'com', 'scr', 'msi', 'jar', 'vbs', 'ps1',
    ];

    public function store(UploadedFile $file, int $candidateId, bool $withBlur): StoredCandidatePhoto
    {
        $clearBytes = $this->sanitize($file);
        $name = (string) Str::uuid();
        $clearPath = 'candidate-photos/'.$candidateId.'/'.$name.'.jpg';
        $disk = Storage::disk('local');
        $disk->put($clearPath, $clearBytes);

        if (! $withBlur) {
            return new StoredCandidatePhoto($clearPath, null);
        }

        $blurredPath = 'candidate-photos/'.$candidateId.'/'.$name.'-blurred.jpg';

        try {
            $disk->put($blurredPath, $this->blur($clearBytes));
        } catch (\Throwable $exception) {
            $disk->delete($clearPath);
            throw $exception;
        }

        return new StoredCandidatePhoto($clearPath, $blurredPath);
    }

    public function blurStored(CandidatePhoto $photo): ?string
    {
        $clearPath = $this->ownedPath($photo->candidate_id, $photo->photo);

        if ($clearPath === null) {
            return null;
        }

        $bytes = $this->readOwned($photo->candidate_id, $clearPath);

        if ($bytes === null) {
            return null;
        }

        $blurredPath = substr($clearPath, 0, -4).'-blurred.jpg';

        if ($this->ownedPath($photo->candidate_id, $blurredPath) === null) {
            return null;
        }

        Storage::disk('local')->put($blurredPath, $this->blur($bytes));

        return $blurredPath;
    }

    /**
     * @param  array<int, ?string>  $paths
     */
    public function deletePaths(int $candidateId, array $paths): void
    {
        $disk = Storage::disk('local');

        foreach ($paths as $path) {
            $safe = $this->ownedPath($candidateId, $path);

            if ($safe !== null && $disk->exists($safe)) {
                $disk->delete($safe);
            }
        }
    }

    public function inlineResponse(CandidatePhoto $photo, string $variant): \Illuminate\Http\Response
    {
        $path = $variant === 'blurred' ? $photo->blurred_photo : $photo->photo;
        $bytes = is_string($path) ? $this->readOwned($photo->candidate_id, $path) : null;

        abort_if($bytes === null, 404);

        return response($bytes, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Length' => (string) strlen($bytes),
            'Content-Disposition' => 'inline; filename="photo.jpg"',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'",
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function ownedPath(int $candidateId, ?string $path): ?string
    {
        if (! is_string($path) || $candidateId < 1) {
            return null;
        }

        $pattern = '#^candidate-photos/'.$candidateId.'/[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}(-blurred)?\.jpg$#';

        if (preg_match($pattern, $path) !== 1) {
            return null;
        }

        return $path;
    }

    private function sanitize(UploadedFile $file): string
    {
        $this->assertSafeName($file);
        $path = $file->getRealPath() ?: $file->getPathname();
        $this->assertReadableImage($path);

        $image = $this->decode($path);

        try {
            $image = $this->orient($image, $path);
            $this->assertDecodedBounds($image);
            $image = $this->flatten($image);
            $image = $this->fit($image, self::MAX_EDGE);

            return $this->jpegBytes($image, self::CLEAR_MAX_BYTES);
        } finally {
            $this->destroy($image);
        }
    }

    private function assertSafeName(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            $this->fail('Upload the photo again.');
        }

        $name = $file->getClientOriginalName();

        if ($name === '' || strlen($name) > 180 || preg_match('/[\x00-\x1F]/', $name) === 1) {
            $this->fail('This file cannot be uploaded.');
        }

        if (str_contains($name, '..') || str_contains($name, '/') || str_contains($name, '\\')) {
            $this->fail('This file cannot be uploaded.');
        }

        $parts = explode('.', strtolower($name));
        $extension = (string) array_pop($parts);

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $this->fail('Only JPEG, PNG, and WebP photos can be uploaded.');
        }

        foreach ($parts as $part) {
            if (in_array($part, self::DANGEROUS_EXTENSIONS, true)) {
                $this->fail('This file cannot be uploaded.');
            }
        }
    }

    private function assertReadableImage(string $path): void
    {
        $size = filesize($path);

        if ($size === false || $size < 32 || $size > self::MAX_UPLOAD_BYTES) {
            $this->fail($size !== false && $size > self::MAX_UPLOAD_BYTES
                ? 'Photos must be 8 MB or smaller.'
                : 'This photo could not be read. Upload a normal JPEG, PNG, or WebP photo.');
        }

        $head = file_get_contents($path, false, null, 0, 12);
        $info = @getimagesize($path);

        if ($head === false || $info === false) {
            $this->fail('This photo could not be read. Upload a normal JPEG, PNG, or WebP photo.');
        }

        $type = (int) ($info[2] ?? 0);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $expected = [
            IMAGETYPE_JPEG => 'image/jpeg',
            IMAGETYPE_PNG => 'image/png',
            IMAGETYPE_WEBP => 'image/webp',
        ];

        if (! isset($expected[$type]) || $mime !== $expected[$type] || ! $this->magicMatches($head, $type)) {
            $this->fail('Only JPEG, PNG, and WebP photos can be uploaded.');
        }

        $this->assertBounds((int) $info[0], (int) $info[1]);
    }

    private function assertDecodedBounds(\GdImage $image): void
    {
        $this->assertBounds(imagesx($image), imagesy($image));
    }

    private function assertBounds(int $width, int $height): void
    {
        if ($width < self::MIN_EDGE || $height < self::MIN_EDGE) {
            $this->fail('Use a photo at least '.self::MIN_EDGE.' pixels wide and tall.');
        }

        if ($width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION || ($width * $height) > self::MAX_PIXELS) {
            $this->fail('This photo is too large.');
        }
    }

    private function magicMatches(string $head, int $type): bool
    {
        return match ($type) {
            IMAGETYPE_JPEG => str_starts_with($head, "\xFF\xD8\xFF"),
            IMAGETYPE_PNG => str_starts_with($head, "\x89PNG\r\n\x1A\n"),
            IMAGETYPE_WEBP => str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WEBP',
            default => false,
        };
    }

    private function decode(string $path): \GdImage
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            $this->fail('This photo could not be read. Upload a normal JPEG, PNG, or WebP photo.');
        }

        $image = @imagecreatefromstring($contents);

        if (! $image instanceof \GdImage) {
            $this->fail('This photo could not be read. Upload a normal JPEG, PNG, or WebP photo.');
        }

        return $image;
    }

    private function orient(\GdImage $image, string $path): \GdImage
    {
        $info = @getimagesize($path);

        if (($info[2] ?? null) !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        if ($orientation === 2) {
            imageflip($image, IMG_FLIP_HORIZONTAL);

            return $image;
        }

        if ($orientation === 4) {
            imageflip($image, IMG_FLIP_VERTICAL);

            return $image;
        }

        $angle = match ($orientation) {
            3 => 180.0,
            6 => -90.0,
            8 => 90.0,
            default => null,
        };

        if ($angle === null) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if (! $rotated instanceof \GdImage) {
            return $image;
        }

        $this->destroy($image);

        return $rotated;
    }

    private function flatten(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $canvas = imagecreatetruecolor($width, $height);

        if (! $canvas instanceof \GdImage) {
            $this->destroy($image);
            $this->fail('This photo could not be read. Upload a normal JPEG, PNG, or WebP photo.');
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
        $this->destroy($image);

        return $canvas;
    }

    private function fit(\GdImage $image, int $maxEdge): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest <= $maxEdge) {
            return $image;
        }

        $scale = $maxEdge / $longest;
        $scaled = imagescale(
            $image,
            max(1, (int) round($width * $scale)),
            max(1, (int) round($height * $scale)),
            IMG_BICUBIC,
        );

        if (! $scaled instanceof \GdImage) {
            return $image;
        }

        $this->destroy($image);

        return $scaled;
    }

    private function blur(string $jpeg): string
    {
        $image = @imagecreatefromstring($jpeg);

        if (! $image instanceof \GdImage) {
            $this->fail('This photo could not be blurred.');
        }

        $tiny = null;
        $blurred = null;

        try {
            $width = max(1, imagesx($image));
            $height = max(1, imagesy($image));
            $tiny = imagescale($image, 28, max(1, (int) round($height * (28 / $width))), IMG_BILINEAR_FIXED);

            if (! $tiny instanceof \GdImage) {
                $this->fail('This photo could not be blurred.');
            }

            $targetWidth = min($width, 720);
            $targetHeight = max(1, (int) round($height * ($targetWidth / $width)));
            $blurred = imagescale($tiny, $targetWidth, $targetHeight, IMG_BILINEAR_FIXED);
            $this->destroy($tiny);
            $tiny = null;

            if (! $blurred instanceof \GdImage) {
                $this->fail('This photo could not be blurred.');
            }

            imagefilter($blurred, IMG_FILTER_GAUSSIAN_BLUR);
            imagefilter($blurred, IMG_FILTER_GAUSSIAN_BLUR);

            return $this->jpegBytes($blurred, self::BLUR_MAX_BYTES);
        } finally {
            $this->destroy($tiny);
            $this->destroy($blurred);
            $this->destroy($image);
        }
    }

    private function jpegBytes(\GdImage $image, int $maxBytes): string
    {
        $quality = 82;
        $working = $image;
        $owned = [];

        try {
            for ($attempt = 0; $attempt < 8; $attempt++) {
                ob_start();
                imagejpeg($working, null, $quality);
                $bytes = ob_get_clean();

                if (is_string($bytes) && $this->isJpeg($bytes) && strlen($bytes) <= $maxBytes) {
                    return $bytes;
                }

                if ($quality > 58) {
                    $quality -= 8;

                    continue;
                }

                $width = max(480, (int) round(imagesx($working) * 0.85));
                $height = max(480, (int) round(imagesy($working) * 0.85));

                if ($width >= imagesx($working) && $height >= imagesy($working)) {
                    break;
                }

                $scaled = imagescale($working, $width, $height, IMG_BICUBIC);

                if (! $scaled instanceof \GdImage) {
                    break;
                }

                $owned[] = $scaled;
                $working = $scaled;
                $quality = 72;
            }
        } finally {
            foreach ($owned as $extra) {
                $this->destroy($extra);
            }
        }

        $this->fail('This photo could not be saved at a smaller size.');
    }

    private function isJpeg(string $bytes): bool
    {
        if (! str_starts_with($bytes, "\xFF\xD8\xFF")) {
            return false;
        }

        $info = @getimagesizefromstring($bytes);

        return $info !== false && ($info[2] ?? null) === IMAGETYPE_JPEG;
    }

    private function readOwned(int $candidateId, string $path): ?string
    {
        $absolute = $this->absoluteOwnedFile($candidateId, $path);

        if ($absolute === null) {
            return null;
        }

        $bytes = file_get_contents($absolute);

        if ($bytes === false || ! $this->isJpeg($bytes)) {
            return null;
        }

        return $bytes;
    }

    private function absoluteOwnedFile(int $candidateId, string $path): ?string
    {
        $safe = $this->ownedPath($candidateId, $path);

        if ($safe === null) {
            return null;
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($safe)) {
            return null;
        }

        $root = realpath($disk->path('candidate-photos/'.$candidateId));
        $absolute = realpath($disk->path($safe));

        if ($root === false || $absolute === false || ! str_starts_with($absolute, $root.DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $absolute;
    }

    private function destroy(mixed $image): void
    {
        if ($image instanceof \GdImage) {
            imagedestroy($image);
        }
    }

    /**
     * @return never
     */
    private function fail(string $message): void
    {
        throw ValidationException::withMessages([
            'photo' => $message,
        ]);
    }
}
