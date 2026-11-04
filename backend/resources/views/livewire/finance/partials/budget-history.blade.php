@php
    $rp = fn($n) => 'Rp ' . number_format((int) abs($n), 0, ',', '.');
    $sum = $history['summary'];
    $recurring = $b->isRecurring();
@endphp
<section class="rounded-2xl bg-white p-4 shadow-[0_4px_24px_rgba(15,23,42,0.05)] sm:p-6">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h4 class="text-sm font-semibold text-slate-900">Riwayat · {{ $b->name }}</h4>
            <p class="mt-0.5 text-xs text-slate-400">{{ $b->periodTypeLabel() }}. Dihitung dari transaksi pengeluaran,
                di luar hasil Sync saldo.</p>
        </div>
    </div>

    {{-- ringkasan --}}
    @if($sum)
        <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-[11px] uppercase tracking-wide text-slate-400">Periode selesai</p>
                <p class="mt-1 text-lg font-bold tabular-nums text-slate-900">{{ $sum['count'] }}</p>
                <p class="text-xs text-slate-500">{{ $sum['under'] }} hemat · {{ $sum['over'] }} lebih</p>
            </div>
            <div class="rounded-xl bg-emerald-50 p-3">
                <p class="text-[11px] uppercase tracking-wide text-emerald-700/70">Total hemat</p>
                <p class="mt-1 text-lg font-bold tabular-nums text-emerald-700">{{ $rp($sum['saved']) }}</p>
            </div>
            <div class="rounded-xl bg-rose-50 p-3">
                <p class="text-[11px] uppercase tracking-wide text-rose-700/70">Total lebih</p>
                <p class="mt-1 text-lg font-bold tabular-nums text-rose-700">{{ $rp($sum['overspent']) }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-[11px] uppercase tracking-wide text-slate-400">Rata-rata terpakai</p>
                <p class="mt-1 text-lg font-bold tabular-nums text-slate-900">{{ round($sum['avg_pct']) }}%</p>
                <p class="text-xs text-slate-500">dari nominal budget</p>
            </div>
        </div>

        @if($sum['best'] || $sum['worst'])
            <div class="mt-3 grid gap-3 text-xs sm:grid-cols-2">
                @if($sum['best'])
                    <p class="rounded-lg bg-slate-50 px-3 py-2 text-slate-600">
                        Terhemat: <span class="font-medium text-slate-900">{{ $sum['best']['label'] }}</span>,
                        <span class="font-medium text-emerald-600">hemat {{ $rp($sum['best']['diff']) }}</span>
                    </p>
                @endif
                @if($sum['worst'])
                    <p class="rounded-lg bg-slate-50 px-3 py-2 text-slate-600">
                        Terboros: <span class="font-medium text-slate-900">{{ $sum['worst']['label'] }}</span>,
                        <span class="font-medium text-rose-600">lebih {{ $rp($sum['worst']['diff']) }}</span>
                    </p>
                @endif
            </div>
        @endif
    @elseif($recurring)
        <p class="mt-4 rounded-lg bg-slate-50 px-4 py-3 text-xs text-slate-500">
            Belum ada periode yang selesai. Ringkasan muncul setelah periode pertama berakhir.</p>
    @endif

    {{-- tabel periode --}}
    <div class="mt-5 overflow-x-auto overscroll-x-contain">
        <table class="w-full text-left text-sm" style="min-width: 560px">
            <thead class="bg-slate-800 text-[11px] font-medium uppercase tracking-wider text-white">
                <tr>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3 text-right">Nominal</th>
                    <th class="px-4 py-3 text-right">Terpakai</th>
                    <th class="px-4 py-3 text-right">%</th>
                    <th class="px-4 py-3 text-right">Hasil</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($history['periods'] as $p)
                    <tr>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $p['label'] }}
                            @unless($p['finished'])<span
                            class="ml-1 rounded bg-blue-50 px-1.5 py-0.5 text-[10px] font-medium text-blue-700">berjalan</span>@endunless
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-slate-500">{{ $rp($p['amount']) }}</td>
                        <td class="px-4 py-3 text-right font-medium tabular-nums">{{ $rp($p['spent']) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-slate-500">{{ round($p['pct']) }}%</td>
                        <td class="px-4 py-3 text-right text-xs font-medium tabular-nums">
                            @if(!$p['finished'])
                                <span
                                    class="text-slate-400">{{ $p['diff'] >= 0 ? 'sisa ' . $rp($p['diff']) : 'lewat ' . $rp($p['diff']) }}</span>
                            @elseif($p['diff'] >= 0)
                                <span class="text-emerald-600">hemat {{ $rp($p['diff']) }}</span>
                            @else
                                <span class="text-rose-600">lebih {{ $rp($p['diff']) }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- rincian kategori untuk budget sekali jalan --}}
    @if(!$recurring && $history['breakdown'])
        @php $max = max(array_column($history['breakdown'], 'total')); @endphp
        <div class="mt-5">
            <p class="text-xs font-medium text-slate-700">Pengeluaran terbesar</p>
            <ul class="mt-2 space-y-2">
                @foreach($history['breakdown'] as $row)
                    <li>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-600">{{ $row['name'] }}</span>
                            <span class="tabular-nums text-slate-500">{{ $rp($row['total']) }}</span>
                        </div>
                        <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-blue-500"
                                style="width: {{ $max > 0 ? round($row['total'] / $max * 100) : 0 }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($recurring)
        <p class="mt-4 text-[11px] text-slate-400">Riwayat dimulai sejak budget dibuat dan menampilkan maksimal 24 periode
            terakhir.
            Nominal tiap periode memakai nominal yang berlaku saat itu.</p>
    @endif
</section>