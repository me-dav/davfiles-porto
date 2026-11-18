<div>
    @if($show && $b)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4"
            wire:key="budget-detail-modal" wire:click.self="close">
            <div class="my-8 w-full max-w-xl rounded-2xl bg-white p-5 shadow-xl sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="truncate text-base font-semibold text-slate-900">{{ $b->name }}</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Transaksi periode {{ $s['period_label'] }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <button type="button" wire:click="edit"
                            class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-violet-700">Edit</button>
                        <button type="button" wire:click="close" aria-label="Tutup"
                            class="text-slate-400 hover:text-slate-700">✕</button>
                    </div>
                </div>

                <div class="mt-4 rounded-xl bg-slate-50 p-3 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-bold tabular-nums text-slate-900">Rp {{ number_format($s['spent'], 0, ',', '.') }}</span>
                        <span class="text-xs text-slate-500">dari Rp {{ number_format($s['amount'], 0, ',', '.') }}
                            · {{ (int) round($s['pct']) }}%</span>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full" style="width: {{ $s['bar'] }}%; background: {{ $s['color'] }}"></div>
                    </div>
                </div>

                <div class="mt-3 max-h-[50vh] overflow-y-auto">
                    @include('livewire.finance.partials.budget-transactions', ['txs' => $txs])
                </div>
            </div>
        </div>
    @endif
</div>
