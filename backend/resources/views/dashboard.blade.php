{{-- resources/views/dashboard.blade.php --}}
<x-layouts.erp title="Dashboard">
    @if(auth()->user()->hasModule('finance'))
        <div class="space-y-4">
            <livewire:finance.dashboard />
        </div>
    @else
        <x-erp.card>
            <p class="text-sm text-gray-600">Belum ada modul yang aktif untuk akunmu.</p>
        </x-erp.card>
    @endif
</x-layouts.erp>