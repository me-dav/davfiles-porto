@props(['min' => '340px'])
<div class="overflow-x-auto overscroll-x-contain rounded-xl">
    <table class="w-fit md:w-full text-left text-sm" style="min-width: {{ $min }}">{{ $slot }}</table>
</div>