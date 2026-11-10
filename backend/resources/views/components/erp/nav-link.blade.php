@props(['active' => false])
{{-- Tema: aktif = pill ink (hitam) dengan titik violet; hover = abu lembut --}}
<a {{ $attributes->class([
    'group relative mx-3 flex items-center gap-2 mb-0.5 rounded-xl px-3 py-2.5 font-medium transition-colors duration-200',
    'bg-slate-900 text-white shadow-[0_6px_16px_-6px_rgba(15,23,42,0.45)]' => $active,
    'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => !$active,
]) }} @if($active) aria-current="page" @endif>
    <span @class([
        'h-1.5 w-1.5 shrink-0 rounded-full transition-colors',
        'bg-violet-400' => $active,
        'bg-slate-300 group-hover:bg-violet-500' => !$active,
    ])></span>
    {{ $slot }}
</a>
