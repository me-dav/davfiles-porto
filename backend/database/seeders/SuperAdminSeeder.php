<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrNew(['email' => 'davidmohammadns@gmail.com']);
        $user->forceFill([
            'name' => 'Mohammad David',
            'password' => 'password',   // otomatis di-hash oleh cast
            'role' => 'super_admin',
            'status' => 'active',
            'approved_at' => now(),
            'email_verified_at' => now(),
        ])->save();
    }
}
