<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // create_accounts_table
        Schema::create('accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->enum('type', ['bank', 'cash', 'ewallet', 'investment'])->default('cash');
            $t->bigInteger('opening_balance')->default(0);   // Rupiah, integer
            $t->boolean('is_default')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
