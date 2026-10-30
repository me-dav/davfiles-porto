@props(['flush' => false])
<div {{ $attributes->class([
    'rounded-2xl bg-white shadow-[0_4px_24px_rgba(15,23,42,0.05)]',
    'p-0' => $flush,
    'p-4 sm:p-6' => !$flush,
]) }}>{{ $slot }}</div>