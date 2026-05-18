<?php

namespace Database\Seeders;

use App\Models\JournalEntry;
use Illuminate\Database\Seeder;

class JournalSeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            [
                'title' => 'Setting Up a Homelab with Proxmox VE 8',
                'slug' => 'homelab-proxmox-ve-8',
                'type' => 'server-notes',
                'hero_image_url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1200',
                'tags' => ['server', 'proxmox', 'homelab', 'virtualisation'],
                'status' => 'published',
                'published_at' => '2025-12-10 09:00:00',
                'body' => '<p>After years of running bare-metal Ubuntu servers I finally migrated everything to Proxmox VE 8.1. The promise: one hypervisor, isolated VMs per service, ZFS snapshots for backup.</p>
<h2>What broke immediately</h2>
<p>The network bridge config changed in Proxmox 8 — my old <code>vmbr0</code> setup dropped all traffic after the upgrade. Turns out the default MTU was being set to 1500 but my NIC was advertising 9000 (jumbo frames). Adding <code>mtu 1</code> to the bridge config in <code>/etc/network/interfaces</code> fixed it.</p>
<h2>ZFS pool setup</h2>
<p>Running a mirrored pool across two 4TB drives. The most important command I wish I had known earlier: <code>zpool set autotrim=on tank</code>. Without this, SSD performance degrades silently over weeks.</p>
<h2>Takeaways</h2>
<p>Proxmox is excellent once you accept its opinionated networking model. The web UI for VM snapshots alone is worth the migration effort.</p>',
            ],
            [
                'title' => 'Rinjani Summit — 3726m, Third Attempt',
                'slug' => 'rinjani-summit-3726m-third-attempt',
                'type' => 'hiking',
                'hero_image_url' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200',
                'tags' => ['hiking', 'lombok', 'rinjani', 'indonesia', 'summit'],
                'status' => 'published',
                'published_at' => '2025-11-01 06:00:00',
                'body' => '<p>The first two attempts were turned back by weather — once at Pelawangan II at 2700m, once 200m below the crater rim. The third attempt we left Sembalun at 2 AM.</p>
<h2>The crater rim at night</h2>
<p>At 3600m with a headtorch and 8°C wind, Rinjani is a different world from the forest trail at base camp. The volcanic sand slope is exhausting — two steps up, slide one back. You develop a rhythm by accepting that efficiency is not the point.</p>
<h2>The summit</h2>
<p>We reached the rim at 5:40 AM, 20 minutes before sunrise. Segara Anak crater lake glowing orange below. The kind of view that resets whatever you were worried about before the trip.</p>
<h2>Gear notes</h2>
<p>The single best decision: trekking poles. The volcanic sand descent without them would have been miserable. Worst decision: not bringing a balaclava — a buff over the face worked but barely.</p>',
            ],
            [
                'title' => 'Whitespace as Signal: What Editorial Design Taught Me About UI',
                'slug' => 'whitespace-editorial-ui-design',
                'type' => 'ui-thoughts',
                'hero_image_url' => 'https://images.unsplash.com/photo-1586281380349-632531db7ed4?w=1200',
                'tags' => ['design', 'typography', 'ui', 'editorial', 'whitespace'],
                'status' => 'published',
                'published_at' => '2025-10-15 08:00:00',
                'body' => '<p>Print designers have been solving hierarchy without color for 500 years. Web designers are still re-learning this every React cycle.</p>
<h2>The problem with color-driven hierarchy</h2>
<p>Most UI design uses color as the primary hierarchy signal: blue for primary actions, red for danger, green for success. This works until you need to redesign the brand, support dark mode, or meet WCAG AA contrast ratios across all combinations.</p>
<h2>What editorial design does instead</h2>
<p>Pick up any well-designed newspaper or magazine. Hierarchy comes from: type size, type weight, column width, leading, and whitespace. Not color. The reader navigates intuitively without a single colored button.</p>
<h2>Applied to UI</h2>
<p>I started treating whitespace as a design element rather than empty space. Increasing the gap between a label and its input from 4px to 8px signals "these belong together but are separate things." A 40px gap between sections signals "new topic." No color required.</p>
<p>The secondary benefit: accessible by default. Contrast is handled by type weight and size, not color-on-color combinations.</p>',
            ],
            [
                'title' => 'Learning Rust for Embedded: First 30 Days',
                'slug' => 'rust-embedded-first-30-days',
                'type' => 'learning-log',
                'hero_image_url' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=1200',
                'tags' => ['rust', 'embedded', 'learning', 'iot', 'microcontroller'],
                'status' => 'published',
                'published_at' => '2025-09-01 07:00:00',
                'body' => '<p>I spent a month convincing myself that the borrow checker is my friend. Verdict: it is — but it takes about three weeks.</p>
<h2>Week 1 — Fighting the compiler</h2>
<p>Coming from C++, my instinct was to reach for raw pointers and manual memory management. Rust makes this deliberately painful. The first week felt like being corrected constantly. Every session ended with "fine, you were right."</p>
<h2>Week 2 — Embassy and async embedded</h2>
<p>Discovered Embassy — an async executor for embedded Rust. Running async tasks on a microcontroller without an OS is a different mental model. The key insight: tasks are not threads. They are state machines that yield at <code>.await</code> points.</p>
<h2>Week 3 — Something clicked</h2>
<p>Stopped fighting the ownership model and started designing around it. If a value is moved, it cannot be used again — that constraint forces cleaner data flow than I typically write in C++.</p>
<h2>Week 4 — Shipping something</h2>
<p>Wrote a basic UART temperature logger for an STM32F4 using Embassy + embedded-hal. 512 bytes of RAM, no allocator, no panic. The binary was smaller than the equivalent C++ version.</p>
<h2>Conclusion</h2>
<p>30 days is not enough to be productive in embedded Rust. 60 days probably is. The ceiling is extremely high — the floor is genuinely steep.</p>',
            ],
        ];

        foreach ($entries as $data) {
            JournalEntry::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}