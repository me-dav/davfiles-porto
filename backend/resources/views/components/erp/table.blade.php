@props(['min' => '640px'])
<div {{ $attributes->class('overflow-x-auto overscroll-x-contain') }}>
    <table class="w-full text-left text-sm" style="min-width: {{ $min }}">{{ $slot }}</table>
</div>