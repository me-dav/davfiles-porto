<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// File: 2026_01_02_000000_create_journal_entries_table.php

return new class extends Migration {
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['exploration', 'server', 'hiking', 'ui_thoughts', 'learning_log']);
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('image_path')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body');                       // Markdown
            $table->json('tags')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};