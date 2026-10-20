@props(['active' => false])
<a {{ $attributes->class([
    'flex items-center border-l-[3px] px-6 py-2.5 font-medium transition',
    'border-pink-500 text-pink-600' => $active,
    'border-transparent text-slate-600 hover:text-pink-600' => !$active,
]) }}>{{ $slot }}</a>