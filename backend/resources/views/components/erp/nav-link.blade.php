@props(['active' => false])
<a {{ $attributes->class([
    'flex items-center border-l-[3px] px-6 py-2.5 font-medium transition',
    'border-blue-500 text-blue-600' => $active,
    'border-transparent text-slate-600 hover:text-blue-600' => !$active,
]) }}>{{ $slot }}</a>