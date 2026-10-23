<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $t) {
            $t->string('testimonial_token', 64)->nullable()->unique();
            $t->boolean('testimonial_open')->default(false);
        });

        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->cascadeOnDelete();
            $t->string('name', 80);
            $t->string('email', 150);                 // disimpan huruf kecil
            $t->unsignedTinyInteger('rating');
            $t->text('body');
            $t->boolean('consent')->default(false);
            $t->string('status', 10)->default('pending')->index();
            $t->string('ip_hash', 64)->nullable();
            $t->timestamps();

            $t->unique(['project_id', 'email']);       // satu testimoni per email per project
        });

        DB::statement('ALTER TABLE testimonials ADD CONSTRAINT testimonials_rating_range CHECK (rating BETWEEN 1 AND 5)');
        DB::statement("ALTER TABLE testimonials ADD CONSTRAINT testimonials_status_valid CHECK (status IN ('pending','active','rejected'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
        Schema::table('projects', function (Blueprint $t) {
            $t->dropColumn(['testimonial_token', 'testimonial_open']);
        });
    }
};
