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
        // create_transactions_table
        Schema::create('transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('account_id')->constrained()->restrictOnDelete();
            $t->foreignId('category_id')->constrained('finance_categories')->restrictOnDelete();
            $t->enum('type', ['income', 'expense']);
            $t->bigInteger('amount');                         // selalu positif
            $t->date('transacted_at');
            $t->string('note')->nullable();
            $t->string('source', 20)->default('web');         // web | hermes | api
            $t->string('idempotency_key', 64)->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['user_id', 'transacted_at']);
            $t->unique(['user_id', 'idempotency_key']);       // NULL boleh berulang di MySQL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
