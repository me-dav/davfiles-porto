<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    use ApiResponse;

    /**
     * POST /api/v1/uploads
     *
     * Auth required. Accepts an image file, stores it, returns the public URL.
     *
     * Body: multipart/form-data
     *   file: (image file)
     *
     * To swap local → S3 later: just change FILESYSTEM_DISK=s3 in .env
     * and add AWS credentials. No code change needed here.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:4096',  // 4 MB
            ],
        ]);

        $file      = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        // Generate a unique filename to avoid collisions
        $filename  = date('Y/m') . '/' . Str::uuid() . '.' . $extension;

        // Store to 'public' disk (local dev) or 's3' disk (production)
        // Disk is determined by FILESYSTEM_DISK in .env
        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';

        Storage::disk($disk)->put($filename, file_get_contents($file), 'public');

        $url = Storage::disk($disk)->url($filename);

        return $this->success(['url' => $url], 'File uploaded', 201);
    }
}