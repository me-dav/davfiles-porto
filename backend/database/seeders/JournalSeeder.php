<?php

namespace Database\Seeders;

use App\Models\JournalEntry;
use Illuminate\Database\Seeder;

class JournalSeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            ['exploration', 'Mencoba Docker Swarm', ['server', 'learning'], 'https://images.unsplash.com/photo-1605745341112-85968b19335b?w=1200'],
            ['server', 'Catatan Hardening Ubuntu', ['server'], 'https://images.unsplash.com/photo-1629654297299-c8506221ca97?w=1200'],
            ['hiking', 'Kabut di Penanggungan', ['hiking'], 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=1200'],
            ['ui_thoughts', 'Whitespace sebagai Fitur', ['ui'], 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=1200'],
        ];
        foreach ($entries as [$type, $title, $tags, $image]) {
            $slug = \Illuminate\Support\Str::slug($title);
            $entry = \App\Models\JournalEntry::where('slug', $slug)->first();
            if ($entry) {
                if (empty($entry->image_path)) {
                    $entry->update(['image_path' => $image]);
                }
            } else {
                \App\Models\JournalEntry::create([
                    'type' => $type,
                    'title' => $title,
                    'slug' => $slug,
                    'tags' => $tags,
                    'image_path' => $image,
                    'status' => 'published',
                    'published_at' => now(),
                    'excerpt' => 'Ringkasan dummy.',
                    'body' => "Isi dummy untuk **{$title}**.",
                ]);
            }
        }
    }
}