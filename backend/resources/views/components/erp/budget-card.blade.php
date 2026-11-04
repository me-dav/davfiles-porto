@props(['b', 's'])
@php
    $rp = fn($n) => 'Rp ' . number_format((int) abs($n), 0, ',', '.');
    $over = $s['over'] > 0;
    $pct = (int) round($s['pct']);
@endphp
<article {{ $attributes->class('flex flex-col rounded-2xl bg-white p-4 shadow-[0_4px_24px_rgba(15,23,42,0.05)] sm:p-5') }}>
    <header class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h3 class="truncate text-sm font-semibold text-slate-900">{{ $b->name }}</h3>
            <p class="mt-0.5 text-xs text-slate-400">{{ $s['period_label'] }}</p>
        </div>
        <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold text-white"
            style="background: {{ $s['color'] }}">{{ $s['label'] }}</span>
    </header>

    <p class="mt-2 truncate text-xs text-slate-500">{{ $b->categoryLabel() }} · {{ $b->accountLabel() }}</p>

    <div class="mt-4 flex items-end justify-between gap-2">
        <p class="truncate text-xl font-bold tabular-nums text-slate-900">{{ $rp($s['spent']) }}</p>
        <p class="shrink-0 text-xs tabular-nums text-slate-400">dari {{ $rp($s['amount']) }}</p>
    </div>

    <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-100" role="progressbar"
        aria-label="Pemakaian budget {{ $b->name }}" aria-valuemin="0" aria-valuemax="100"
        aria-valuenow="{{ min(100, $pct) }}">
        <div class="h-full rounded-full transition-all duration-700 ease-out"
            style="width: {{ $s['bar'] }}%; background: {{ $s['color'] }}"></div>
    </div>

    <div class="mt-2 flex items-center justify-between gap-2 text-xs">
        <span class="font-semibold tabular-nums" style="color: {{ $s['color'] }}">{{ $pct }}%</span>
        @if($over)
            <span class="font-semibold text-rose-600">Lewat {{ $rp($s['over']) }}</span>
        @else
            <span class="tabular-nums text-slate-500">Sisa {{ $rp($s['remaining']) }}</span>
        @endif
    </div>

    <p class="mt-2 min-h-[1rem] text-[11px] text-slate-400">
        @if($s['state'] === 'upcoming')
            Mulai {{ $s['start']->translatedFormat('j M Y') }}
        @elseif($s['state'] === 'finished')
            @if($over) Selesai, lebih {{ $rp($s['over']) }} @else Selesai, hemat {{ $rp($s['remaining']) }} @endif
        @else
            @if($over)
                Sisa {{ $s['days_left'] }} hari
            @elseif($s['remaining'] === 0)
                Budget habis · sisa {{ $s['days_left'] }} hari
            @else
                Sisa {{ $s['days_left'] }} hari · boleh {{ $rp($s['per_day'] ?? 0) }} per hari
            @endif
        @endif
    </p>

    {{ $slot }}
</article>