<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'name' => 'Lumen Grid',
                'slug' => 'lumen-grid',
                'short_description' => 'Real-time IoT lighting control dashboard with MQTT and live WebSocket charts for industrial floors.',
                'role' => 'Full-Stack Developer',
                'stack' => ['Next.js', 'Laravel', 'MQTT', 'MySQL', 'Tailwind CSS', 'WebSocket'],
                'year' => 2025,
                'preview_image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200',
                'live_url' => 'https://lumengrid.xyz',
                'github_url' => 'https://github.com/mdavid/lumen-grid',
                'featured' => true,
                'body' => '<h2>Challenge</h2>
<p>Industrial lighting nodes across 6 floors needed sub-200ms feedback loops. The existing polling solution had ~2s latency and caused visible flicker on node transitions.</p>
<h2>Approach</h2>
<p>Deployed a Mosquitto MQTT broker feeding a Laravel Echo WebSocket server. The Next.js dashboard consumes the WebSocket stream and renders live Recharts line graphs per floor. Node commands round-trip in under 80ms.</p>
<h2>Result</h2>
<p>Reduced reaction latency from ~2 seconds (polling) to 80ms average (push). Zero missed events during a 72-hour stress test at full node capacity.</p>',
            ],
            [
                'name' => 'Folio CMS',
                'slug' => 'folio-cms',
                'short_description' => 'Headless editorial CMS with a drag-and-drop layout builder built for independent publishers.',
                'role' => 'Lead Developer',
                'stack' => ['React', 'Laravel', 'MySQL', 'AWS S3', 'Tailwind CSS'],
                'year' => 2024,
                'preview_image_url' => 'https://images.unsplash.com/photo-1499951360447-b19be8fe80f5?w=1200',
                'live_url' => null,
                'github_url' => 'https://github.com/mdavid/folio-cms',
                'featured' => true,
                'body' => '<h2>Challenge</h2>
<p>Non-technical editors at a small publishing house needed a Notion-like authoring experience with pixel-precise layout control — without paying for a SaaS CMS.</p>
<h2>Approach</h2>
<p>Built a headless CMS using Laravel as the API layer and a custom React drag-and-drop editor. Content blocks are serialised as JSON and rendered by a separate Next.js front-end.</p>
<h2>Result</h2>
<p>Editorial team onboarded in one afternoon. Publishing turnaround dropped from 3 days (Word → developer → live) to same-day self-serve.</p>',
            ],
            [
                'name' => 'Trail Log',
                'slug' => 'trail-log',
                'short_description' => 'Mobile-first hiking journal with GPX track import, elevation charts, and offline-first PWA support.',
                'role' => 'Solo Developer & Designer',
                'stack' => ['Next.js', 'Python', 'FastAPI', 'PostgreSQL', 'Leaflet.js'],
                'year' => 2024,
                'preview_image_url' => 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=1200',
                'live_url' => 'https://traillog.app',
                'github_url' => null,
                'featured' => true,
                'body' => '<h2>Challenge</h2>
<p>GPX files exported from different GPS devices (Garmin, Suunto, phone apps) have wildly inconsistent schemas. Elevation data also needed smoothing — raw barometric readings are noisy on steep ascents.</p>
<h2>Approach</h2>
<p>Built a Python FastAPI service that normalises GPX across schemas, applies a Savitzky-Golay filter to elevation data, and stores processed tracks in PostgreSQL. The Next.js PWA uses a service worker to cache tracks for offline viewing on the trail.</p>
<h2>Result</h2>
<p>Supports 12 distinct GPX dialects. Elevation accuracy improved by ~30% compared to raw data on technical mountain routes.</p>',
            ],
        ];

        foreach ($projects as $data) {
            Project::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}