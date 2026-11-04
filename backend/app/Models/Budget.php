<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Budget extends Model
{
    use BelongsToUser;

    protected $guarded = [];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'start_day' => 'integer',
        'all_categories' => 'boolean',
        'all_accounts' => 'boolean',
        'is_archived' => 'boolean',
    ];

    public function categories()
    {
        return $this->belongsToMany(FinanceCategory::class, 'budget_category', 'budget_id', 'category_id');
    }

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'budget_account', 'budget_id', 'account_id');
    }

    public function amounts()
    {
        return $this->hasMany(BudgetAmount::class);
    }

    public function isRecurring(): bool
    {
        return $this->period_type !== 'custom';
    }

    public function periodTypeLabel(): string
    {
        return match ($this->period_type) {
            'weekly' => 'Mingguan',
            'monthly' => $this->start_day > 1 ? 'Bulanan (tanggal ' . $this->start_day . ')' : 'Bulanan',
            default => 'Sekali jalan',
        };
    }

    public function categoryLabel(int $show = 2): string
    {
        if ($this->all_categories) {
            return 'Semua kategori';
        }
        $names = $this->categories->pluck('name')->sort()->values();

        return $names->isEmpty() ? 'Belum ada kategori' : $this->summarize($names, $show);
    }

    public function accountLabel(int $show = 2): string
    {
        if ($this->all_accounts) {
            return 'Semua akun';
        }
        $names = $this->accounts->pluck('name')->sort()->values();

        return $names->isEmpty() ? 'Belum ada akun' : $this->summarize($names, $show);
    }

    private function summarize(Collection $names, int $show): string
    {
        $head = $names->take($show)->join(', ');
        $rest = $names->count() - $show;

        return $rest > 0 ? $head . ' +' . $rest : $head;
    }
}