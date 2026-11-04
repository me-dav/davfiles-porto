<?php

namespace App\Livewire\Finance;

use App\Models\{Account, Budget, FinanceCategory};
use App\Support\{BudgetService, MoneyParser};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class BudgetForm extends Component
{
    public const MAX_BUDGETS = 30;

    public bool $show = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $periodType = 'monthly';   // weekly | monthly | custom
    public int $startDay = 1;
    public string $startsOn = '';
    public string $endsOn = '';
    public string $amount = '';
    public bool $allCategories = false;
    public array $categoryIds = [];          // string, agar cocok dengan value checkbox
    public bool $allAccounts = true;
    public array $accountIds = [];

    private function guard(): void
    {
        $u = auth()->user();
        abort_unless($u && $u->isActive() && $u->hasModule('finance'), 403);
    }

    private function resetForm(): void
    {
        $this->reset();
        $this->startsOn = today()->toDateString();
        $this->endsOn = today()->addDays(29)->toDateString();
        $this->resetValidation();
    }

    #[On('open-budget-form')]
    public function open(?int $id = null): void
    {
        $this->guard();
        $this->resetForm();

        if ($id) {
            $b = Budget::with(['categories', 'accounts', 'amounts'])->findOrFail($id);

            $this->editingId = $b->id;
            $this->name = $b->name;
            $this->periodType = $b->period_type;
            $this->startDay = (int) $b->start_day;
            $this->startsOn = $b->starts_on->toDateString();
            $this->endsOn = $b->ends_on?->toDateString() ?? $this->endsOn;
            $this->allCategories = $b->all_categories;
            $this->categoryIds = $b->categories->pluck('id')->map(fn($i) => (string) $i)->all();
            $this->allAccounts = $b->all_accounts;
            $this->accountIds = $b->accounts->pluck('id')->map(fn($i) => (string) $i)->all();

            [$start] = BudgetService::periodOf($b, today());
            $this->amount = (string) BudgetService::amountFor($b, $start);
        }

        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
        $this->resetValidation();
    }

    public function selectAllCategories(): void
    {
        $this->categoryIds = FinanceCategory::where('kind', 'expense')->pluck('id')
            ->map(fn($i) => (string) $i)->all();
    }

    public function clearCategories(): void
    {
        $this->categoryIds = [];
    }

    public function save(): void
    {
        $this->guard();
        $this->name = trim($this->name);

        $rules = [
            'name' => ['required', 'string', 'max:60'],
            'amount' => ['required', 'string', 'max:20'],
        ];
        if (!$this->editingId) {
            $rules['periodType'] = ['required', 'in:weekly,monthly,custom'];
            $rules['startDay'] = ['required', 'integer', 'between:1,28'];
        }
        if ($this->periodType === 'custom') {
            $rules['startsOn'] = ['required', 'date'];
            $rules['endsOn'] = ['required', 'date', 'after_or_equal:startsOn'];
        }

        $this->validate($rules, [
            'endsOn.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ], [
            'name' => 'nama',
            'amount' => 'nominal',
            'startDay' => 'tanggal mulai periode',
            'startsOn' => 'tanggal mulai',
            'endsOn' => 'tanggal selesai',
        ]);

        try {
            $amount = MoneyParser::toInt($this->amount);
        } catch (\InvalidArgumentException) {
            $this->addError('amount', 'Nominal tidak valid. Contoh: 2jt, 500rb, 1.500.000');
            return;
        }

        if (
            $this->periodType === 'custom'
            && Carbon::parse($this->startsOn)->diffInDays(Carbon::parse($this->endsOn), true) > 365
        ) {
            $this->addError('endsOn', 'Rentang maksimal 1 tahun.');
            return;
        }

        if (!$this->editingId && Budget::count() >= self::MAX_BUDGETS) {
            $this->addError('name', 'Batas ' . self::MAX_BUDGETS . ' budget tercapai. Hapus atau arsipkan salah satu dulu.');
            return;
        }

        // id dari browser tidak dipercaya: hanya yang benar-benar milik user ini
        $catIds = [];
        if (!$this->allCategories) {
            $ids = array_values(array_unique(array_map('intval', $this->categoryIds)));
            $catIds = FinanceCategory::where('kind', 'expense')->whereIn('id', $ids)->pluck('id')->all();
            if (!$catIds) {
                $this->addError('categoryIds', 'Pilih minimal satu kategori, atau aktifkan "Semua kategori pengeluaran".');
                return;
            }
        }

        $accIds = [];
        if (!$this->allAccounts) {
            $ids = array_values(array_unique(array_map('intval', $this->accountIds)));
            $accIds = Account::where('is_active', true)->whereIn('id', $ids)->pluck('id')->all();
            if (!$accIds) {
                $this->addError('accountIds', 'Pilih minimal satu akun, atau aktifkan "Semua akun".');
                return;
            }
        }

        DB::transaction(function () use ($amount, $catIds, $accIds) {
            if ($this->editingId) {
                $b = Budget::with('amounts')->findOrFail($this->editingId);

                $attrs = [
                    'name' => $this->name,
                    'all_categories' => $this->allCategories,
                    'all_accounts' => $this->allAccounts,
                ];
                if (!$b->isRecurring()) {
                    $attrs['starts_on'] = $this->startsOn;
                    $attrs['ends_on'] = $this->endsOn;
                }
                $b->update($attrs);
                $b->refresh()->load('amounts');

                if ($b->isRecurring()) {
                    // nominal baru berlaku mulai periode berjalan; periode lama tidak berubah
                    [$periodStart] = BudgetService::periodOf($b, today());
                    if (BudgetService::amountFor($b, $periodStart) !== $amount) {
                        $b->amounts()->updateOrCreate(
                            ['effective_from' => $periodStart->toDateString()],
                            ['amount' => $amount],
                        );
                    }
                } else {
                    $b->amounts()->delete();
                    $b->amounts()->create(['amount' => $amount, 'effective_from' => $b->starts_on->toDateString()]);
                }
            } else {
                if ($this->periodType === 'custom') {
                    $starts = Carbon::parse($this->startsOn)->startOfDay();
                    $ends = Carbon::parse($this->endsOn)->startOfDay();
                } else {
                    [$starts] = BudgetService::range($this->periodType, $this->startDay, today());
                    $ends = null;
                }

                $b = Budget::create([
                    'name' => $this->name,
                    'period_type' => $this->periodType,
                    'start_day' => $this->periodType === 'monthly' ? $this->startDay : 1,
                    'starts_on' => $starts->toDateString(),
                    'ends_on' => $ends?->toDateString(),
                    'all_categories' => $this->allCategories,
                    'all_accounts' => $this->allAccounts,
                ]);
                $b->amounts()->create(['amount' => $amount, 'effective_from' => $starts->toDateString()]);
            }

            $b->categories()->sync($catIds);
            $b->accounts()->sync($accIds);
        });

        $this->close();
        $this->dispatch('budget-saved');
    }

    private function hint(): ?array
    {
        $catIds = array_map('intval', $this->categoryIds);
        $accIds = array_map('intval', $this->accountIds);

        if (!$this->allCategories && !$catIds) {
            return null;
        }
        if (!$this->allAccounts && !$accIds) {
            return null;
        }

        try {
            $s = $this->startsOn !== '' ? Carbon::parse($this->startsOn) : null;
            $e = $this->endsOn !== '' ? Carbon::parse($this->endsOn) : null;
        } catch (\Throwable) {
            $s = $e = null;
        }

        $type = in_array($this->periodType, ['weekly', 'monthly', 'custom'], true) ? $this->periodType : 'monthly';

        return BudgetService::hint($type, $this->startDay, $s, $e, $this->allCategories, $catIds, $this->allAccounts, $accIds);
    }

    public function render()
    {
        return view('livewire.finance.budget-form', [
            'categories' => FinanceCategory::where('kind', 'expense')->orderBy('name')->get(['id', 'name']),
            'accounts' => Account::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get(['id', 'name']),
            'hint' => $this->show ? $this->hint() : null,
        ]);
    }
}