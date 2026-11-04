<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('name', 60);
            $t->string('period_type', 10);                    // weekly | monthly | custom
            $t->unsignedTinyInteger('start_day')->default(1); // monthly: tanggal mulai (1-28)
            $t->date('starts_on');                            // awal riwayat / tanggal mulai custom
            $t->date('ends_on')->nullable();                  // hanya custom
            $t->boolean('all_categories')->default(false);
            $t->boolean('all_accounts')->default(true);
            $t->boolean('is_archived')->default(false);
            $t->timestamps();

            $t->index(['user_id', 'is_archived']);
        });

        DB::statement("ALTER TABLE budgets ADD CONSTRAINT budgets_period_valid CHECK (period_type IN ('weekly','monthly','custom'))");
        DB::statement('ALTER TABLE budgets ADD CONSTRAINT budgets_start_day_valid CHECK (start_day BETWEEN 1 AND 28)');
        DB::statement("ALTER TABLE budgets ADD CONSTRAINT budgets_custom_valid CHECK (period_type <> 'custom' OR (ends_on IS NOT NULL AND ends_on >= starts_on))");

        // riwayat nominal: nominal berlaku mulai effective_from
        Schema::create('budget_amounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $t->bigInteger('amount');
            $t->date('effective_from');
            $t->timestamps();

            $t->unique(['budget_id', 'effective_from']);
        });

        DB::statement('ALTER TABLE budget_amounts ADD CONSTRAINT budget_amounts_positive CHECK (amount > 0)');

        Schema::create('budget_category', function (Blueprint $t) {
            $t->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $t->foreignId('category_id')->constrained('finance_categories')->cascadeOnDelete();
            $t->primary(['budget_id', 'category_id']);
        });

        Schema::create('budget_account', function (Blueprint $t) {
            $t->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $t->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $t->primary(['budget_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_account');
        Schema::dropIfExists('budget_category');
        Schema::dropIfExists('budget_amounts');
        Schema::dropIfExists('budgets');
    }
};