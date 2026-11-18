@php $rp = fn($n) => 'Rp ' . number_format((int) abs($n), 0, ',', '.'); @endphp
@if($txs->isEmpty())
    <p class="py-4 text-center text-xs text-slate-400">Belum ada transaksi pada periode ini.</p>
@else
    <ul class="divide-y divide-slate-100">
        @foreach($txs as $t)
            <li class="flex items-center justify-between gap-3 py-2.5 text-sm">
                <div class="min-w-0">
                    <p class="truncate font-medium text-slate-800">{{ $t->note ?: ($t->category->name ?? '-') }}</p>
                    <p class="truncate text-xs text-slate-400">
                        {{ $t->transacted_at->translatedFormat('j M Y') }} · {{ $t->category->name ?? '-' }} ·
                        {{ $t->account->name ?? '-' }}
                    </p>
                </div>
                <p class="shrink-0 font-medium tabular-nums text-slate-900">{{ $rp($t->amount) }}</p>
            </li>
        @endforeach
    </ul>
    @if($txs->count() >= 100)
        <p class="pt-2 text-center text-[11px] text-slate-400">Menampilkan 100 transaksi terbaru.</p>
    @endif
@endif
