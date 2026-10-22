@props(['label', 'value', 'sub' => null, 'color' => 'blue', 'delta' => null, 'goodWhenUp' => true])
@php
    $bg = [
        'pink' => 'from-pink-500 to-rose-400',
        'purple' => 'from-purple-500 to-indigo-500',
        'orange' => 'from-amber-400 to-orange-500',
        'blue' => 'from-sky-400 to-blue-500',
    ][$color];

    $pill = null;
    if ($delta) {
        $dir = $delta['dir'];
        $good = $dir === 'flat' ? null : (($dir === 'up') === $goodWhenUp);
        $pill = [
            'arrow' => $dir === 'up' ? '▲' : ($dir === 'down' ? '▼' : '–'),
            'text' => $delta['pct'] === null
                ? 'baru'
                : rtrim(rtrim(number_format(abs($delta['pct']), 1, ',', '.'), '0'), ',') . '%',
            'cls' => $good === null ? 'text-slate-500' : ($good ? 'text-emerald-600' : 'text-rose-600'),
        ];
    }
@endphp
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $bg }} p-5 text-white shadow-lg">
    <div class="relative z-10 flex items-start justify-between gap-2">
        <p class="text-xs font-medium opacity-90">{{ $label }}</p>
        @if($pill)
            <span
                class="rounded-full bg-white px-2 py-0.5 text-[11px] font-semibold {{ $pill['cls'] }}">{{ $pill['arrow'] }}
                {{ $pill['text'] }}</span>
        @endif
    </div>
    <p class="mt-6 text-2xl font-bold tabular-nums">{{ $value }}</p>
    @if($sub)
    <p class="mt-1 text-[11px] opacity-80">{{ $sub }}</p>@endif
    <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
    <div class="pointer-events-none absolute -bottom-8 right-6 h-20 w-20 rounded-full bg-white/10"></div>
</div>