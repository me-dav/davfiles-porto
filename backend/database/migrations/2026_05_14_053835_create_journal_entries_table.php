<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// File: 2026_01_02_000000_create_journal_entries_table.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();

            $table->enum('type', [
                'exploration',
                'server-notes',
                'hiking',
                'ui-thoughts',
                'learning-log',
            ]);

            $table->string('hero_image_url')->nullable();

            // Full HTML / Markdown body
            $table->longText('body')->nullable();

            // Stored as JSON string: '["server","proxmox"]'
            $table->json('tags')->nullable();

            $table->enum('status', ['draft', 'published'])->default('draft');

            // Set when status transitions to 'published'
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            // Indexes used in list queries
            $table->index('status');
            $table->index('type');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};