<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// File: 2026_01_03_000000_create_toolbox_items_table.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toolbox_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_url')->nullable();
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();

            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toolbox_items');
    }
};