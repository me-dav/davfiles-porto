{{-- resources/views/components/erp/balance-stack.blade.php --}}
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
    $H = 132;        // tinggi kartu
    $peek = 16;      // tinggi kartu belakang yang mengintip
    $step = 84;      // jarak antar kartu saat merekah
    $maxBack = 3;    // maksimal kartu belakang yang terlihat saat menumpuk
    $v = min($n - 1, $maxBack);
    $hClosed = $H + $v * $peek;
    $hOpen = ($n - 1) * $step + $H;
@endphp

<div x-data="{ open: false }" role="button" tabindex="0" :aria-expanded="open"
    aria-label="Saldo total dan saldo per akun" @if($n > 1) @click="open = !open" @keydown.enter.prevent="open = !open"
    @keydown.space.prevent="open = !open" @endif :style="open ? 'height:{{ $hOpen }}px' : 'height:{{ $hClosed }}px'"
    class="relative w-full select-none transition-[height] duration-500 ease-[cubic-bezier(.22,1,.36,1)] motion-reduce:transition-none {{ $n > 1 ? 'cursor-pointer' : '' }}">

    @foreach($items as $i => $it)
        @php
            $level = min($i, $maxBack);
            $topClosed = ($v - $level) * $peek;
            $inset = $level * 10;
            $hidden = $i > $maxBack ? 0 : 1;
            $grad = $it['front'] ? 'from-blue-600 to-sky-500' : $grads[($i - 1) % count($grads)];
        @endphp

        <div class="absolute transition-all duration-500 ease-[cubic-bezier(.22,1,.36,1)] motion-reduce:transition-none"
            :style="open
                    ? 'top:{{ $i * $step }}px;left:0;right:0;opacity:1;z-index:{{ $i + 1 }}'
                    : 'top:{{ $topClosed }}px;left:{{ $inset }}px;right:{{ $inset }}px;opacity:{{ $hidden }};z-index:{{ $n - $i }}'">
            <div class="flex flex-col rounded-2xl bg-gradient-to-br {{ $grad }} text-white shadow-lg ring-1 ring-white/10"
                style="height: {{ $H }}px">
                <div
                    class="flex h-4 shrink-0 items-center justify-between px-4 text-[10px] font-medium uppercase tracking-wider opacity-90">
                    <span class="truncate">{{ $it['name'] }}</span>
                    <span>{{ $it['sub'] }}</span>
                </div>
                <div class="flex flex-1 flex-col justify-between px-4 pb-3 pt-3">
                    <p class="text-2xl font-bold tabular-nums">{{ $rp($it['balance']) }}</p>
                    @if($it['front'] && $n > 1)
                        <p class="flex items-center gap-1 text-[11px] opacity-80">
                            <span x-text="open ? 'Ketuk untuk menutup' : 'Ketuk untuk lihat per akun'"></span>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>