<?php

// app/Http/Controllers/Api/V1/FinanceApiController.php
namespace App\Http\Controllers\Api\V1;

use App\Actions\Finance\AddTransaction;
use App\Http\Controllers\Controller;
use App\Models\{Account, FinanceCategory, Transaction};
use App\Support\{ApiResponse, CategoryResolver, MoneyParser, SummaryService};
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinanceApiController extends Controller
{
    use ApiResponse;

    private function tx(Transaction $t): array
    {
        return [
            'id' => $t->id,
            'account' => $t->account?->name,
            'type' => $t->type,
            'amount' => $t->amount,
            'category' => $t->category?->name,
            'note' => $t->note,
            'date' => $t->transacted_at->toDateString(),
            'source' => $t->source,
        ];
    }

    public function categories()
    {
        return $this->ok([
            'expense' => FinanceCategory::where('kind', 'expense')->orderBy('name')->pluck('name'),
            'income' => FinanceCategory::where('kind', 'income')->orderBy('name')->pluck('name'),
        ], 'Categories fetched');
    }

    public function accounts()
    {
        return $this->ok(['accounts' => Account::where('is_active', true)->get(['id', 'name', 'type', 'is_default'])], 'Accounts fetched');
    }

    public function index(Request $r)
    {
        $r->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'type' => 'nullable|in:income,expense',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $q = Transaction::with(['category', 'account'])
            ->when($r->from, fn($q, $v) => $q->whereDate('transacted_at', '>=', $v))
            ->when($r->to, fn($q, $v) => $q->whereDate('transacted_at', '<=', $v))
            ->when($r->type, fn($q, $v) => $q->where('type', $v))
            ->when($r->category, fn($q, $v) => $q->whereHas('category', fn($c) => $c->whereRaw('LOWER(name)=?', [mb_strtolower($v)])))
            ->orderByDesc('transacted_at')->orderByDesc('id');

        $items = $q->limit($r->integer('limit', 20))->get();

        return $this->ok(['transactions' => $items->map(fn($t) => $this->tx($t))], 'Transactions fetched');
    }

    public function store(Request $r, AddTransaction $action)
    {
        $r->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required',
            'category' => 'required|string|max:60',
            'note' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'account' => 'nullable|string|max:60',
        ]);

        $key = $r->header('Idempotency-Key');
        if ($key && ($dup = Transaction::with(['category', 'account'])->where('idempotency_key', $key)->first())) {
            return $this->ok(['transaction' => $this->tx($dup)], 'Duplicate ignored');
        }

        try {
            $amount = MoneyParser::toInt(is_numeric($r->amount) ? (int) $r->amount : (string) $r->amount);
        } catch (\InvalidArgumentException $e) {
            return $this->fail('Invalid amount: ' . $r->amount, 422);
        }

        $cat = CategoryResolver::find($r->category, $r->type);
        if (!$cat) {
            $list = FinanceCategory::where('kind', $r->type)->pluck('name')->join(', ');
            return $this->fail("Category not found: {$r->category}. Available: {$list}", 422);
        }

        $accountId = null;
        if ($r->filled('account')) {
            $acc = Account::where('is_active', true)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($r->account))])->first();
            if (!$acc) {
                $list = Account::where('is_active', true)->pluck('name')->join(', ');
                return $this->fail("Account not found: {$r->account}. Available: {$list}", 422);
            }
            $accountId = $acc->id;
        }

        $source = mb_substr($r->user()->currentAccessToken()?->name ?? 'api', 0, 20);

        $tx = $action->handle([
            'account_id' => $accountId,
            'type' => $r->type,
            'amount' => $amount,
            'category_id' => $cat->id,
            'note' => $r->note,
            'transacted_at' => $r->date ? Carbon::parse($r->date) : today(),
            'source' => $source,
            'idempotency_key' => $key,
        ])->load(['category', 'account']);

        $now = now();
        return $this->ok([
            'transaction' => $this->tx($tx),
            'month_summary' => SummaryService::totals($now->copy()->startOfMonth(), $now->copy()->startOfDay()),
        ], 'Transaction recorded', 201);
    }

    public function summary(Request $r)
    {
        $r->validate(['period' => ['nullable', 'regex:/^\d{4}-\d{2}$/']]);
        $start = Carbon::createFromFormat('Y-m-d', ($r->period ?? now()->format('Y-m')) . '-01')->startOfDay();
        $end = $start->copy()->endOfMonth()->startOfDay();

        return $this->ok([
            'period' => $start->format('Y-m'),
            'totals' => SummaryService::totals($start, $end),
            'top_expense_categories' => SummaryService::byCategory($start, $end, 'expense')->take(5)->values(),
        ], 'Summary fetched');
    }

    public function destroy(int $id)
    {
        Transaction::findOrFail($id)->delete();   // soft delete, terfilter per user
        return $this->ok(null, 'Transaction deleted');
    }
}
