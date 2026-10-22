<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('from_account_id')->constrained('accounts')->restrictOnDelete();
            $t->foreignId('to_account_id')->constrained('accounts')->restrictOnDelete();
            $t->bigInteger('amount');
            $t->bigInteger('fee')->default(0);
            $t->date('transacted_at');
            $t->string('note')->nullable();
            $t->string('source', 20)->default('web');
            $t->string('idempotency_key', 64)->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['user_id', 'transacted_at']);
            $t->unique(['user_id', 'idempotency_key']);
        });

        DB::statement('ALTER TABLE transfers ADD CONSTRAINT transfers_valid
        CHECK (from_account_id <> to_account_id AND amount > 0 AND fee >= 0)');

        Schema::table('transactions', function (Blueprint $t) {
            $t->foreignId('transfer_id')->nullable()->after('account_id')
                ->constrained('transfers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $t) {
            $t->dropConstrainedForeignId('transfer_id');
        });
        Schema::dropIfExists('transfers');
    }
};
