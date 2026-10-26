<?php

// app/Http/Controllers/TestimonialFormController.php
namespace App\Http\Controllers;

use App\Models\{Project, Testimonial};
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;

class TestimonialFormController extends Controller
{
    private function project(string $token): Project
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{40}$/', $token), 404);

        return Project::where('testimonial_token', $token)->firstOrFail();
    }

    private function page(string $view, Project $project, string $token, int $status = 200)
    {
        return response()
            ->view($view, [
                'project' => $project,
                'token' => $token,
                'formKey' => Crypt::encryptString((string) time()),   // untuk pembatas kecepatan isi
            ], $status)
            ->header('Cache-Control', 'no-store')
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function show(string $token)
    {
        $project = $this->project($token);

        if (session('sent')) {
            return $this->page('testimonial.thanks', $project, $token);
        }
        if (!$project->testimonial_open) {
            return $this->page('testimonial.closed', $project, $token, 403);
        }

        return $this->page('testimonial.form', $project, $token);
    }

    public function store(Request $request, string $token)
    {
        $project = $this->project($token);

        if (!$project->testimonial_open) {
            return $this->page('testimonial.closed', $project, $token, 403);
        }

        $done = redirect()->route('testimonial.form', $token)->with('sent', true);

        // kolom jebakan: manusia tidak melihatnya, bot mengisinya
        if ($request->filled('website')) {
            return $done;
        }

        // pembatas kecepatan isi
        try {
            $openedAt = (int) Crypt::decryptString((string) $request->input('k'));
        } catch (\Throwable) {
            $openedAt = 0;
        }
        if ($openedAt === 0) {
            return back()->withInput()->withErrors(['body' => 'Sesi form sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.']);
        }
        if (time() - $openedAt < 4) {
            return back()->withInput()->withErrors(['body' => 'Terlalu cepat. Coba kirim lagi sebentar.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:20', 'max:1000', 'not_regex:/https?:\/\/|www\./i'],
            'consent' => ['accepted'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.min' => 'Nama terlalu pendek.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'rating.required' => 'Pilih penilaian 1 sampai 5.',
            'rating.between' => 'Penilaian harus 1 sampai 5.',
            'body.required' => 'Isi testimoni wajib diisi.',
            'body.min' => 'Testimoni minimal 20 karakter.',
            'body.max' => 'Testimoni maksimal 1000 karakter.',
            'body.not_regex' => 'Mohon tidak menyertakan tautan di dalam testimoni.',
            'consent.accepted' => 'Persetujuan diperlukan agar testimoni bisa ditampilkan.',
        ]);

        // batas per IP: hanya kiriman yang berhasil yang dihitung
        $key = 'testimonial-ok:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->withInput()->withErrors(['body' => 'Terlalu banyak kiriman dari jaringan ini. Coba lagi dalam beberapa waktu.']);
        }

        if ($project->testimonials()->where('status', 'pending')->count() >= 100) {
            return back()->withInput()->withErrors(['body' => 'Form sedang tidak menerima kiriman baru. Silakan coba lagi nanti.']);
        }

        $email = mb_strtolower(trim($data['email']));
        $dupe = ['email' => 'Email ini sudah pernah mengirim testimoni untuk project ini.'];

        if ($project->testimonials()->where('email', $email)->exists()) {
            return back()->withInput()->withErrors($dupe);
        }

        try {
            Testimonial::create([
                'project_id' => $project->id,
                'name' => $this->clean($data['name'], false),
                'email' => $email,
                'rating' => (int) $data['rating'],
                'body' => $this->clean($data['body'], true),
                'consent' => true,
                'status' => 'pending',
                'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()->withInput()->withErrors($dupe);   // dua kiriman bersamaan
        }

        RateLimiter::hit($key, 3600);

        return $done;
    }

    // teks polos: buang karakter kontrol, rapikan spasi dan baris kosong berlebih
    private function clean(string $v, bool $multiline): string
    {
        $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v) ?? '';
        $v = str_replace("\r\n", "\n", $v);

        return $multiline
            ? trim(preg_replace("/\n{3,}/", "\n\n", $v))
            : trim(preg_replace('/\s+/u', ' ', $v));
    }
}
