<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin user ────────────────────────────────────────────────────
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Mohammad David Nur Syahfrudin',
                'email'    => 'admin@example.com',
                'password' => Hash::make('password'),  // change after first login!
                'role'     => 'admin',
            ]
        );

        // ── Content seeders ───────────────────────────────────────────────
        $this->call([
            ToolboxSeeder::class,
            ProjectSeeder::class,
            JournalSeeder::class,
        ]);
    }
}