{{-- resources/views/livewire/finance/dashboard.blade.php --}}
@php
    $rp = fn($n) => 'Rp ' . number_format(abs($n), 0, ',', '.');
    // $goodWhenUp: true untuk masuk/selisih, false untuk keluar (naik = buruk)
    $badge = function (?float $p, bool $goodWhenUp) {
        if ($p === null)
            return '';
        $good = ($p >= 0) === $goodWhenUp;
        $cls = $good ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700';
        return '<span class="ml-2 rounded px-1.5 py-0.5 text-xs font-medium ' . $cls . '">' . ($p >= 0 ? '▲' : '▼') . ' ' . abs($p) . '%</span>';
    };
    $btn = fn($on) => $on ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100';
@endphp

<div class="space-y-4">
    {{-- Filter --}}
    <x-erp.card>
        <div class="flex flex-wrap items-end gap-3">
            <div class="inline-flex rounded-lg border border-gray-200 p-0.5 text-sm">
                <button wire:click="setPreset('week')" class="rounded-md px-3 py-1.5 {{ $btn($preset === 'week') }}">7
                    Hari</button>
                <button wire:click="setPreset('month')"
                    class="rounded-md px-3 py-1.5 {{ $btn($preset === 'month') }}">Bulan ini</button>
                <button wire:click="setPreset('3months')"
                    class="rounded-md px-3 py-1.5 {{ $btn($preset === '3months') }}">3 Bulan</button>
            </div>

            <div class="flex items-center gap-2 text-sm">
                <input type="date" wire:model.live="from" class="rounded-lg border-gray-300 text-sm">
                <span class="text-gray-400">–</span>
                <input type="date" wire:model.live="to" class="rounded-lg border-gray-300 text-sm">
            </div>

            <div class="inline-flex rounded-lg border border-gray-200 p-0.5 text-sm">
                <button wire:click="$set('donutType','expense')"
                    class="rounded-md px-3 py-1.5 {{ $btn($donutType === 'expense') }}">Donut: Keluar</button>
                <button wire:click="$set('donutType','income')"
                    class="rounded-md px-3 py-1.5 {{ $btn($donutType === 'income') }}">Masuk</button>
            </div>
        </div>
        <p class="mt-3 text-xs text-slate-500">Periode: {{ $rangeLabel }} · dibandingkan dengan {{ $prevLabel }}</p>
    </x-erp.card>

    {{-- Kartu --}}
    <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,340px)_1fr]">
        <x-erp.card>
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800">
                    My cards <sup class="ml-0.5 text-xs font-normal text-slate-400">{{ count($accountCards) }}</sup>
                </h3>
                <a href="{{ route('finance.accounts', ['add' => 1]) }}"
                    class="inline-flex items-center gap-1.5 rounded-full bg-slate-900 px-3.5 py-1.5 text-xs font-medium text-white transition hover:bg-blue-600">
                    Add <span class="text-sm leading-none">+</span>
                </a>
            </div>

            <x-erp.balance-stack :total="$balance" :cards="$accountCards" />
        </x-erp.card>

        <div class="grid gap-5 sm:grid-cols-3">
            <x-erp.stat color="purple" label="Masuk" :value="$rp($totals['income'])" :delta="$deltas['income']"
                :good-when-up="true" :sub="'Sebelumnya: ' . $rp($prevTotals['income'])" />

            <x-erp.stat color="orange" label="Keluar" :value="$rp($totals['expense'])" :delta="$deltas['expense']"
                :good-when-up="false" :sub="'Sebelumnya: ' . $rp($prevTotals['expense'])" />

            <x-erp.stat color="blue" label="Selisih" :value="($totals['net'] < 0 ? '−' : '+') . $rp($totals['net'])"
                :delta="$deltas['net']" :good-when-up="true" :sub="'Sebelumnya: ' . ($prevTotals['net'] < 0 ? '−' : '+') . $rp($prevTotals['net'])" />
        </div>
    </div>

    @if($insight)
        <p class="text-sm text-gray-600">💡 {{ $insight }}</p>
    @endif

    {{-- Grafik (wire:ignore: di-update lewat event, bukan re-render) --}}
    <div x-data="financeCharts(@js($chart))" x-on:charts-updated.window="update($event.detail.chart)"
        class="grid gap-5 lg:grid-cols-3">

        <x-erp.card class="lg:col-span-2">
            <div class="grid gap-6 md:grid-cols-[200px_1fr]">
                {{-- angka: BOLEH ikut re-render --}}
                <div>
                    <p class="text-sm font-medium text-slate-800">Dashboard</p>
                    <p class="text-xs text-slate-400">{{ $rangeLabel }}</p>
                    <p class="mt-6 text-3xl font-bold text-slate-900 tabular-nums">{{ $rp($totals['income']) }}</p>
                    <p class="text-xs text-slate-400">Pemasukan periode ini</p>
                    <p class="mt-4 text-2xl font-bold text-slate-900 tabular-nums">{{ $rp($totals['expense']) }}</p>
                    <p class="text-xs text-slate-400">Pengeluaran periode ini</p>
                </div>

                {{-- canvas: JANGAN di-render ulang --}}
                <div wire:ignore class="h-64"><canvas x-ref="bar"></canvas></div>
            </div>
        </x-erp.card>

        <x-erp.card>
            <p class="text-sm font-medium text-slate-800">Kategori</p>
            <div wire:ignore class="mt-3 h-56"><canvas x-ref="donut"></canvas></div>
        </x-erp.card>
    </div>

    {{-- Tabel kategori --}}
    <div class="grid gap-5 lg:grid-cols-[1fr_2fr]">
        <x-erp.card class="">
            <p class="text-sm font-medium text-slate-800">Transaksi terakhir</p>
            <ul class="mt-5 space-y-5">
                @forelse($recent as $t)
                    <li class="flex items-center gap-3">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white
                                                                                {{ $t->type === 'income' ? 'bg-purple-500' : 'bg-pink-500' }}">{{ $t->type === 'income' ? '+' : '−' }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[13px] font-medium text-slate-800">{{ $t->category->name }}</p>
                            <p class="truncate text-xs text-slate-400 max-w-xs lg:max-w-full">{{ $t->note ?: '-' }} ·
                                {{ $t->transacted_at->translatedFormat('d M') }}
                            </p>
                        </div>
                        <span class="text-[13px] font-medium tabular-nums">{{ $rp($t->amount) }}</span>
                    </li>
                @empty
                    <li class="text-sm text-slate-400">Belum ada transaksi.</li>
                @endforelse
            </ul>
        </x-erp.card>

        <x-erp.card class="p-0">
            <div class="overflow-hidden rounded-xl">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-800 text-[11px] font-medium uppercase tracking-wider text-white">
                        <tr>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-right">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($cats as $c)
                            @php $prev = (int) ($prevCats[$c->name] ?? 0); @endphp
                            <tr>
                                <td class="px-4 py-3">{{ $c->name }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $rp($c->total) }}</td>
                                <td class="px-4 py-3 text-right tabular-nums text-gray-500">
                                    {{ $sum ? round($c->total / $sum * 100) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-400">Belum ada data pada periode
                                    ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-erp.card>
    </div>
</div>