<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// File: 2026_01_01_000000_create_projects_table.php

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->string('role')->nullable();
            $table->enum('category', ['Web', 'IoT', 'Editing', 'Other'])->default('Web');
            // Stored as JSON string: '["Next.js","Laravel"]'
            // Cast to array via Model::$casts
            $table->json('stack')->nullable();

            $table->unsignedSmallInteger('year');

            $table->string('image_path')->nullable();
            $table->string('live_url')->nullable();
            $table->string('github_url')->nullable();

            // Full HTML / rich text body (case study)
            $table->longText('body')->nullable();

            $table->boolean('featured')->default(false)->index();

            $table->timestamps();
            $table->unsignedInteger('sort_order')->default(0);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};