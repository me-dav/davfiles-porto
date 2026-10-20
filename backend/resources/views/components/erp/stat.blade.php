{{-- components/erp/stat.blade.php --}}
@props(['label', 'value', 'sub' => null, 'color' => 'pink'])
@php
    $bg = [
        'pink' => 'from-pink-500 to-rose-400',
        'purple' => 'from-purple-500 to-indigo-500',
        'orange' => 'from-amber-400 to-orange-500',
        'blue' => 'from-sky-400 to-blue-500',
    ][$color];
@endphp
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $bg }} p-5 text-white shadow-lg">
    <p class="text-xs font-medium opacity-90">{{ $label }}</p>
    <p class="mt-6 text-2xl font-bold tabular-nums">{{ $value }}</p>
    @if($sub)
    <p class="mt-1 text-[11px] opacity-80">{{ $sub }}</p>@endif
    <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
    <div class="pointer-events-none absolute -bottom-8 right-6 h-20 w-20 rounded-full bg-white/10"></div>
</div>