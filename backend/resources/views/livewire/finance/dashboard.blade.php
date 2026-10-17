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
    $btn = fn($on) => $on ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100';
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

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model.live="compare" class="rounded border-gray-300 text-indigo-600">
                Bandingkan
            </label>

            @if($compare)
                <select wire:model.live="compareMode" class="rounded-lg border-gray-300 text-sm">
                    <option value="previous">Periode sebelumnya</option>
                    <option value="custom">Rentang custom</option>
                </select>
                @if($compareMode === 'custom')
                    <input type="date" wire:model.live="cFrom" class="rounded-lg border-gray-300 text-sm">
                    <span class="text-gray-400">–</span>
                    <input type="date" wire:model.live="cTo" class="rounded-lg border-gray-300 text-sm">
                @endif
            @endif
        </div>
        <p class="mt-3 text-xs text-gray-500">Periode: {{ $rangeLabel }}</p>
    </x-erp.card>

    {{-- Kartu --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-erp.card>
            <p class="text-sm text-gray-500">Saldo total</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $balance < 0 ? '−' : '' }}{{ $rp($balance) }}</p>
        </x-erp.card>
        <x-erp.card>
            <p class="text-sm text-gray-500">Masuk</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-emerald-600">{{ $rp($totals['income']) }}
                @if($prevTotals){!! $badge($this->pct($totals['income'], $prevTotals['income']), true) !!}@endif
            </p>
            @if($prevTotals)
            <p class="mt-1 text-xs text-gray-400">Pembanding: {{ $rp($prevTotals['income']) }}</p>@endif
        </x-erp.card>
        <x-erp.card>
            <p class="text-sm text-gray-500">Keluar</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-rose-600">{{ $rp($totals['expense']) }}
                @if($prevTotals){!! $badge($this->pct($totals['expense'], $prevTotals['expense']), false) !!}@endif
            </p>
            @if($prevTotals)
            <p class="mt-1 text-xs text-gray-400">Pembanding: {{ $rp($prevTotals['expense']) }}</p>@endif
        </x-erp.card>
        <x-erp.card>
            <p class="text-sm text-gray-500">Selisih</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">
                {{ $totals['net'] < 0 ? '−' : '+' }}{{ $rp($totals['net']) }}</p>
            @if($prevTotals)
                <p class="mt-1 text-xs text-gray-400">Pembanding:
            {{ $prevTotals['net'] < 0 ? '−' : '+' }}{{ $rp($prevTotals['net']) }}</p>@endif
        </x-erp.card>
    </div>

    @if($insight)
        <p class="text-sm text-gray-600">💡 {{ $insight }}</p>
    @endif

    {{-- Grafik (wire:ignore: di-update lewat event, bukan re-render) --}}
    <div wire:ignore x-data="financeCharts(@js($chart))" x-on:charts-updated.window="update($event.detail.chart)"
        class="grid gap-4 lg:grid-cols-3">
        <x-erp.card class="lg:col-span-2">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-700">Masuk vs Keluar</h3>
                <div class="inline-flex rounded-lg border border-gray-200 p-0.5 text-xs">
                    <button @click="setKind('bar')" :class="kind==='bar' ? 'bg-gray-900 text-white' : 'text-gray-600'"
                        class="rounded-md px-2.5 py-1">Batang</button>
                    <button @click="setKind('line')" :class="kind==='line' ? 'bg-gray-900 text-white' : 'text-gray-600'"
                        class="rounded-md px-2.5 py-1">Garis</button>
                </div>
            </div>
            <div class="h-72"><canvas x-ref="bar"></canvas></div>
        </x-erp.card>

        <x-erp.card>
            <h3 class="mb-3 text-sm font-medium text-gray-700">Kategori</h3>
            <div class="h-72"><canvas x-ref="donut"></canvas></div>
        </x-erp.card>
    </div>

    {{-- Tabel kategori --}}
    <x-erp.card class="p-0">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">%</th>
                    @if($compare)
                        <th class="px-4 py-3 text-right">Pembanding</th>
                    <th class="px-4 py-3 text-right">Perubahan</th>@endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($cats as $c)
                    @php $prev = (int) ($prevCats[$c->name] ?? 0); @endphp
                    <tr>
                        <td class="px-4 py-3">{{ $c->name }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $rp($c->total) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-gray-500">
                            {{ $sum ? round($c->total / $sum * 100) : 0 }}%</td>
                        @if($compare)
                            <td class="px-4 py-3 text-right tabular-nums text-gray-500">{{ $rp($prev) }}</td>
                            <td class="px-4 py-3 text-right">
                                {!! $badge($this->pct((int) $c->total, $prev), $donutType === 'income') ?: '<span class="text-xs text-gray-400">baru</span>' !!}
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">Belum ada data pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-erp.card>
</div>