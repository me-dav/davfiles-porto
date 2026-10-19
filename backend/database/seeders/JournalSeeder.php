<?php

namespace Database\Seeders;

use App\Models\JournalEntry;
use Illuminate\Database\Seeder;

class JournalSeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            ['exploration', 'Mencoba Docker Swarm', ['server', 'learning']],
            ['server', 'Catatan Hardening Ubuntu', ['server']],
            ['hiking', 'Kabut di Penanggungan', ['hiking']],
            ['ui_thoughts', 'Whitespace sebagai Fitur', ['ui']],
        ];
        foreach ($entries as [$type, $title, $tags]) {
            \App\Models\JournalEntry::firstOrCreate(['slug' => \Illuminate\Support\Str::slug($title)], [
                'type' => $type,
                'title' => $title,
                'tags' => $tags,
                'status' => 'published',
                'published_at' => now(),
                'excerpt' => 'Ringkasan dummy.',
                'body' => "Isi dummy untuk **{$title}**.",
            ]);
        }
    }
}