<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    public function uploadProductImage(
        UploadedFile $file,
        string $folder = 'products'
    ): array {

        $uuid = Str::uuid();

        $mainFilename = "{$uuid}.webp";
        $thumbFilename = "thumb_{$uuid}.webp";

        /*
        |--------------------------------------------------------------------------
        | Main Image
        |--------------------------------------------------------------------------
        */

        $mainImage = Image::read($file);

        if ($mainImage->width() > 1200) {
            $mainImage->scale(width: 1200);
        }

        Storage::disk('public')->put(
            "{$folder}/{$mainFilename}",
            $mainImage->toWebp(80)
        );

        /*
        |--------------------------------------------------------------------------
        | Thumbnail
        |--------------------------------------------------------------------------
        */

        $thumbnail = Image::read($file);

        $thumbnail->cover(400, 400);

        Storage::disk('public')->put(
            "{$folder}/{$thumbFilename}",
            $thumbnail->toWebp(70)
        );

        return [
            'url' => asset("storage/{$folder}/{$mainFilename}"),
            'thumbnail_url' => asset("storage/{$folder}/{$thumbFilename}"),
        ];
    }

    public function deleteImage(string $url): void
    {
        $path = str_replace(
            asset('storage') . '/',
            '',
            $url
        );

        Storage::disk('public')->delete($path);

        $filename = basename($path);

        if (!str_starts_with($filename, 'thumb_')) {

            $thumbPath = dirname($path)
                . '/thumb_'
                . $filename;

            Storage::disk('public')->delete($thumbPath);
        }
    }
}
