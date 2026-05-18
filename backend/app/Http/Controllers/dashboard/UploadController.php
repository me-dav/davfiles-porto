<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * POST /dashboard/upload
     *
     * Dipanggil via fetch() dari form Blade (JavaScript).
     * Return JSON { url } supaya bisa langsung dimasukkan ke input preview_image_url.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        $file      = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $filename  = date('Y/m') . '/' . Str::uuid() . '.' . $extension;

        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
        Storage::disk($disk)->put($filename, file_get_contents($file), 'public');
        $url = Storage::disk($disk)->url($filename);

        return response()->json(['url' => $url]);
    }
}