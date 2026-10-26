<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NextRevalidate
{
    public static function ping(string ...$tags): void
    {
        $url = config('services.next.url');
        if (!$url)
            return;   // tidak diset: lewati (mis. saat tes lokal)

        try {
            Http::timeout(3)
                ->withHeaders(['x-revalidate-secret' => (string) config('services.next.secret')])
                ->post($url, ['tags' => $tags]);
        } catch (\Throwable $e) {
            // gagal revalidate tidak boleh menggagalkan penyimpanan di admin
            Log::warning('Revalidate Next gagal: ' . $e->getMessage());
        }
    }
}