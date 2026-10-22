@props(['total', 'cards' => []])
@php
    $rp = fn($n) => ($n < 0 ? '−' : '') . 'Rp ' . number_format(abs($n), 0, ',', '.');
    $grads = [
        'from-purple-500 to-indigo-500',
        'from-amber-400 to-orange-500',
        'from-sky-400 to-blue-500',
        'from-slate-600 to-slate-500',
    ];
    $types = ['cash' => 'Tunai', 'bank' => 'Bank', 'ewallet' => 'E-Wallet', 'investment' => 'Investasi'];

    $items = array_merge(
        [['name' => 'Saldo total', 'sub' => count($cards) . ' akun aktif', 'balance' => $total, 'front' => true]],
        array_map(fn($c) => [
            'name' => $c['name'] . ($c['default'] ? ' · default' : ''),
            'sub' => $types[$c['type']] ?? $c['type'],
            'balance' => $c['balance'],
            'front' => false,
        ], $cards)
    );
    $n = count($items);
@endphp

{{-- wire:key: kalau jumlah akun berubah, komponen dibuat ulang dengan state baru --}}
<div wire:key="stack-{{ $n }}" x-data="balanceStack({ n: {{ $n }}, h: 132, peek: 16, step: 84, maxBack: 3 })">

    <div role="group" aria-roledescription="carousel" aria-label="Saldo total dan saldo per akun" tabindex="0"
        @pointerdown="down($event)" @pointermove="move($event)" @pointerup="up($event)" @pointercancel="cancel()"
        @keydown.enter.prevent="toggle()" @keydown.space.prevent="toggle()" @keydown.arrow-left.prevent="next()"
        @keydown.arrow-right.prevent="prev()" :style="'height:' + height() + 'px'"
        class="relative min-h-[132px] w-full touch-pan-y select-none outline-none transition-[height] duration-500
                ease-[cubic-bezier(.22,1,.36,1)] motion-reduce:transition-none {{ $n > 1 ? 'cursor-grab active:cursor-grabbing' : '' }}">

        @foreach($items as $i => $it)
            @php $grad = $it['front'] ? 'from-blue-600 to-sky-500' : $grads[($i - 1) % count($grads)]; @endphp
            <div class="absolute transition-all duration-500 ease-[cubic-bezier(.22,1,.36,1)] motion-reduce:transition-none"
                :style="cardStyle({{ $i }})">
                <div
                    class="flex h-[132px] flex-col rounded-2xl bg-gradient-to-br {{ $grad }} text-white shadow-lg ring-1 ring-white/10">
                    <div
                        class="flex h-4 shrink-0 items-center justify-between px-4 text-[10px] font-medium uppercase tracking-wider opacity-90">
                        <span class="truncate">{{ $it['name'] }}</span>
                        <span>{{ $it['sub'] }}</span>
                    </div>
                    <div class="flex flex-1 items-end px-4 pb-4">
                        <p class="text-2xl font-bold tabular-nums">{{ $rp($it['balance']) }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($n > 1)
        <div x-show="!open" class="mt-3 flex justify-center gap-1.5">
            @foreach($items as $i => $it)
                <button type="button" @click="goTo({{ $i }})" aria-label="Tampilkan {{ $it['name'] }}"
                    :class="order[0] === {{ $i }} ? 'w-4 bg-blue-600' : 'w-1.5 bg-slate-300'"
                    class="h-1.5 rounded-full transition-all duration-300"></button>
            @endforeach
        </div>
        <p class="mt-2 text-center text-[11px] text-slate-400"
            x-text="open ? 'Ketuk untuk menutup' : 'Geser untuk ganti kartu · ketuk untuk lihat semua'"></p>
    @endif
</div>