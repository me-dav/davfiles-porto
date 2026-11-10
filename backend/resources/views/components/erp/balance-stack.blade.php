@props(['total', 'cards' => []])
@php
    $rp = fn($n) => ($n < 0 ? '−' : '') . 'Rp ' . number_format(abs($n), 0, ',', '.');
    // Palet premium: ink (hitam), violet, gold. Jangan tambah hue lain di sini.
    $grads = [
        'from-violet-600 to-violet-900',
        'from-amber-600 to-amber-800',
        'from-slate-800 to-violet-950',
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
    $peek = 34;       // tinggi strip kartu belakang yang mengintip
    $maxBack = 3;     // maksimal kartu belakang yang terlihat
    $v = min($n - 1, $maxBack);

    $eyeOff = 'M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88';
    $eyeOn = 'M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z';
@endphp

{{-- wire:key: kalau jumlah akun berubah, komponen dibuat ulang dengan state baru --}}
<div wire:key="stack-{{ $n }}" class="mx-auto w-full max-w-[380px]"
    x-data="balanceStack({ n: {{ $n }}, peek: {{ $peek }}, maxBack: {{ $maxBack }} })">

    {{-- tinggi tumpukan = padding atas (ruang strip) + tinggi satu kartu (rasio kartu ATM) --}}
    <div x-ref="stage" role="group" aria-roledescription="carousel" aria-label="Saldo total dan saldo per akun"
        tabindex="0" @pointerdown="down($event)" @pointermove="move($event)" @pointerup="up($event)"
        @pointercancel="cancel()" @keydown.arrow-left.prevent="next()" @keydown.arrow-right.prevent="prev()"
        class="relative w-full touch-pan-y select-none outline-none" style="padding-top: {{ $v * $peek }}px">

        <div aria-hidden="true" class="invisible w-full" style="aspect-ratio: 1.75 / 1"></div>

        @foreach($items as $i => $it)
            @php $grad = $it['front'] ? 'from-slate-950 to-slate-700' : $grads[($i - 1) % count($grads)]; @endphp

            <div class="absolute transition-all duration-500 ease-[cubic-bezier(.22,1,.36,1)] motion-reduce:transition-none"
                :style="cardStyle({{ $i }})" @click="pick({{ $i }})">

                <div
                    class="{{ $n > 1 ? 'bs-notch' : '' }} flex h-full flex-col rounded-3xl border border-white/10 bg-gradient-to-br {{ $grad }} px-5 pb-4 text-white">

                    {{-- judul: ini yang terlihat di strip kartu belakang --}}
                    <div class="flex shrink-0 items-center justify-between gap-3 text-[10px] font-semibold uppercase tracking-wider"
                        style="height: {{ $peek }}px">
                        <span class="truncate">{{ $it['name'] }}</span>
                        <span class="shrink-0 opacity-80">{{ $it['sub'] }}</span>
                    </div>

                    <div class="flex flex-1 flex-col justify-center">
                        <p class="text-[11px] opacity-70">{{ $it['front'] ? 'Total semua akun' : 'Saldo akun' }}</p>
                        <p class="mt-1 text-2xl font-bold leading-9 tabular-nums sm:text-3xl">
                            <span x-show="!show">Rp <span class="tracking-[0.2em]">••••••</span></span>
                            <span x-show="show" x-cloak>{{ $rp($it['balance']) }}</span>
                        </p>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" data-nosnap @click.stop="show = !show"
                            :tabindex="order[0] === {{ $i }} ? 0 : -1" :aria-pressed="show.toString()"
                            class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-[11px] font-medium
                                           transition hover:bg-white/25 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path x-show="!show" stroke-linecap="round" stroke-linejoin="round" d="{{ $eyeOff }}" />
                                <path x-show="show" x-cloak stroke-linecap="round" stroke-linejoin="round"
                                    d="{{ $eyeOn }}" />
                            </svg>
                            <span x-text="show ? 'Sembunyikan' : 'Lihat saldo'">Lihat saldo</span>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($n > 1)
        <div class="mt-3 flex justify-center gap-1.5">
            @foreach($items as $i => $it)
                <button type="button" @click="goTo({{ $i }})" aria-label="Tampilkan {{ $it['name'] }}"
                    :class="order[0] === {{ $i }} ? 'w-4 bg-slate-900' : 'w-1.5 bg-slate-300'"
                    class="h-1.5 rounded-full transition-all duration-300"></button>
            @endforeach
        </div>
        <p class="mt-2 text-center text-[11px] text-slate-400">Geser kartu atau ketuk kartu di belakang untuk berganti</p>
    @endif
</div>