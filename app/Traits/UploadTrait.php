<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait UploadTrait
{

    public function uploadFile($file, $folderName)
    {
        return $file->store($folderName, 'public');
    }

    public function deleteFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}