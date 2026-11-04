<div class="space-y-3" x-data="{ by(n) { $refs.rail.scrollBy({ left: n, behavior: 'smooth' }) } }">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-sm font-semibold text-slate-800">
            Budget <sup class="ml-0.5 text-xs font-normal text-slate-400">{{ $total }}</sup>
        </h3>
        <div class="flex items-center gap-3">
            <a href="{{ route('finance.budgets') }}" class="text-xs font-medium text-blue-600 hover:underline">
                {{ $total > $items->count() ? 'Lihat semua (' . $total . ')' : 'Kelola' }}
            </a>
            <div class="hidden gap-1 sm:flex">
                <button type="button" @click="by(-300)" aria-label="Geser ke kiri"
                    class="flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">←</button>
                <button type="button" @click="by(300)" aria-label="Geser ke kanan"
                    class="flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">→</button>
            </div>
        </div>
    </div>

    <div x-ref="rail" class="no-scrollbar flex snap-x snap-mandatory gap-4 overflow-x-auto pb-2">
        @foreach($items as $it)
            <div wire:key="bs-{{ $it['b']->id }}" wire:click="$dispatch('open-budget-form', { id: {{ $it['b']->id }} })"
                class="flex w-[85%] shrink-0 cursor-pointer snap-start sm:w-72">
                <x-erp.budget-card :b="$it['b']" :s="$it['s']" class="w-full" />
            </div>
        @endforeach

        <button type="button" wire:click="$dispatch('open-budget-form')"
            class="flex min-h-[11rem] w-[60%] shrink-0 snap-start flex-col items-center justify-center gap-2 rounded-2xl
                       border-2 border-dashed border-slate-300 text-slate-400 transition hover:border-blue-400 hover:text-blue-600 sm:w-56">
            <span class="text-3xl leading-none">+</span>
            <span class="text-sm font-medium">{{ $total === 0 ? 'Buat budget pertama' : 'Tambah budget' }}</span>
        </button>
    </div>

    <livewire:finance.budget-form />
</div>