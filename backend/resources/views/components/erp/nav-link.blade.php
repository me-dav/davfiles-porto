@props(['active' => false])
<a {{ $attributes->class([
    'flex items-center rounded-lg px-3 py-2 font-medium transition',
    'bg-indigo-50 text-indigo-700' => $active,
    'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => ! $active,
]) }}>{{ $slot }}</a>