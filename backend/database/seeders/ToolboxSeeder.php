<?php

namespace Database\Seeders;

use App\Models\ToolboxItem;
use Illuminate\Database\Seeder;

class ToolboxSeeder extends Seeder
{
    public function run(): void
    {
        $tools = [
            ['name' => 'React',       'logo_url' => null, 'order' => 1],
            ['name' => 'Next.js',     'logo_url' => null, 'order' => 2],
            ['name' => 'TypeScript',  'logo_url' => null, 'order' => 3],
            ['name' => 'Laravel',     'logo_url' => null, 'order' => 4],
            ['name' => 'Python',      'logo_url' => null, 'order' => 5],
            ['name' => 'C++',         'logo_url' => null, 'order' => 6],
            ['name' => 'Docker',      'logo_url' => null, 'order' => 7],
            ['name' => 'Tailwind CSS','logo_url' => null, 'order' => 8],
            ['name' => 'Vercel',      'logo_url' => null, 'order' => 9],
            ['name' => 'GitHub',      'logo_url' => null, 'order' => 10],
            ['name' => 'MySQL',       'logo_url' => null, 'order' => 11],
            ['name' => 'Linux',       'logo_url' => null, 'order' => 12],
        ];

        foreach ($tools as $tool) {
            ToolboxItem::firstOrCreate(['name' => $tool['name']], $tool);
        }
    }
}